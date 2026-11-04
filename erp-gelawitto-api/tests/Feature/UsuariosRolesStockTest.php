<?php

namespace Tests\Feature;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UsuariosRolesStockTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $produccion;
    protected User $cajero;
    protected User $contador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $this->produccion = User::where('email', 'produccion@gelawitto.com')->firstOrFail();
        $this->cajero = User::where('email', 'caja@gelawitto.com')->firstOrFail();
        $this->contador = User::where('email', 'finanzas@gelawitto.com')->firstOrFail();
    }

    public function test_todos_los_usuarios_tienen_credenciales_rol_y_estado_activo(): void
    {
        $usuarios = [$this->admin, $this->produccion, $this->cajero, $this->contador];

        foreach ($usuarios as $u) {
            $this->assertTrue($u->activo, "El usuario {$u->email} debe estar activo.");
            $this->assertNotEmpty($u->name);
            $this->assertNotEmpty($u->rol);
            $this->assertNotNull($u->rol_id, "El usuario {$u->email} debe tener rol_id asignado en BD.");
        }
    }

    public function test_administrador_acceso_total_y_stock_completo(): void
    {
        $me = $this->actingAs($this->admin)->getJson('/me');
        $me->assertOk()
            ->assertJsonPath('user.rol', 'Administrador')
            ->assertJsonPath('user.email', 'admin@gelawitto.com');

        $this->assertCount(9, $me->json('modulos'), 'El administrador debe tener acceso a los 9 módulos.');

        $inv = $this->actingAs($this->admin)->getJson('/inventario');
        $inv->assertOk()
            ->assertJsonStructure(['insumos', 'productos', 'kardex']);

        $this->assertNotEmpty($inv->json('insumos'), 'Debe listar insumos con stock.');
        $this->assertNotEmpty($inv->json('productos'), 'Debe listar productos con stock.');

        $primerInsumo = $inv->json('insumos.0');
        $this->assertArrayHasKey('stock_actual', $primerInsumo);
        $this->assertArrayHasKey('costo_promedio', $primerInsumo);

        $primerProducto = $inv->json('productos.0');
        $this->assertArrayHasKey('stock_actual', $primerProducto);
        $this->assertArrayHasKey('precio_venta', $primerProducto);

        // Rutas completas permitidas
        $this->actingAs($this->admin)->getJson('/dashboard')->assertOk();
        $this->actingAs($this->admin)->getJson('/catalogos')->assertOk();
        $this->actingAs($this->admin)->getJson('/compras')->assertOk();
        $this->actingAs($this->admin)->getJson('/produccion')->assertOk();
        $this->actingAs($this->admin)->getJson('/calidad')->assertOk();
        $this->actingAs($this->admin)->getJson('/ventas')->assertOk();
        $this->actingAs($this->admin)->getJson('/finanzas')->assertOk();
        $this->actingAs($this->admin)->getJson('/seguridad')->assertOk();
    }

    public function test_produccion_acceso_modulos_operativos_y_stock_mrp(): void
    {
        $me = $this->actingAs($this->produccion)->getJson('/me');
        $me->assertOk()
            ->assertJsonPath('user.name', 'Moises Zelaya')
            ->assertJsonPath('user.rol', 'Producción');

        $clavesPermitidas = collect($me->json('modulos'))->pluck('clave')->all();
        sort($clavesPermitidas);
        $esperadas = ['calidad', 'catalogos', 'dashboard', 'inventario', 'produccion'];
        sort($esperadas);
        $this->assertEquals($esperadas, $clavesPermitidas);

        // Stock en inventario
        $inv = $this->actingAs($this->produccion)->getJson('/inventario');
        $inv->assertOk();
        $this->assertNotEmpty($inv->json('insumos'));
        $this->assertNotEmpty($inv->json('productos'));

        // Recetas y stocks en producción (para cálculo MRP)
        $prod = $this->actingAs($this->produccion)->getJson('/produccion');
        $prod->assertOk()
            ->assertJsonStructure(['recetas', 'lotes', 'productos', 'insumos', 'centros_trabajo', 'oee']);
        $this->assertNotEmpty($prod->json('recetas'));
        $this->assertNotEmpty($prod->json('insumos'));

        // Módulos prohibidos
        $this->actingAs($this->produccion)->getJson('/ventas')->assertStatus(403);
        $this->actingAs($this->produccion)->getJson('/compras')->assertStatus(403);
        $this->actingAs($this->produccion)->getJson('/finanzas')->assertStatus(403);
        $this->actingAs($this->produccion)->getJson('/seguridad')->assertStatus(403);
    }

    public function test_cajero_acceso_pos_stock_disponible_y_bloqueo_subensambles(): void
    {
        $me = $this->actingAs($this->cajero)->getJson('/me');
        $me->assertOk()
            ->assertJsonPath('user.name', 'Valeria Mendoza')
            ->assertJsonPath('user.rol', 'Cajero');

        $clavesPermitidas = collect($me->json('modulos'))->pluck('clave')->all();
        sort($clavesPermitidas);
        $esperadas = ['dashboard', 'inventario', 'ventas'];
        sort($esperadas);
        $this->assertEquals($esperadas, $clavesPermitidas);

        // Stock en inventario
        $inv = $this->actingAs($this->cajero)->getJson('/inventario');
        $inv->assertOk();
        $this->assertNotEmpty($inv->json('productos'));

        // Punto de venta: productos con stock disponible y sin subensambles
        $ventas = $this->actingAs($this->cajero)->getJson('/ventas');
        $ventas->assertOk()
            ->assertJsonStructure(['turnoActivo', 'ventas', 'productos', 'clientes', 'historialTurnos']);

        $productosPos = collect($ventas->json('productos'));
        $this->assertNotEmpty($productosPos);

        foreach ($productosPos as $p) {
            $this->assertArrayHasKey('stock_actual', $p);
            $this->assertArrayHasKey('stock_disponible', $p);
            $this->assertNotEquals('SUB-GEL-BASE', $p['sku'], 'El subensamble no debe aparecer en el POS.');
        }

        // Módulos prohibidos
        $this->actingAs($this->cajero)->getJson('/catalogos')->assertStatus(403);
        $this->actingAs($this->cajero)->getJson('/compras')->assertStatus(403);
        $this->actingAs($this->cajero)->getJson('/produccion')->assertStatus(403);
        $this->actingAs($this->cajero)->getJson('/calidad')->assertStatus(403);
        $this->actingAs($this->cajero)->getJson('/finanzas')->assertStatus(403);
        $this->actingAs($this->cajero)->getJson('/seguridad')->assertStatus(403);
    }

    public function test_contador_acceso_finanzas_compras_valoracion_stock(): void
    {
        $me = $this->actingAs($this->contador)->getJson('/me');
        $me->assertOk()
            ->assertJsonPath('user.name', 'Carlos Navarrete')
            ->assertJsonPath('user.rol', 'Contador');

        $clavesPermitidas = collect($me->json('modulos'))->pluck('clave')->all();
        sort($clavesPermitidas);
        $esperadas = ['catalogos', 'compras', 'dashboard', 'finanzas', 'inventario'];
        sort($esperadas);
        $this->assertEquals($esperadas, $clavesPermitidas);

        // Stock y valoración en inventario
        $inv = $this->actingAs($this->contador)->getJson('/inventario');
        $inv->assertOk();
        $this->assertNotEmpty($inv->json('insumos'));
        $this->assertNotEmpty($inv->json('kardex'));

        // Compras
        $compras = $this->actingAs($this->contador)->getJson('/compras');
        $compras->assertOk()
            ->assertJsonStructure(['ordenes', 'proveedores', 'insumos', 'iva_porcentaje']);
        $this->assertNotEmpty($compras->json('insumos'));

        // Finanzas y contabilidad
        $finanzas = $this->actingAs($this->contador)->getJson('/finanzas');
        $finanzas->assertOk()
            ->assertJsonStructure(['movimientos', 'resumen', 'cuentas_por_cobrar', 'contabilidad']);

        // Módulos prohibidos
        $this->actingAs($this->contador)->getJson('/produccion')->assertStatus(403);
        $this->actingAs($this->contador)->getJson('/calidad')->assertStatus(403);
        $this->actingAs($this->contador)->getJson('/ventas')->assertStatus(403);
        $this->actingAs($this->contador)->getJson('/seguridad')->assertStatus(403);
    }
}
