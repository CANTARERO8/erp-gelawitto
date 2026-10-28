<?php

namespace Tests\Feature;

use App\Models\Catalogos\Cliente;
use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Proveedor;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Finanzas\AsientoContable;
use App\Models\Finanzas\FinanzasMovimiento;
use App\Models\Finanzas\PartidaAsiento;
use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Models\User;
use App\Models\Ventas\CajaTurno;
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
            ->assertRedirect();

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
            ->assertRedirect();

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
            ->assertRedirect();

        $receta = Receta::where('nombre', 'Fórmula Estándar Mango 20L')->first();
        $this->assertNotNull($receta);
        $this->assertCount(2, $receta->ingredientes);
    }

    public function test_cuarentena_de_calidad_bloquea_venta_hasta_liberacion(): void
    {
        $admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $receta = Receta::with('producto')->firstOrFail();
        $producto = $receta->producto;

        // Dejamos stock inicial en 0 para aislar las unidades del nuevo lote
        $producto->update(['stock_actual' => 0]);
        LoteProduccion::where('producto_id', $producto->id)->update(['estado_calidad' => 'Liberado']);

        // 1. Programar y ejecutar lote de 10 unidades (queda en estado_calidad = Pendiente)
        $this->actingAs($admin)->post('/produccion/lotes', [
            'receta_id' => $receta->id,
            'cantidad_programada' => 10,
            'notas' => 'Lote prueba cuarentena',
        ])->assertRedirect();

        $lote = LoteProduccion::latest('id')->firstOrFail();

        $this->actingAs($admin)->post("/produccion/lotes/{$lote->id}/ejecutar", [
            'cantidad_obtenida' => 10,
            'notas' => 'Ejecutado completo',
        ])->assertRedirect();

        $this->assertEquals(10.0, (float) $producto->fresh()->stock_actual);
        $this->assertEquals('Pendiente', $lote->fresh()->estado_calidad);

        // 2. Intentar vender 2 unidades antes de liberar en Calidad -> debe fallar por cuarentena
        $cliente = Cliente::firstOrFail();
        $this->actingAs($admin)
            ->post('/ventas', [
                'cliente_id' => $cliente->id,
                'producto_id' => $producto->id,
                'cantidad' => 2,
                'metodo_pago' => 'Efectivo',
            ])
            ->assertSessionHasErrors('cantidad');

        // 3. Calidad inspecciona y libera el lote
        $this->actingAs($admin)
            ->post("/calidad/lotes/{$lote->id}/inspeccion", [
                'unidades_inspeccionadas' => 10,
                'unidades_no_conformes' => 0,
                'disposicion' => 'Liberación',
                'notas' => 'Aprobado para venta',
            ])
            ->assertRedirect();

        $this->assertEquals('Liberado', $lote->fresh()->estado_calidad);

        // 4. Ahora la venta sí procede
        $this->actingAs($admin)
            ->post('/ventas', [
                'cliente_id' => $cliente->id,
                'producto_id' => $producto->id,
                'cantidad' => 2,
                'metodo_pago' => 'Efectivo',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertEquals(8.0, (float) $producto->fresh()->stock_actual);
    }

    public function test_compras_y_ventas_multilinea_con_iva_credito_y_partida_doble_cuadrada(): void
    {
        $admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $proveedor = Proveedor::firstOrFail();
        $insumos = Insumo::take(2)->get();

        // 1. Crear Orden de Compra multi-línea con IVA (15%)
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
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        // Subtotal = 1000 + 1000 = 2000; IVA 15% = 300; Total = 2300
        $this->assertDatabaseHas('ordenes_compra', [
            'notas' => 'Compra multi-insumo con IVA',
            'subtotal' => 2000.00,
            'impuestos' => 300.00,
            'total' => 2300.00,
        ]);

        $ordenId = \App\Models\Compras\OrdenCompra::latest('id')->value('id');

        // Recibir la orden a crédito
        $this->actingAs($admin)
            ->post("/compras/{$ordenId}/recibir", [
                'modalidad_pago' => 'Pendiente',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        // 2. Venta multi-línea a Crédito con IVA (15%)
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
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        // La venta a crédito no debe inflar el efectivo en caja, pero sí crear una Cuenta por Cobrar
        $this->assertEquals($efectivoAntes, (float) $turno->fresh()->ventas_efectivo);

        $cxc = FinanzasMovimiento::where('tipo', 'Cuenta por Cobrar')
            ->where('estado', 'Pendiente')
            ->latest('id')
            ->first();

        $this->assertNotNull($cxc);

        // 3. Liquidar (cobrar) la Cuenta por Cobrar en Finanzas
        $this->actingAs($admin)
            ->post("/finanzas/liquidar/{$cxc->id}")
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertEquals('Pagado', $cxc->fresh()->estado);
        $this->assertEquals('Ingreso', $cxc->fresh()->tipo);

        // 4. Verificar que todos los asientos contables cumplen Partida Doble (Debe == Haber)
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
}
