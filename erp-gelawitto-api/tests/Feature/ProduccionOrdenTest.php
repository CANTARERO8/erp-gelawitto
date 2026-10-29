<?php

namespace Tests\Feature;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Produccion\Receta;
use App\Models\Produccion\RecetaInsumo;
use App\Models\User;
use App\Services\Produccion\ProduccionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProduccionOrdenTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Receta $receta;

    private Insumo $insumo;

    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $unidad = UnidadMedida::where('abreviatura', 'ud')->firstOrFail();

        $this->insumo = Insumo::create([
            'codigo' => 'TST-ORD-001',
            'nombre' => 'Insumo de prueba orden de producción',
            'categoria' => 'Prueba',
            'unidad_id' => $unidad->id,
            'stock_actual' => 10,
            'stock_minimo' => 0,
            'costo_promedio' => 4,
            'activo' => true,
        ]);

        $this->producto = Producto::create([
            'sku' => 'TST-ORD-001',
            'nombre' => 'Producto de prueba orden de producción',
            'sabor' => 'Prueba',
            'presentacion' => 'Unidad',
            'precio_venta' => 100,
            'costo_unitario' => 0,
            'stock_actual' => 0,
            'stock_minimo' => 0,
            'activo' => true,
        ]);

        $this->receta = Receta::create([
            'producto_id' => $this->producto->id,
            'nombre' => 'Receta de prueba orden',
            'rendimiento_unidades' => 2,
            'tiempo_preparacion_min' => 1,
            'activa' => true,
        ]);

        RecetaInsumo::create([
            'receta_id' => $this->receta->id,
            'insumo_id' => $this->insumo->id,
            'cantidad_requerida' => 1,
            'merma_estimada_porcentaje' => 0,
        ]);
    }

    public function test_programar_orden_no_descuenta_insumos_ni_suma_producto(): void
    {
        $servicio = app(ProduccionService::class);

        $lote = $servicio->programarLote(['receta_id' => $this->receta->id, 'cantidad_programada' => 4], $this->admin->id, null);

        $this->assertEquals('Programada', $lote->estado);
        $this->assertNull($lote->cantidad_obtenida);
        $this->assertNull($lote->costo_total_lote);
        $this->assertEquals(10.0, (float) $this->insumo->fresh()->stock_actual);
        $this->assertEquals(0.0, (float) $this->producto->fresh()->stock_actual);
        $this->assertDatabaseMissing('movimientos_inventario', ['referencia' => $lote->codigo_lote]);
    }

    public function test_ejecutar_descuenta_insumos_escalados_y_registra_merma(): void
    {
        $servicio = app(ProduccionService::class);
        $lote = $servicio->programarLote(['receta_id' => $this->receta->id, 'cantidad_programada' => 4], $this->admin->id, null);

        // La receta rinde 2 y se programan 4: factor 2, así que se descuentan 2 unidades del insumo.
        $ejecutado = $servicio->ejecutarLote($lote, ['cantidad_obtenida' => 3.5], $this->admin->id, null);

        $this->assertEquals('Finalizado', $ejecutado->estado);
        $this->assertEquals(0.5, (float) $ejecutado->merma);
        $this->assertEquals(8.00, (float) $ejecutado->costo_total_lote);
        $this->assertEquals(8.0, (float) $this->insumo->fresh()->stock_actual);
        $this->assertEquals(3.5, (float) $this->producto->fresh()->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'referencia' => $lote->codigo_lote,
            'tipo_movimiento' => 'Consumo de Producción',
            'cantidad' => -2.000,
        ]);
    }

    public function test_ejecutar_la_misma_orden_dos_veces_es_rechazado(): void
    {
        $servicio = app(ProduccionService::class);
        $lote = $servicio->programarLote(['receta_id' => $this->receta->id, 'cantidad_programada' => 2], $this->admin->id, null);
        $servicio->ejecutarLote($lote, ['cantidad_obtenida' => 2], $this->admin->id, null);

        $stockInsumo = (float) $this->insumo->fresh()->stock_actual;
        $stockProducto = (float) $this->producto->fresh()->stock_actual;

        $this->expectException(ValidationException::class);
        $servicio->ejecutarLote($lote->fresh(), ['cantidad_obtenida' => 2], $this->admin->id, null);

        $this->assertEquals($stockInsumo, (float) $this->insumo->fresh()->stock_actual);
        $this->assertEquals($stockProducto, (float) $this->producto->fresh()->stock_actual);
        $this->assertEquals(1, DB::table('lotes_produccion')->where('id', $lote->id)->count());
    }
}
