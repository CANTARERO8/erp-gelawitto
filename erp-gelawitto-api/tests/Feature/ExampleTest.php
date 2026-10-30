<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_users_receive_401_unauthorized(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(401);
    }

    public function test_login_status_endpoint_returns_json(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200)
            ->assertJsonStructure(['failedAttempts', 'lockoutSeconds']);
    }

    public function test_active_user_can_authenticate_and_access_erp_modules(): void
    {
        $user = User::where('email', 'admin@gelawitto.com')->firstOrFail();

        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $loginResponse->assertOk()
            ->assertJsonStructure(['message', 'user', 'modulos', 'parametros']);
        $this->assertAuthenticatedAs($user);

        foreach (['/dashboard', '/catalogos', '/compras', '/inventario', '/produccion', '/ventas', '/finanzas', '/seguridad'] as $uri) {
            $this->actingAs($user)->get($uri)->assertStatus(200);
        }
    }

    public function test_production_batch_deducts_ingredients_and_adds_finished_product(): void
    {
        $user = User::where('email', 'produccion@gelawitto.com')->firstOrFail();
        $receta = DB::table('recetas')->first();
        $productoAntes = DB::table('productos')->where('id', $receta->producto_id)->first();

        $this->actingAs($user)->post('/produccion/lotes', [
            'receta_id' => $receta->id,
            'cantidad_programada' => 10,
            'notas' => 'Lote de prueba automatizada',
        ])->assertCreated();

        $lote = DB::table('lotes_produccion')->orderByDesc('id')->firstOrFail();
        $this->actingAs($user)->post("/produccion/lotes/{$lote->id}/ejecutar", [
            'cantidad_obtenida' => 9.5,
        ])->assertOk();

        $productoDespues = DB::table('productos')->where('id', $receta->producto_id)->first();
        $this->assertEquals(
            round((float) $productoAntes->stock_actual + 9.5, 3),
            round((float) $productoDespues->stock_actual, 3)
        );
    }
}
