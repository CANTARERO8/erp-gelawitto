<?php

namespace Tests\Feature;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Models\Produccion\RecetaInsumo;
use App\Models\User;
use App\Services\Produccion\ProduccionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProduccionCostoPromedioTest extends TestCase
{
    use DatabaseTransactions;

    public function test_el_costo_unitario_del_producto_es_promedio_ponderado_entre_lotes(): void
    {
        $admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $unidad = UnidadMedida::where('abreviatura', 'ud')->firstOrFail();

        $insumo = Insumo::create([
            'codigo' => 'TST-PROM-001',
            'nombre' => 'Insumo de prueba costo promedio',
            'categoria' => 'Prueba',
            'unidad_id' => $unidad->id,
            'stock_actual' => 100,
            'stock_minimo' => 0,
            'costo_promedio' => 10,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sku' => 'TST-PROM-001',
            'nombre' => 'Producto de prueba costo promedio',
            'sabor' => 'Prueba',
            'presentacion' => 'Unidad',
            'precio_venta' => 100,
            'costo_unitario' => 0,
            'stock_actual' => 0,
            'stock_minimo' => 0,
            'activo' => true,
        ]);

        $receta = Receta::create([
            'producto_id' => $producto->id,
            'nombre' => 'Receta de prueba',
            'rendimiento_unidades' => 1,
            'tiempo_preparacion_min' => 1,
            'activa' => true,
        ]);

        RecetaInsumo::create([
            'receta_id' => $receta->id,
            'insumo_id' => $insumo->id,
            'cantidad_requerida' => 1,
            'merma_estimada_porcentaje' => 0,
        ]);

        $servicio = app(ProduccionService::class);

        // Lote 1: costo de 10 por unidad.
        $lote1 = $servicio->programarLote(['receta_id' => $receta->id, 'cantidad_programada' => 1], $admin->id, null);
        $servicio->ejecutarLote($lote1, ['cantidad_obtenida' => 1], $admin->id, null);

        // El insumo sube a 30 antes del lote 2.
        $insumo->update(['costo_promedio' => 30]);

        $lote2 = $servicio->programarLote(['receta_id' => $receta->id, 'cantidad_programada' => 1], $admin->id, null);
        $servicio->ejecutarLote($lote2, ['cantidad_obtenida' => 1], $admin->id, null);

        // Promedio ponderado: (1 x 10 + 1 x 30) / 2 = 20. Tomar solo el último lote daría 30.
        $this->assertEquals(20.00, (float) $producto->fresh()->costo_unitario);
        $this->assertEquals(2.000, (float) $producto->fresh()->stock_actual);
    }
}
