<?php

namespace Tests\Feature;

use App\Models\Catalogos\Cliente;
use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Proveedor;
use App\Models\Compras\OrdenCompra;
use App\Models\Produccion\CentroTrabajo;
use App\Models\Produccion\ConsumoLote;
use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Models\Produccion\RutaOperacion;
use App\Models\User;
use App\Models\Ventas\Venta;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BomOeeTrazabilidadFefoTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
    }

    public function test_catalogo_sin_registros_heladeria_y_con_tres_skus_terminados_mas_subensamble(): void
    {
        $this->assertEquals(0, Producto::where('sku', 'like', 'GEL-%')->count());

        $terminados = Producto::where('tipo', 'terminado')->pluck('sku')->all();
        $this->assertGreaterThanOrEqual(3, count($terminados));
        $this->assertContains('GLW-TRA-100', $terminados);
        $this->assertContains('GLW-FRU-100', $terminados);
        $this->assertContains('GLW-LCO-100', $terminados);

        $semielaborados = Producto::where('tipo', 'semielaborado')->pluck('sku')->all();
        $this->assertContains('SUB-GEL-BASE', $semielaborados);
    }

    public function test_bom_de_dos_niveles_consume_subensamble_y_calcula_costo_en_cascada(): void
    {
        $subBase = Producto::where('sku', 'SUB-GEL-BASE')->firstOrFail();
        $prodFrutal = Producto::where('sku', 'GLW-FRU-100')->firstOrFail();
        $recFrutal = Receta::where('producto_id', $prodFrutal->id)->firstOrFail();

        $stockBaseAntes = (float) $subBase->stock_actual;
        $insFruta = Insumo::where('codigo', 'INS-005')->firstOrFail();
        $stockFrutaAntes = (float) $insFruta->stock_actual;

        $this->actingAs($this->admin)->post('/produccion/lotes', [
            'receta_id' => $recFrutal->id,
            'cantidad_programada' => 5,
            'notas' => 'Prueba BOM 2 niveles',
        ])->assertCreated();

        $lote = LoteProduccion::latest('id')->firstOrFail();

        $this->actingAs($this->admin)->post("/produccion/lotes/{$lote->id}/ejecutar", [
            'cantidad_obtenida' => 5,
            'tiempo_paro_min' => 3.5,
            'fecha_vencimiento' => now()->addDays(12)->toDateString(),
            'notas' => 'Ejecución BOM 2 niveles',
        ])->assertOk();

        $lote->refresh();
        $this->assertEquals(round($stockBaseAntes - 0.400, 3), (float) $subBase->fresh()->stock_actual);
        $this->assertEquals(round($stockFrutaAntes - 0.100, 3), (float) $insFruta->fresh()->stock_actual);

        $this->assertEquals(107.50, (float) $lote->costo_total_lote);
        $this->assertEquals(21.50, (float) $lote->costo_unitario_resultante);
        $this->assertEquals(3.50, (float) $lote->tiempo_paro_min);

        $this->assertTrue(
            ConsumoLote::where('lote_produccion_id', $lote->id)
                ->whereNotNull('subproducto_lote_id')
                ->exists()
        );
    }

    public function test_centros_de_trabajo_rutas_y_calculo_de_oee_y_tiempo_de_ciclo(): void
    {
        $this->assertGreaterThanOrEqual(4, CentroTrabajo::count());
        $this->assertGreaterThanOrEqual(10, RutaOperacion::count());

        $response = $this->actingAs($this->admin)->get('/api/produccion/oee');
        $response->assertOk()
            ->assertJsonStructure([
                'disponibilidad',
                'rendimiento',
                'calidad',
                'oee_global',
                'tiempo_ciclo_real_promedio_min',
                'tiempo_ciclo_estandar_promedio_min',
                'lotes' => [
                    ['codigo_lote', 'producto', 'disponibilidad', 'rendimiento', 'calidad', 'oee', 'ciclo_estandar_min', 'ciclo_real_min'],
                ],
            ]);

        $this->assertGreaterThan(0, (float) $response->json('oee_global'));
        $this->assertGreaterThan(0, (float) $response->json('tiempo_ciclo_real_promedio_min'));
    }

    public function test_trazabilidad_completa_fefo_desde_lote_proveedor_hasta_venta_cliente(): void
    {
        $proveedor = Proveedor::firstOrFail();
        $insumo = Insumo::where('codigo', 'INS-005')->firstOrFail();

        $this->actingAs($this->admin)->post('/compras', [
            'proveedor_id' => $proveedor->id,
            'insumo_id' => $insumo->id,
            'cantidad' => 5.000,
            'costo_unitario' => 150.00,
            'lote_proveedor' => 'LPR-TEST-FEFO-01',
            'fecha_vencimiento' => now()->addDays(5)->toDateString(),
        ])->assertCreated();

        $orden = OrdenCompra::latest('id')->firstOrFail();
        $this->actingAs($this->admin)->post("/compras/{$orden->id}/recibir", [
            'modalidad_pago' => 'Pagado',
        ])->assertOk();

        $this->assertDatabaseHas('lotes_insumo', [
            'insumo_id' => $insumo->id,
            'lote_proveedor' => 'LPR-TEST-FEFO-01',
            'cantidad_disponible' => 5.000,
        ]);

        $producto = Producto::where('sku', 'GLW-TRA-100')->firstOrFail();
        $receta = Receta::where('producto_id', $producto->id)->firstOrFail();

        LoteProduccion::where('producto_id', $producto->id)->update(['cantidad_disponible' => 0, 'estado_calidad' => 'Liberado']);
        $producto->update(['stock_actual' => 0]);

        $this->actingAs($this->admin)->post('/produccion/lotes', [
            'receta_id' => $receta->id,
            'cantidad_programada' => 5,
        ])->assertCreated();
        $loteA = LoteProduccion::latest('id')->firstOrFail();
        $this->actingAs($this->admin)->post("/produccion/lotes/{$loteA->id}/ejecutar", [
            'cantidad_obtenida' => 3,
            'fecha_vencimiento' => now()->addDays(3)->toDateString(),
        ])->assertOk();
        $this->actingAs($this->admin)->post("/calidad/lotes/{$loteA->id}/inspeccion", [
            'unidades_inspeccionadas' => 3,
            'unidades_no_conformes' => 0,
            'disposicion' => 'Liberación',
        ])->assertOk();

        $this->actingAs($this->admin)->post('/produccion/lotes', [
            'receta_id' => $receta->id,
            'cantidad_programada' => 5,
        ])->assertCreated();
        $loteB = LoteProduccion::latest('id')->firstOrFail();
        $this->actingAs($this->admin)->post("/produccion/lotes/{$loteB->id}/ejecutar", [
            'cantidad_obtenida' => 5,
            'fecha_vencimiento' => now()->addDays(10)->toDateString(),
        ])->assertOk();
        $this->actingAs($this->admin)->post("/calidad/lotes/{$loteB->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 0,
            'disposicion' => 'Liberación',
        ])->assertOk();

        $cliente = Cliente::where('tipo', 'Mayorista')->firstOrFail();
        $this->actingAs($this->admin)->post('/ventas', [
            'cliente_id' => $cliente->id,
            'producto_id' => $producto->id,
            'cantidad' => 5,
            'metodo_pago' => 'Efectivo',
        ])->assertCreated();

        $venta = Venta::with('lineas')->latest('id')->firstOrFail();
        $this->assertCount(2, $venta->lineas);

        $lineaA = $venta->lineas->firstWhere('lote_produccion_id', $loteA->id);
        $lineaB = $venta->lineas->firstWhere('lote_produccion_id', $loteB->id);

        $this->assertNotNull($lineaA);
        $this->assertNotNull($lineaB);
        $this->assertEquals(3.0, (float) $lineaA->cantidad);
        $this->assertEquals(2.0, (float) $lineaB->cantidad);

        $this->assertEquals(0.0, (float) $loteA->fresh()->cantidad_disponible);
        $this->assertEquals(3.0, (float) $loteB->fresh()->cantidad_disponible);

        $calidadRes = $this->actingAs($this->admin)->get('/api/calidad')->assertOk();
        $trazaLoteA = collect($calidadRes->json('trazabilidad'))->firstWhere('codigo_lote', $loteA->codigo_lote);
        $this->assertNotEmpty($trazaLoteA['ventas_clientes']);
        $this->assertEquals($venta->folio, $trazaLoteA['ventas_clientes'][0]['folio']);
    }

    public function test_preferencias_visuales_se_persisten_en_tabla_parametros_y_subensambles_no_se_venden_en_pos(): void
    {
        $this->postJson('/api/preferencias', [
            'ui_tema' => 'dark',
            'ui_sidebar_colapsado' => 'true',
        ])->assertOk()
            ->assertJsonPath('parametros.ui_tema', 'dark')
            ->assertJsonPath('parametros.ui_sidebar_colapsado', 'true');

        $this->assertDatabaseHas('parametros', [
            'clave' => 'ui_tema',
            'valor' => 'dark',
        ]);

        // Verificar que SUB-GEL-BASE no aparece en el selector de productos de punto de venta
        $ventasRes = $this->actingAs($this->admin)->get('/api/ventas')->assertOk();
        $skusPos = collect($ventasRes->json('productos'))->pluck('sku')->all();
        $this->assertNotContains('SUB-GEL-BASE', $skusPos);

        // Y si se intenta forzar su venta por API, se rechaza con 422
        $subBase = Producto::where('sku', 'SUB-GEL-BASE')->firstOrFail();
        $cliente = Cliente::firstOrFail();
        $this->actingAs($this->admin)->postJson('/api/ventas', [
            'cliente_id' => $cliente->id,
            'producto_id' => $subBase->id,
            'cantidad' => 1,
            'metodo_pago' => 'Efectivo',
        ])->assertStatus(422);
    }

    public function test_creacion_de_producto_con_tipo_y_receta_bom_multinivel_con_rutas_en_base_de_datos(): void
    {
        $this->actingAs($this->admin)->postJson('/api/catalogos/productos', [
            'sku' => 'GLW-MAR-100',
            'nombre' => 'Gelawitto de Maracuyá y Arroz',
            'tipo' => 'terminado',
            'sabor' => 'Maracuyá',
            'presentacion' => 'Vaso 100 mL',
            'precio_venta' => 115.00,
            'costo_unitario' => 21.50,
            'stock_minimo' => 10,
        ])->assertCreated();

        $nuevoProd = Producto::where('sku', 'GLW-MAR-100')->firstOrFail();
        $subBase = Producto::where('sku', 'SUB-GEL-BASE')->firstOrFail();
        $insFruta = Insumo::where('codigo', 'INS-005')->firstOrFail();
        $ctDosificado = CentroTrabajo::where('codigo', 'CT-02')->firstOrFail();

        $this->actingAs($this->admin)->postJson('/api/produccion/recetas', [
            'producto_id' => $nuevoProd->id,
            'nombre' => 'BOM 2 Niveles: Gelawitto de Maracuyá (Lote 5 vasos)',
            'rendimiento_unidades' => 5,
            'tiempo_preparacion_min' => 45,
            'instrucciones' => 'Dosificación de 80 mL de base gelificada + 20 g de pulpa de maracuyá',
            'ingredientes' => [
                [
                    'subproducto_id' => $subBase->id,
                    'insumo_id' => null,
                    'nivel' => 1,
                    'cantidad_requerida' => 0.400,
                    'merma_estimada_porcentaje' => 0,
                ],
                [
                    'subproducto_id' => null,
                    'insumo_id' => $insFruta->id,
                    'nivel' => 1,
                    'cantidad_requerida' => 0.100,
                    'merma_estimada_porcentaje' => 0,
                ],
            ],
            'rutas' => [
                [
                    'centro_trabajo_id' => $ctDosificado->id,
                    'secuencia' => 1,
                    'operacion' => 'Dosificación y sellado de vaso 100 mL',
                    'tiempo_preparacion_min' => 6.0,
                    'tiempo_ciclo_min_por_unidad' => 1.5,
                ],
            ],
        ])->assertCreated();

        $recetaCreada = Receta::where('producto_id', $nuevoProd->id)->firstOrFail();
        $this->assertDatabaseHas('receta_insumos', [
            'receta_id' => $recetaCreada->id,
            'subproducto_id' => $subBase->id,
            'cantidad_requerida' => 0.400,
        ]);
        $this->assertDatabaseHas('rutas_operacion', [
            'receta_id' => $recetaCreada->id,
            'centro_trabajo_id' => $ctDosificado->id,
            'operacion' => 'Dosificación y sellado de vaso 100 mL',
        ]);
    }

    public function test_lotes_sembrados_consumen_su_bom_completa_y_el_kardex_cuadra_con_el_stock(): void
    {
        foreach (LoteProduccion::where('estado', 'Finalizado')->with('receta.ingredientes')->get() as $lote) {
            $factor = (float) $lote->cantidad_programada / (float) $lote->receta->rendimiento_unidades;
            foreach ($lote->receta->ingredientes as $linea) {
                $consumos = ConsumoLote::where('lote_produccion_id', $lote->id);
                $consumido = $linea->subproducto_id
                    ? (float) (clone $consumos)->whereNotNull('subproducto_lote_id')->sum('cantidad')
                    : (float) (clone $consumos)->whereIn('lote_insumo_id', \App\Models\Compras\LoteInsumo::where('insumo_id', $linea->insumo_id)->pluck('id'))->sum('cantidad');
                $this->assertEqualsWithDelta((float) $linea->cantidad_requerida * $factor, $consumido, 0.001, "{$lote->codigo_lote} no consume la BOM completa.");
            }
        }

        foreach (['insumo' => Insumo::all(), 'producto' => Producto::all()] as $tipo => $items) {
            foreach ($items as $item) {
                $ultimo = \App\Models\Inventario\MovimientoInventario::where('tipo_item', $tipo)->where('item_id', $item->id)->orderByDesc('fecha')->orderByDesc('id')->first();
                $this->assertEqualsWithDelta((float) $item->stock_actual, (float) ($ultimo?->stock_resultante ?? $item->stock_actual), 0.001, "Kardex no cuadra para {$item->nombre}.");
            }
        }

        foreach (LoteProduccion::where('estado', 'Finalizado')->get() as $lote) {
            $this->assertGreaterThanOrEqual(0.0, (float) $lote->cantidad_disponible);
            $this->assertLessThanOrEqual((float) $lote->cantidad_obtenida, (float) $lote->cantidad_disponible);
        }
    }
}
