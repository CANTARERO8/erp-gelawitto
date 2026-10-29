<?php

namespace Tests\Feature;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Proveedor;
use App\Models\Catalogos\Cliente;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Finanzas\FinanzasMovimiento;
use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Models\User;
use App\Models\Ventas\CajaTurno;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_inertia_payloads_match_expected_schema_across_all_modules(): void
    {
        $this->actingAs($this->adminUser)->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Erp/Dashboard')
                ->has('kpis.ventas_hoy')
                ->has('kpis.ventas_acumuladas')
                ->has('kpis.valor_inventario_insumos')
                ->has('kpis.valor_inventario_productos')
                ->has('kpis.alertas_stock')
                ->has('kpis.cuentas_por_pagar')
                ->has('insumosCriticos')
                ->has('ultimosMovimientos')
                ->has('productosRentabilidad')
            );

        $this->actingAs($this->adminUser)->get('/catalogos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Erp/Catalogos')
                ->has('productos.0', fn (Assert $p) => $p
                    ->hasAll(['id', 'sku', 'nombre', 'sabor', 'presentacion', 'precio_venta', 'costo_unitario', 'stock_actual', 'stock_minimo', 'activo'])
                )
                ->has('insumos.0', fn (Assert $i) => $i
                    ->hasAll(['id', 'codigo', 'nombre', 'categoria', 'unidad', 'unidad_id', 'stock_actual', 'stock_minimo', 'costo_promedio', 'activo'])
                )
                ->has('proveedores')
                ->has('clientes')
                ->has('unidades')
            );

        $this->actingAs($this->adminUser)->get('/compras')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Erp/Compras')
                ->has('ordenes.0', fn (Assert $o) => $o
                    ->hasAll(['id', 'folio', 'proveedor_nombre', 'comprador_nombre', 'fecha_emision', 'estado', 'total', 'notas'])
                )
                ->has('proveedores')
                ->has('insumos')
            );

        $this->actingAs($this->adminUser)->get('/inventario')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Erp/Inventario')
                ->has('insumos')
                ->has('productos')
                ->has('kardex.0', fn (Assert $k) => $k
                    ->hasAll(['id', 'fecha', 'tipo_item', 'nombre_item', 'tipo_movimiento', 'referencia', 'cantidad', 'stock_resultante', 'costo_unitario', 'responsable'])
                )
            );

        $this->actingAs($this->adminUser)->get('/produccion')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Erp/Produccion')
                ->has('recetas.0', fn (Assert $r) => $r
                    ->hasAll(['id', 'nombre', 'producto_nombre', 'producto_sku', 'sabor', 'rendimiento_unidades', 'tiempo_preparacion_min', 'instrucciones', 'ingredientes'])
                )
                ->has('lotes.0', fn (Assert $l) => $l
                    ->hasAll(['id', 'codigo_lote', 'producto_nombre', 'producto_sku', 'operador_nombre', 'fecha_produccion', 'cantidad_programada', 'cantidad_obtenida', 'merma', 'costo_total_lote', 'costo_unitario_resultante', 'estado', 'notas'])
                )
            );

        $this->actingAs($this->adminUser)->get('/ventas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Erp/Ventas')
                ->has('turnoActivo')
                ->has('ventas')
                ->has('productos')
                ->has('clientes')
                ->has('historialTurnos')
            );

        $this->actingAs($this->adminUser)->get('/finanzas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Erp/Finanzas')
                ->has('movimientos.0', fn (Assert $m) => $m
                    ->hasAll(['id', 'folio', 'fecha', 'tipo', 'categoria', 'concepto', 'referencia', 'monto', 'estado', 'fecha_vencimiento', 'responsable'])
                )
                ->has('resumen', fn (Assert $r) => $r
                    ->hasAll(['ingresos', 'egresos', 'flujo_neto', 'cuentas_por_pagar'])
                )
            );

        $this->actingAs($this->adminUser)->get('/seguridad')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Erp/Seguridad')
                ->has('usuarios.0', fn (Assert $u) => $u
                    ->hasAll(['id', 'name', 'email', 'rol', 'activo', 'ultimo_acceso'])
                )
                ->has('bitacora.0', fn (Assert $b) => $b
                    ->hasAll(['id', 'modulo', 'accion', 'descripcion', 'ip_address', 'usuario_nombre', 'usuario_rol', 'created_at'])
                )
            );
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
        $costoInicial = (float) $insumo->costo_promedio;

        $cantidadComprada = 15.5;
        $costoUnitarioCompra = 2.50;
        $subtotal = round($cantidadComprada * $costoUnitarioCompra, 2);

        // Paso 1: la orden de compra no mueve stock ni finanzas.
        $this->actingAs($this->adminUser)->post('/compras', [
            'proveedor_id' => $proveedor->id,
            'insumo_id' => $insumo->id,
            'cantidad' => $cantidadComprada,
            'costo_unitario' => $costoUnitarioCompra,
            'notas' => 'Prueba compra modular',
        ])->assertSessionHasNoErrors();

        $orden = \App\Models\Compras\OrdenCompra::latest('id')->firstOrFail();
        $this->assertEquals('Pendiente', $orden->estado);
        $this->assertEquals($stockInicial, (float) $insumo->fresh()->stock_actual);

        // Paso 2: la recepción en almacén incrementa stock, Kardex y finanzas.
        $this->actingAs($this->adminUser)->post("/compras/{$orden->id}/recibir", [
            'modalidad_pago' => 'Pagado',
        ])->assertSessionHasNoErrors();

        $insumo->refresh();
        $this->assertEquals('Recibida', $orden->fresh()->estado);

        $nuevoStockEsperado = round($stockInicial + $cantidadComprada, 3);
        $nuevoCostoPromedioEsperado = round((($stockInicial * $costoInicial) + $subtotal) / $nuevoStockEsperado, 2);

        $this->assertEquals($nuevoStockEsperado, (float) $insumo->stock_actual);
        $this->assertEquals($nuevoCostoPromedioEsperado, (float) $insumo->costo_promedio);
        $this->assertDatabaseHas('movimientos_inventario', [
            'item_id' => $insumo->id,
            'tipo_movimiento' => 'Entrada por Compra',
        ]);
        $this->assertDatabaseHas('finanzas_movimientos', [
            'tipo' => 'Egreso',
            'estado' => 'Pagado',
        ]);
    }

    public function test_inventario_module_records_stock_adjustments_and_blocks_negative_stock(): void
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

        // Attempt to deduct more than available stock
        $excessResponse = $this->actingAs($this->adminUser)->post('/inventario/ajustes', [
            'tipo_item' => 'insumo',
            'item_id' => $insumo->id,
            'tipo_movimiento' => 'Ajuste Negativo',
            'cantidad' => 999999,
            'motivo' => 'Intento de stock negativo',
        ]);
        $excessResponse->assertSessionHasErrors('cantidad');
    }

    public function test_produccion_module_processes_batch_and_blocks_insufficient_ingredients(): void
    {
        $receta = Receta::with(['producto', 'ingredientes.insumo'])->firstOrFail();
        $producto = $receta->producto;
        $stockProdInicial = (float) $producto->stock_actual;

        // Paso 1: programar la orden no mueve producto ni insumos.
        $this->actingAs($this->adminUser)->post('/produccion/lotes', [
            'receta_id' => $receta->id,
            'cantidad_programada' => 10,
            'notas' => 'Producción de lote de prueba modular',
        ])->assertSessionHasNoErrors();

        $lote = LoteProduccion::latest('id')->firstOrFail();
        $this->assertEquals('Programada', $lote->estado);
        $this->assertEquals($stockProdInicial, (float) $producto->fresh()->stock_actual);

        // Paso 2: ejecutar la orden descuenta insumos e ingresa el producto.
        $this->actingAs($this->adminUser)->post("/produccion/lotes/{$lote->id}/ejecutar", [
            'cantidad_obtenida' => 9.8,
            'notas' => 'Ejecución de prueba',
        ])->assertSessionHasNoErrors();

        $producto->refresh();
        $this->assertEquals(round($stockProdInicial + 9.8, 3), (float) $producto->stock_actual);
        $this->assertDatabaseHas('lotes_produccion', [
            'id' => $lote->id,
            'estado' => 'Finalizado',
        ]);

        // Una orden programada con insumos insuficientes se puede crear, pero no ejecutar.
        $this->actingAs($this->adminUser)->post('/produccion/lotes', [
            'receta_id' => $receta->id,
            'cantidad_programada' => 100000,
            'notas' => 'Lote imposible por falta de insumos',
        ])->assertSessionHasNoErrors();

        $loteImposible = LoteProduccion::latest('id')->firstOrFail();
        $this->actingAs($this->adminUser)->post("/produccion/lotes/{$loteImposible->id}/ejecutar", [
            'cantidad_obtenida' => 100000,
        ])->assertSessionHasErrors('cantidad_obtenida');
    }

    public function test_ventas_module_opens_shift_processes_sale_and_closes_shift(): void
    {
        // Close any existing open shift to start clean
        CajaTurno::where('estado', 'Abierta')->update(['estado' => 'Cerrada', 'fecha_cierre' => now()]);

        $producto = Producto::where('stock_actual', '>', 5)->firstOrFail();
        $cliente = Cliente::firstOrFail();

        // Attempting sale without open shift must fail
        $noShiftResponse = $this->actingAs($this->adminUser)->post('/ventas', [
            'producto_id' => $producto->id,
            'cliente_id' => $cliente->id,
            'cantidad' => 1,
            'metodo_pago' => 'Efectivo',
        ]);
        $noShiftResponse->assertSessionHasErrors('producto_id');

        // 1. Abrir caja
        $openResponse = $this->actingAs($this->adminUser)->post('/ventas/caja', [
            'accion' => 'abrir',
            'monto_inicial' => 100.00,
            'observaciones' => 'Apertura de turno modular test',
        ]);
        $openResponse->assertSessionHasNoErrors();
        $turno = CajaTurno::where('estado', 'Abierta')->firstOrFail();
        $this->assertEquals('100.00', (string) $turno->monto_inicial);

        // Attempting to open a second shift while one is open must fail
        $duplicateShiftResponse = $this->actingAs($this->adminUser)->post('/ventas/caja', [
            'accion' => 'abrir',
            'monto_inicial' => 50.00,
        ]);
        $duplicateShiftResponse->assertSessionHasErrors('monto_inicial');

        // 2. Realizar venta
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

    public function test_seguridad_module_manages_users_prevents_self_lockout_and_logs_audit(): void
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

        // Self-deactivation must be blocked
        $selfToggle = $this->actingAs($this->adminUser)->post("/seguridad/usuarios/{$this->adminUser->id}/toggle");
        $selfToggle->assertSessionHasErrors('id');

        $this->assertDatabaseHas('bitacora_auditoria', [
            'modulo' => 'Seguridad',
            'accion' => 'Cambio de Estado',
        ]);
    }

    public function test_auth_module_enforces_rate_limiting_and_blocks_inactive_users(): void
    {
        $inactiveUser = User::create([
            'name' => 'Inactivo Test',
            'email' => 'inactivo.test@gelawitto.com',
            'password' => bcrypt('password123'),
            'rol' => 'Cajero',
            'activo' => false,
        ]);

        // Inactive user cannot login even with valid password
        $inactiveResponse = $this->post('/login', [
            'email' => $inactiveUser->email,
            'password' => 'password123',
        ]);
        $inactiveResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // 5 failed attempts trigger lockout timer
        \Illuminate\Support\Facades\RateLimiter::clear('gelawitto-login|127.0.0.1');

        for ($i = 1; $i <= 5; $i++) {
            $failResponse = $this->post('/login', [
                'email' => 'admin@gelawitto.com',
                'password' => 'wrong-password-'.$i,
            ]);

            if ($i < 5) {
                $failResponse->assertSessionHasErrors(['email', 'attempts']);
            } else {
                $failResponse->assertSessionHasErrors(['lockout', 'timer']);
            }
        }

        \Illuminate\Support\Facades\RateLimiter::clear('gelawitto-login|127.0.0.1');
    }

    public function test_role_based_access_control_restricts_unauthorized_modules(): void
    {
        $cajero = User::where('email', 'caja@gelawitto.com')->firstOrFail();
        $produccion = User::where('email', 'produccion@gelawitto.com')->firstOrFail();
        $contador = User::where('email', 'finanzas@gelawitto.com')->firstOrFail();

        // Cajero: autorizado en Dashboard, Ventas e Inventario; denegado en Seguridad, Finanzas, Producción
        $this->actingAs($cajero)->get('/dashboard')->assertOk();
        $this->actingAs($cajero)->get('/ventas')->assertOk();
        $this->actingAs($cajero)->get('/inventario')->assertOk();
        $this->actingAs($cajero)->get('/seguridad')->assertStatus(403);
        $this->actingAs($cajero)->get('/finanzas')->assertStatus(403);
        $this->actingAs($cajero)->get('/produccion')->assertStatus(403);

        // Producción: autorizado en Producción, Calidad, Catálogos e Inventario; denegado en Ventas, Finanzas, Seguridad
        $this->actingAs($produccion)->get('/produccion')->assertOk();
        $this->actingAs($produccion)->get('/calidad')->assertOk();
        $this->actingAs($produccion)->get('/catalogos')->assertOk();
        $this->actingAs($produccion)->get('/ventas')->assertStatus(403);
        $this->actingAs($produccion)->get('/seguridad')->assertStatus(403);

        // Contador: autorizado en Finanzas, Compras, Catálogos e Inventario; denegado en Producción, Calidad, Seguridad
        $this->actingAs($contador)->get('/finanzas')->assertOk();
        $this->actingAs($contador)->get('/compras')->assertOk();
        $this->actingAs($contador)->get('/produccion')->assertStatus(403);
        $this->actingAs($contador)->get('/calidad')->assertStatus(403);
        $this->actingAs($contador)->get('/seguridad')->assertStatus(403);
    }
}
