<?php

namespace Tests\Feature;

use App\Models\Calidad\ControlCalidad;
use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Iiot\LecturaTemperatura;
use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Models\Produccion\RecetaInsumo;
use App\Models\User;
use App\Services\Produccion\ProduccionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class IiotTest extends TestCase
{
    use DatabaseTransactions;

    private const TOKEN = 'token-de-prueba-iiot';

    private User $admin;

    private Receta $receta;

    protected function setUp(): void
    {
        parent::setUp();

        config(['iiot.token' => self::TOKEN]);

        $this->admin = User::where('email', 'admin@gelawitto.com')->firstOrFail();
        $unidad = UnidadMedida::where('abreviatura', 'ud')->firstOrFail();

        $insumo = Insumo::create([
            'codigo' => 'TST-IOT-001',
            'nombre' => 'Insumo de prueba IIoT',
            'categoria' => 'Prueba',
            'unidad_id' => $unidad->id,
            'stock_actual' => 10,
            'stock_minimo' => 0,
            'costo_promedio' => 4,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sku' => 'TST-IOT-001',
            'nombre' => 'Producto de prueba IIoT',
            'sabor' => 'Prueba',
            'presentacion' => 'Unidad',
            'precio_venta' => 100,
            'costo_unitario' => 0,
            'stock_actual' => 0,
            'stock_minimo' => 0,
            'activo' => true,
        ]);

        $this->receta = Receta::create([
            'producto_id' => $producto->id,
            'nombre' => 'Receta de prueba IIoT',
            'rendimiento_unidades' => 1,
            'tiempo_preparacion_min' => 1,
            'activa' => true,
        ]);

        RecetaInsumo::create([
            'receta_id' => $this->receta->id,
            'insumo_id' => $insumo->id,
            'cantidad_requerida' => 1,
            'merma_estimada_porcentaje' => 0,
        ]);
    }

    /**
     * Lote ejecutado y todavía sin inspeccionar: está en refrigeración.
     */
    private function loteEnRefrigeracion(): LoteProduccion
    {
        $servicio = app(ProduccionService::class);
        $lote = $servicio->programarLote(['receta_id' => $this->receta->id, 'cantidad_programada' => 5], $this->admin->id, null);

        return $servicio->ejecutarLote($lote, ['cantidad_obtenida' => 5], $this->admin->id, null);
    }

    private function enviarLectura(float $temperatura, ?string $token = self::TOKEN)
    {
        $headers = $token === null ? [] : ['X-IIoT-Token' => $token];

        return $this->withHeaders($headers)->postJson('/api/iiot/lecturas', ['temperatura' => $temperatura]);
    }

    public function test_lectura_en_rango_se_registra_sin_retener_lotes(): void
    {
        $lote = $this->loteEnRefrigeracion();

        $this->enviarLectura(4.2)
            ->assertCreated()
            ->assertJson(['fuera_de_rango' => false, 'lotes_retenidos' => 0]);

        $this->assertEquals('Pendiente', $lote->fresh()->estado_calidad);
        $this->assertDatabaseHas('lecturas_temperatura', [
            'temperatura' => 4.20,
            'fuera_de_rango' => false,
            'origen' => 'Sensor',
        ]);
    }

    public function test_lectura_fuera_de_rango_retiene_los_lotes_que_esperan_inspeccion(): void
    {
        $lote = $this->loteEnRefrigeracion();

        $this->enviarLectura(8.5)
            ->assertCreated()
            ->assertJson(['fuera_de_rango' => true]);

        $this->assertEquals('Retenido', $lote->fresh()->estado_calidad);
        $this->assertDatabaseHas('lecturas_temperatura', ['temperatura' => 8.50, 'fuera_de_rango' => true]);
    }

    public function test_lote_retenido_puede_inspeccionarse_y_liberarse(): void
    {
        $lote = $this->loteEnRefrigeracion();
        $this->enviarLectura(8.5);

        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 0,
            'disposicion' => 'Liberación',
            'notas' => 'Temperatura revisada: producto dentro de criterio',
        ])->assertSessionHasNoErrors();

        $this->assertEquals('Liberado', $lote->fresh()->estado_calidad);
        $this->assertEquals(1, ControlCalidad::where('lote_id', $lote->id)->count());
    }

    public function test_un_lote_ya_inspeccionado_no_se_retiene_por_temperatura(): void
    {
        $lote = $this->loteEnRefrigeracion();
        $this->actingAs($this->admin)->post("/calidad/lotes/{$lote->id}/inspeccion", [
            'unidades_inspeccionadas' => 5,
            'unidades_no_conformes' => 0,
            'disposicion' => 'Liberación',
        ])->assertSessionHasNoErrors();

        $this->enviarLectura(8.5)->assertCreated();

        // El lote ya tiene disposición, así que la retención no lo altera.
        $this->assertEquals('Liberado', $lote->fresh()->estado_calidad);
    }

    public function test_rechaza_lecturas_sin_token_o_con_token_incorrecto(): void
    {
        $this->enviarLectura(4.0, null)->assertUnauthorized();
        $this->enviarLectura(4.0, 'token-equivocado')->assertUnauthorized();

        $this->assertDatabaseMissing('lecturas_temperatura', ['temperatura' => 4.00, 'origen' => 'Sensor']);
    }

    public function test_cierra_la_ingesta_si_el_token_no_esta_configurado(): void
    {
        config(['iiot.token' => null]);

        $this->enviarLectura(4.0, self::TOKEN)->assertUnauthorized();
    }

    public function test_valida_que_la_temperatura_sea_numerica(): void
    {
        $this->withHeaders(['X-IIoT-Token' => self::TOKEN])
            ->postJson('/api/iiot/lecturas', ['temperatura' => 'caliente'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('temperatura');
    }

    public function test_el_simulador_envia_lecturas_y_detecta_una_excursion(): void
    {
        $antes = LecturaTemperatura::count();

        Artisan::call('iiot:simular', ['--lecturas' => 3, '--excursion' => true]);

        $this->assertEquals($antes + 3, LecturaTemperatura::count());
        $ultima = LecturaTemperatura::orderByDesc('id')->firstOrFail();
        $this->assertTrue((bool) $ultima->fuera_de_rango);
        $this->assertEquals('Simulador', $ultima->origen);
    }
}
