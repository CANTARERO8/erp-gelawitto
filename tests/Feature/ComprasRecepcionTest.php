<?php

namespace Tests\Feature;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Proveedor;
use App\Models\Compras\OrdenCompra;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ComprasRecepcionTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Proveedor $proveedor;

    private Insumo $insumo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $this->proveedor = Proveedor::firstOrFail();
        $this->insumo = Insumo::firstOrFail();
    }

    public function test_crear_orden_no_mueve_stock_ni_genera_movimientos(): void
    {
        $stockInicial = (float) $this->insumo->stock_actual;
        $movimientosAntes = \DB::table('movimientos_inventario')->count();
        $finanzasAntes = \DB::table('finanzas_movimientos')->count();

        $this->actingAs($this->admin)->post('/compras', [
            'proveedor_id' => $this->proveedor->id,
            'insumo_id' => $this->insumo->id,
            'cantidad' => 4,
            'costo_unitario' => 3,
        ])->assertSessionHasNoErrors();

        $orden = OrdenCompra::latest('id')->firstOrFail();

        $this->assertEquals('Pendiente', $orden->estado);
        $this->assertNull($orden->fecha_recepcion);
        $this->assertEquals(12.00, (float) $orden->total);
        $this->assertEquals($stockInicial, (float) $this->insumo->fresh()->stock_actual);
        $this->assertEquals($movimientosAntes, \DB::table('movimientos_inventario')->count());
        $this->assertEquals($finanzasAntes, \DB::table('finanzas_movimientos')->count());
    }

    public function test_recibir_orden_pendiente_a_credito_genera_cuenta_por_pagar(): void
    {
        $stockInicial = (float) $this->insumo->stock_actual;

        $this->actingAs($this->admin)->post('/compras', [
            'proveedor_id' => $this->proveedor->id,
            'insumo_id' => $this->insumo->id,
            'cantidad' => 2,
            'costo_unitario' => 5,
        ]);
        $orden = OrdenCompra::latest('id')->firstOrFail();

        $this->actingAs($this->admin)->post("/compras/{$orden->id}/recibir", [
            'modalidad_pago' => 'Pendiente',
            'notas' => 'Factura 1234',
        ])->assertSessionHasNoErrors();

        $orden->refresh();
        $this->assertEquals('Recibida', $orden->estado);
        $this->assertNotNull($orden->fecha_recepcion);
        $this->assertStringContainsString('Recepción: Factura 1234', $orden->notas);
        $this->assertEquals($stockInicial + 2, (float) $this->insumo->fresh()->stock_actual);
        $this->assertDatabaseHas('finanzas_movimientos', [
            'referencia' => $orden->folio,
            'tipo' => 'Cuenta por Pagar',
            'estado' => 'Pendiente',
            'monto' => 10.00,
        ]);
    }

    public function test_recibir_la_misma_orden_dos_veces_es_rechazado(): void
    {
        $this->actingAs($this->admin)->post('/compras', [
            'proveedor_id' => $this->proveedor->id,
            'insumo_id' => $this->insumo->id,
            'cantidad' => 1,
            'costo_unitario' => 2,
        ]);
        $orden = OrdenCompra::latest('id')->firstOrFail();

        $this->actingAs($this->admin)->post("/compras/{$orden->id}/recibir", ['modalidad_pago' => 'Pagado'])
            ->assertSessionHasNoErrors();

        $stockTrasPrimera = (float) $this->insumo->fresh()->stock_actual;

        $this->actingAs($this->admin)->post("/compras/{$orden->id}/recibir", ['modalidad_pago' => 'Pagado'])
            ->assertSessionHasErrors('orden');

        $this->assertEquals($stockTrasPrimera, (float) $this->insumo->fresh()->stock_actual);
    }
}
