<?php

namespace Tests\Feature;

use App\Models\Catalogos\Cliente;
use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Proveedor;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Compras\OrdenCompra;
use App\Models\Configuracion\Parametro;
use App\Models\Finanzas\AsientoContable;
use App\Models\Finanzas\FinanzasMovimiento;
use App\Models\Finanzas\PartidaAsiento;
use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Models\User;
use App\Models\Ventas\CajaTurno;
use App\Repositories\Ventas\VentasRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ErpCompletoTest extends TestCase
{
    use DatabaseTransactions;

    public function test_puede_crear_proveedor_cliente_y_receta_maestra_con_ingredientes(): void
    {
        $admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();

        $this->actingAs($admin)
            ->post('/catalogos/proveedores', [
                'nombre' => 'Lácteos del Norte S.A.',
                'ruc_nit' => 'J0310000998877',
                'contacto' => 'Ing. Roberto Rivas',
                'telefono' => '+505 2255-9900',
                'correo' => 'ventas@lacteosdelnorte.com.ni',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('proveedores', [
            'ruc_nit' => 'J0310000998877',
            'nombre' => 'Lácteos del Norte S.A.',
        ]);

        $this->actingAs($admin)
            ->post('/catalogos/clientes', [
                'nombre' => 'Hotel Darío Colonial',
                'tipo' => 'Mayorista',
                'identificacion' => 'J0310000112233',
                'telefono' => '+505 2244-1122',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('clientes', [
            'nombre' => 'Hotel Darío Colonial',
            'tipo' => 'Mayorista',
        ]);

        $unidad = UnidadMedida::firstOrFail();
        $productoNuevo = Producto::create([
            'sku' => 'GEL-MAN-050',
            'nombre' => 'Sorbete Mango Tropical',
            'sabor' => 'Mango',
            'presentacion' => 'Tina 5 Litros',
            'precio_venta' => 890.00,
            'costo_unitario' => 310.00,
            'stock_actual' => 0,
            'stock_minimo' => 5,
            'temperatura_conservacion_c' => -18.0,
            'activo' => true,
        ]);

        $insumos = Insumo::take(2)->get();

        $this->actingAs($admin)
            ->post('/produccion/recetas', [
                'producto_id' => $productoNuevo->id,
                'nombre' => 'Fórmula Estándar Mango 20L',
                'rendimiento_unidades' => 20,
                'tiempo_preparacion_min' => 45,
                'instrucciones' => 'Pasteurizar base y madurar 4 horas.',
                'ingredientes' => [
                    [
                        'insumo_id' => $insumos[0]->id,
                        'cantidad_requerida' => 5.0,
                        'merma_estimada_porcentaje' => 2.0,
                    ],
                    [
                        'insumo_id' => $insumos[1]->id,
                        'cantidad_requerida' => 2.5,
                        'merma_estimada_porcentaje' => 0.0,
                    ],
                ],
            ])
            ->assertCreated();

        $receta = Receta::where('nombre', 'Fórmula Estándar Mango 20L')->first();
        $this->assertNotNull($receta);
        $this->assertCount(2, $receta->ingredientes);
    }

    public function test_cuarentena_de_calidad_bloquea_venta_hasta_liberacion(): void
    {
        $admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $receta = Receta::with('producto')->firstOrFail();
        $producto = $receta->producto;

        $producto->update(['stock_actual' => 0]);
        LoteProduccion::where('producto_id', $producto->id)->update(['estado_calidad' => 'Liberado']);

        $this->actingAs($admin)->post('/produccion/lotes', [
            'receta_id' => $receta->id,
            'cantidad_programada' => 10,
            'notas' => 'Lote prueba cuarentena',
        ])->assertCreated();

        $lote = LoteProduccion::latest('id')->firstOrFail();

        $this->actingAs($admin)->post("/produccion/lotes/{$lote->id}/ejecutar", [
            'cantidad_obtenida' => 10,
            'notas' => 'Ejecutado completo',
        ])->assertOk();

        $this->assertEquals(10.0, (float) $producto->fresh()->stock_actual);
        $this->assertEquals('Pendiente', $lote->fresh()->estado_calidad);

        $cliente = Cliente::firstOrFail();
        $this->actingAs($admin)
            ->post('/ventas', [
                'cliente_id' => $cliente->id,
                'producto_id' => $producto->id,
                'cantidad' => 2,
                'metodo_pago' => 'Efectivo',
            ])
            ->assertJsonValidationErrors('cantidad');

        $this->actingAs($admin)
            ->post("/calidad/lotes/{$lote->id}/inspeccion", [
                'unidades_inspeccionadas' => 10,
                'unidades_no_conformes' => 0,
                'disposicion' => 'Liberación',
                'notas' => 'Aprobado para venta',
            ])
            ->assertOk();

        $this->assertEquals('Liberado', $lote->fresh()->estado_calidad);

        $this->actingAs($admin)
            ->post('/ventas', [
                'cliente_id' => $cliente->id,
                'producto_id' => $producto->id,
                'cantidad' => 2,
                'metodo_pago' => 'Efectivo',
            ])
            ->assertCreated();

        $this->assertEquals(8.0, (float) $producto->fresh()->stock_actual);
    }

    public function test_compras_y_ventas_multilinea_con_iva_credito_y_partida_doble_cuadrada(): void
    {
        $admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $proveedor = Proveedor::firstOrFail();
        $insumos = Insumo::take(2)->get();

        $this->actingAs($admin)
            ->post('/compras', [
                'proveedor_id' => $proveedor->id,
                'aplicar_iva' => true,
                'notas' => 'Compra multi-insumo con IVA',
                'lineas' => [
                    [
                        'insumo_id' => $insumos[0]->id,
                        'cantidad' => 10,
                        'costo_unitario' => 100.00,
                    ],
                    [
                        'insumo_id' => $insumos[1]->id,
                        'cantidad' => 5,
                        'costo_unitario' => 200.00,
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('ordenes_compra', [
            'notas' => 'Compra multi-insumo con IVA',
            'subtotal' => 2000.00,
            'impuestos' => 300.00,
            'total' => 2300.00,
        ]);

        $ordenId = OrdenCompra::latest('id')->value('id');

        $this->actingAs($admin)
            ->post("/compras/{$ordenId}/recibir", [
                'modalidad_pago' => 'Pendiente',
            ])
            ->assertOk();

        $clienteMayorista = Cliente::where('tipo', 'Mayorista')->firstOrFail();
        $productos = Producto::where('stock_actual', '>=', 5)->take(2)->get();

        $turno = CajaTurno::where('estado', 'Abierta')->firstOrFail();
        $efectivoAntes = (float) $turno->ventas_efectivo;

        $this->actingAs($admin)
            ->post('/ventas', [
                'cliente_id' => $clienteMayorista->id,
                'metodo_pago' => 'Crédito',
                'aplicar_iva' => true,
                'lineas' => [
                    [
                        'producto_id' => $productos[0]->id,
                        'cantidad' => 2,
                    ],
                    [
                        'producto_id' => $productos[1]->id,
                        'cantidad' => 1,
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertEquals($efectivoAntes, (float) $turno->fresh()->ventas_efectivo);

        $cxc = FinanzasMovimiento::where('tipo', 'Cuenta por Cobrar')
            ->where('estado', 'Pendiente')
            ->latest('id')
            ->first();

        $this->assertNotNull($cxc);

        $this->actingAs($admin)
            ->post("/finanzas/liquidar/{$cxc->id}")
            ->assertOk();

        $this->assertEquals('Pagado', $cxc->fresh()->estado);
        $this->assertEquals('Ingreso', $cxc->fresh()->tipo);

        $asientos = AsientoContable::with('partidas')->get();
        $this->assertGreaterThanOrEqual(8, $asientos->count());

        foreach ($asientos as $asiento) {
            $sumaDebe = round((float) $asiento->partidas->sum('debe'), 2);
            $sumaHaber = round((float) $asiento->partidas->sum('haber'), 2);

            $this->assertEquals(
                (float) $asiento->total_debe,
                (float) $asiento->total_haber,
                "El asiento {$asiento->numero_asiento} no está cuadrado en cabecera."
            );
            $this->assertEquals(
                $sumaDebe,
                $sumaHaber,
                "Las partidas del asiento {$asiento->numero_asiento} no cuadran Debe ({$sumaDebe}) vs Haber ({$sumaHaber})."
            );
        }

        $totalGlobalDebe = round((float) PartidaAsiento::sum('debe'), 2);
        $totalGlobalHaber = round((float) PartidaAsiento::sum('haber'), 2);
        $this->assertEquals($totalGlobalDebe, $totalGlobalHaber);
    }

    public function test_iva_dinamico_desde_tabla_parametros_y_folios_atomicos_con_secuencias(): void
    {
        $admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $proveedor = Proveedor::firstOrFail();
        $insumo = Insumo::firstOrFail();

        Parametro::where('clave', 'iva_porcentaje')->update(['valor' => '10']);

        $this->actingAs($admin)
            ->post('/api/compras', [
                'proveedor_id' => $proveedor->id,
                'aplicar_iva' => true,
                'notas' => 'Compra con IVA dinámico al 10%',
                'lineas' => [
                    [
                        'insumo_id' => $insumo->id,
                        'cantidad' => 10,
                        'costo_unitario' => 100.00,
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('ordenes_compra', [
            'notas' => 'Compra con IVA dinámico al 10%',
            'subtotal' => 1000.00,
            'impuestos' => 100.00,
            'total' => 1100.00,
        ]);

        $ventasRepo = app(VentasRepository::class);
        $folio1 = $ventasRepo->getNextFolioVenta();
        $folio2 = $ventasRepo->getNextFolioVenta();
        $this->assertNotEquals($folio1, $folio2);
    }

    public function test_endpoint_me_devuelve_modulos_permitidos_desde_base_de_datos(): void
    {
        $cajero = User::where('email', 'caja@gelawitto.com')->firstOrFail();

        $response = $this->actingAs($cajero)->get('/api/me');
        $response->assertOk()
            ->assertJsonPath('user.rol', 'Cajero');

        $claves = collect($response->json('modulos'))->pluck('clave')->all();
        $this->assertEquals(['dashboard', 'inventario', 'ventas'], $claves);
    }
}
