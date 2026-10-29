<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_active_user_can_authenticate_and_access_erp_modules(): void
    {
        $user = User::where('email', 'admin@gelawitto.com')->firstOrFail();

        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect('/dashboard');
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
        ])->assertSessionHasNoErrors();

        $lote = DB::table('lotes_produccion')->orderByDesc('id')->firstOrFail();
        $this->actingAs($user)->post("/produccion/lotes/{$lote->id}/ejecutar", [
            'cantidad_obtenida' => 9.5,
        ])->assertSessionHasNoErrors();

        $productoDespues = DB::table('productos')->where('id', $receta->producto_id)->first();
        $this->assertEquals(
            round((float) $productoAntes->stock_actual + 9.5, 3),
            round((float) $productoDespues->stock_actual, 3)
        );
    }
}
