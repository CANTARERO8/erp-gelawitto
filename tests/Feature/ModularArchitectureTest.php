<?php

namespace Tests\Feature;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Proveedor;
use App\Models\Catalogos\Cliente;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Finanzas\FinanzasMovimiento;
use App\Models\Produccion\Receta;
use App\Models\User;
use App\Models\Ventas\CajaTurno;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ModularArchitectureTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('email', 'admin@gelawitto.com')->firstOrFail();
    }

    public function test_catalogos_module_creates_product_and_insumo(): void
    {
        $unidad = UnidadMedida::firstOrFail();

        $prodResponse = $this->actingAs($this->adminUser)->post('/catalogos/productos', [
            'sku' => 'HEL-TEST-'.uniqid(),
            'nombre' => 'Gelato Pistacho Test',
            'sabor' => 'Pistacho Bronte',
            'presentacion' => 'Tina 1 Litro',
            'precio_venta' => 18.50,
            'costo_unitario' => 6.20,
            'stock_minimo' => 5,
        ]);
        $prodResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('productos', ['sabor' => 'Pistacho Bronte']);

        $insumoResponse = $this->actingAs($this->adminUser)->post('/catalogos/insumos', [
            'codigo' => 'INS-TEST-'.uniqid(),
            'nombre' => 'Pasta de Pistacho 100% Pura',
            'categoria' => 'Pastas y Bases',
            'unidad_id' => $unidad->id,
            'stock_minimo' => 2,
            'costo_promedio' => 35.00,
        ]);
        $insumoResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('insumos', ['nombre' => 'Pasta de Pistacho 100% Pura']);
    }

    public function test_compras_module_records_purchase_and_updates_kardex_and_finances(): void
    {
        $proveedor = Proveedor::firstOrFail();
        $insumo = Insumo::firstOrFail();
        $stockInicial = (float) $insumo->stock_actual;

        $response = $this->actingAs($this->adminUser)->post('/compras', [
            'proveedor_id' => $proveedor->id,
            'insumo_id' => $insumo->id,
            'cantidad' => 15.5,
            'costo_unitario' => 2.50,
            'modalidad_pago' => 'Pagado',
            'notas' => 'Prueba compra modular',
        ]);

        $response->assertSessionHasNoErrors();
        $insumo->refresh();
        $this->assertEquals(round($stockInicial + 15.5, 3), (float) $insumo->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'item_id' => $insumo->id,
            'tipo_movimiento' => 'Entrada por Compra',
        ]);
        $this->assertDatabaseHas('finanzas_movimientos', [
            'tipo' => 'Egreso',
            'estado' => 'Pagado',
        ]);
    }

    public function test_inventario_module_records_stock_adjustments(): void
    {
        $insumo = Insumo::where('stock_actual', '>', 5)->firstOrFail();
        $stockInicial = (float) $insumo->stock_actual;

        $response = $this->actingAs($this->adminUser)->post('/inventario/ajustes', [
            'tipo_item' => 'insumo',
            'item_id' => $insumo->id,
            'tipo_movimiento' => 'Merma',
            'cantidad' => 1.25,
            'motivo' => 'Envase quebrado en transporte',
        ]);

        $response->assertSessionHasNoErrors();
        $insumo->refresh();
        $this->assertEquals(round($stockInicial - 1.25, 3), (float) $insumo->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'tipo_movimiento' => 'Merma',
            'item_id' => $insumo->id,
        ]);
    }

    public function test_produccion_module_processes_batch_correctly(): void
    {
        $receta = Receta::with(['producto', 'ingredientes.insumo'])->firstOrFail();
        $producto = $receta->producto;
        $stockProdInicial = (float) $producto->stock_actual;

        $response = $this->actingAs($this->adminUser)->post('/produccion/lotes', [
            'receta_id' => $receta->id,
            'cantidad_programada' => 10,
            'cantidad_obtenida' => 9.8,
            'notas' => 'Producción de lote de prueba modular',
        ]);

        $response->assertSessionHasNoErrors();
        $producto->refresh();
        $this->assertEquals(round($stockProdInicial + 9.8, 3), (float) $producto->stock_actual);
        $this->assertDatabaseHas('lotes_produccion', [
            'receta_id' => $receta->id,
            'estado' => 'Finalizado',
        ]);
    }

    public function test_ventas_module_opens_shift_processes_sale_and_closes_shift(): void
    {
        // Close any existing open shift to start clean
        CajaTurno::where('estado', 'Abierta')->update(['estado' => 'Cerrada', 'fecha_cierre' => now()]);

        // 1. Abrir caja
        $openResponse = $this->actingAs($this->adminUser)->post('/ventas/caja', [
            'accion' => 'abrir',
            'monto_inicial' => 100.00,
            'observaciones' => 'Apertura de turno modular test',
        ]);
        $openResponse->assertSessionHasNoErrors();
        $turno = CajaTurno::where('estado', 'Abierta')->firstOrFail();
        $this->assertEquals('100.00', (string) $turno->monto_inicial);

        // 2. Realizar venta
        $producto = Producto::where('stock_actual', '>', 5)->firstOrFail();
        $cliente = Cliente::firstOrFail();
        $stockAntes = (float) $producto->stock_actual;

        $ventaResponse = $this->actingAs($this->adminUser)->post('/ventas', [
            'producto_id' => $producto->id,
            'cliente_id' => $cliente->id,
            'cantidad' => 2,
            'metodo_pago' => 'Efectivo',
        ]);
        $ventaResponse->assertSessionHasNoErrors();

        $producto->refresh();
        $turno->refresh();
        $this->assertEquals(round($stockAntes - 2, 3), (float) $producto->stock_actual);
        $this->assertTrue((float) $turno->ventas_efectivo > 0);

        // 3. Cerrar caja
        $closeResponse = $this->actingAs($this->adminUser)->post('/ventas/caja', [
            'accion' => 'cerrar',
            'monto_declarado' => (float) $turno->monto_esperado,
            'observaciones' => 'Cierre perfecto sin discrepancia',
        ]);
        $closeResponse->assertSessionHasNoErrors();
        $turno->refresh();
        $this->assertEquals('Cerrada', $turno->estado);
        $this->assertEquals('0.00', (string) $turno->diferencia);
    }

    public function test_finanzas_module_records_and_liquidates_accounts_payable(): void
    {
        $gastoResponse = $this->actingAs($this->adminUser)->post('/finanzas/gastos', [
            'categoria' => 'Mantenimiento Preventivo',
            'concepto' => 'Mantenimiento de Mantecadora Carpigiani',
            'monto' => 450.00,
            'estado' => 'Pendiente',
        ]);
        $gastoResponse->assertSessionHasNoErrors();

        $mov = FinanzasMovimiento::where('concepto', 'Mantenimiento de Mantecadora Carpigiani')->firstOrFail();
        $this->assertEquals('Pendiente', $mov->estado);

        $liquidarResponse = $this->actingAs($this->adminUser)->post("/finanzas/liquidar/{$mov->id}");
        $liquidarResponse->assertSessionHasNoErrors();

        $mov->refresh();
        $this->assertEquals('Pagado', $mov->estado);
        $this->assertEquals('Egreso', $mov->tipo);
    }

    public function test_seguridad_module_manages_users_and_logs_audit(): void
    {
        $newUserResponse = $this->actingAs($this->adminUser)->post('/seguridad/usuarios', [
            'name' => 'Carlos Gelataio Test',
            'email' => 'carlos.test.'.uniqid().'@gelawitto.com',
            'password' => 'secreto123',
            'rol' => 'Producción',
        ]);
        $newUserResponse->assertSessionHasNoErrors();

        $user = User::where('name', 'Carlos Gelataio Test')->firstOrFail();
        $this->assertTrue($user->activo);

        $toggleResponse = $this->actingAs($this->adminUser)->post("/seguridad/usuarios/{$user->id}/toggle");
        $toggleResponse->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertFalse($user->activo);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'modulo' => 'Seguridad',
            'accion' => 'Cambio de Estado',
        ]);
    }
}
