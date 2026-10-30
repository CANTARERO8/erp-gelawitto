<?php

namespace Tests\Feature;

use App\Models\Calidad\ControlCalidad;
use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Models\Produccion\RecetaInsumo;
use App\Models\User;
use App\Services\Produccion\ProduccionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalidadTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Producto $producto;

    private Receta $receta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $unidad = UnidadMedida::where('abreviatura', 'ud')->firstOrFail();

        $insumo = Insumo::create([
            'codigo' => 'TST-CAL-001',
            'nombre' => 'Insumo de prueba calidad',
            'categoria' => 'Prueba',
            'unidad_id' => $unidad->id,
            'stock_actual' => 10,
            'stock_minimo' => 0,
            'costo_promedio' => 4,
            'activo' => true,
        ]);

        $this->producto = Producto::create([
            'sku' => 'TST-CAL-001',
            'nombre' => 'Producto de prueba calidad',
            'sabor' => 'Prueba',
            'presentacion' => 'Unidad',
            'precio_venta' => 100,
            'costo_unitario' => 0,
            'stock_actual' => 0,
            'stock_minimo' => 0,
            'activo' => true,
        ]);

        $receta = Receta::create([
            'producto_id' => $this->producto->id,
            'nombre' => 'Receta de prueba calidad',
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

        $this->receta = $receta;
    }

    /**
     * Programa y ejecuta un lote de 5 unidades a costo 4 por unidad (lote de C$ 20).
     */
    private function loteEjecutado(): LoteProduccion
    {
        $servicio = app(ProduccionService::class);
        $lote = $servicio->programarLote(['receta_id' => $this->receta->id, 'cantidad_programada' => 5], $this->admin->id, null);

        return $servicio->ejecutarLote($lote, ['cantidad_obtenida' => 5], $this->admin->id, null);
    }

    public function test_liberacion_sin_no_conformes_deja_todas_las_unidades_disponibles(): void
    {
        $lote = $this->loteEjecutado();

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 0,
            'disposicion' => 'Liberación',
        ])->assertOk();

        $this->assertEquals('Liberado', $lote->fresh()->estado_calidad);
        $this->assertEquals(5.0, (float) $this->producto->fresh()->stock_actual);
        $this->assertEquals(1, ControlCalidad::where('lote_id', $lote->id)->count());
    }

    public function test_scrap_da_de_baja_la_unidad_no_conforme_y_registra_el_costo_perdido(): void
    {
        $lote = $this->loteEjecutado();

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 1,
            'defecto' => 'Gelificación incompleta',
            'disposicion' => 'Scrap',
        ])->assertOk();

        $this->assertEquals('Scrap', $lote->fresh()->estado_calidad);
        $this->assertEquals(4.0, (float) $this->producto->fresh()->stock_actual);

        $control = ControlCalidad::where('lote_id', $lote->id)->firstOrFail();
        $this->assertEquals(4.00, (float) $control->costo_perdido);
        $this->assertDatabaseHas('movimientos_inventario', [
            'referencia' => $lote->codigo_lote,
            'tipo_movimiento' => 'Scrap por Calidad',
            'cantidad' => -1.000,
        ]);
    }

    public function test_retrabajo_retira_la_unidad_no_conforme_del_stock_vendible(): void
    {
        $lote = $this->loteEjecutado();

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 2,
            'defecto' => 'Fuga de tapa',
            'disposicion' => 'Retrabajo',
        ])->assertOk();

        $this->assertEquals('Retrabajo', $lote->fresh()->estado_calidad);
        $this->assertEquals(3.0, (float) $this->producto->fresh()->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'referencia' => $lote->codigo_lote,
            'tipo_movimiento' => 'Retrabajo por Calidad',
            'cantidad' => -2.000,
        ]);
    }

    public function test_no_permite_liberar_un_lote_con_unidades_no_conformes(): void
    {
        $lote = $this->loteEjecutado();

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 1,
            'defecto' => 'Sabor fuera de criterio',
            'disposicion' => 'Liberación',
        ])->assertJsonValidationErrors('disposicion');

        $this->assertEquals('Pendiente', $lote->fresh()->estado_calidad);
        $this->assertEquals(5.0, (float) $this->producto->fresh()->stock_actual);
    }

    public function test_exige_descripcion_cuando_hay_unidades_no_conformes(): void
    {
        $lote = $this->loteEjecutado();

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 1,
            'disposicion' => 'Scrap',
        ])->assertJsonValidationErrors('defecto');
    }

    public function test_sin_no_conformes_la_disposicion_debe_ser_liberacion(): void
    {
        $lote = $this->loteEjecutado();

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 0,
            'disposicion' => 'Scrap',
        ])->assertJsonValidationErrors('disposicion');
    }

    public function test_no_permite_una_segunda_inspeccion_del_mismo_lote(): void
    {
        $lote = $this->loteEjecutado();

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 0,
            'disposicion' => 'Liberación',
        ])->assertOk();

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 0,
            'disposicion' => 'Liberación',
        ])->assertJsonValidationErrors('lote');

        $this->assertEquals(1, ControlCalidad::where('lote_id', $lote->id)->count());
    }

    public function test_no_inspecciona_un_lote_que_aun_no_se_ejecuta(): void
    {
        $lote = app(ProduccionService::class)->programarLote(
            ['receta_id' => $this->receta->id, 'cantidad_programada' => 5],
            $this->admin->id,
            null
        );

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 0,
            'disposicion' => 'Liberación',
        ])->assertJsonValidationErrors('lote');
    }

    public function test_scrap_no_puede_superar_el_stock_disponible(): void
    {
        $lote = $this->loteEjecutado();
        DB::table('productos')->where('id', $this->producto->id)->update(['stock_actual' => 0]);

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 1,
            'defecto' => 'Prueba de stock insuficiente',
            'disposicion' => 'Scrap',
        ])->assertJsonValidationErrors('unidades_no_conformes');

        $this->assertEquals('Pendiente', $lote->fresh()->estado_calidad);
        $this->assertEquals(0, DB::table('controles_calidad')->where('lote_id', $lote->id)->count());
    }

    public function test_la_pantalla_de_calidad_muestra_la_trazabilidad_del_lote(): void
    {
        $lote = $this->loteEjecutado();

        $this->actingAs($this->admin)->get('/calidad')
            ->assertOk()
            ->assertJsonStructure([
                'trazabilidad',
                'pendientes',
                'lecturas',
                'rango' => ['minimo', 'maximo', 'dispositivo'],
            ]);
        $this->assertNotNull($lote->id);
    }
}
