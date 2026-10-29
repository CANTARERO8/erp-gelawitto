<?php

namespace App\Console\Commands;

use App\Services\Iiot\IiotService;
use Illuminate\Console\Command;

class SimularRefrigerador extends Command
{
    protected $signature = 'iiot:simular
        {--lecturas=10 : Número de lecturas a generar}
        {--intervalo=0 : Segundos entre lecturas}
        {--excursion : Termina con una lectura fuera de rango para probar la retención}';

    protected $description = 'Simula el sensor de temperatura del refrigerador (DS18B20 + ESP32) y envía las lecturas al servicio IIoT.';

    public function handle(IiotService $iiotService): int
    {
        $lecturas = max(1, (int) $this->option('lecturas'));
        $intervalo = max(0, (int) $this->option('intervalo'));
        $excursion = (bool) $this->option('excursion');

        $this->info("Simulador de refrigerador: {$lecturas} lecturas, dispositivo ".config('iiot.refrigerador.codigo').'.');

        for ($i = 1; $i <= $lecturas; $i++) {
            // Operación normal alrededor de 4 °C, con variación de ±0.8 °C.
            $temperatura = round(4.0 + mt_rand(-80, 80) / 100, 2);

            if ($excursion && $i === $lecturas) {
                $temperatura = 8.50;
            }

            $resultado = $iiotService->registrarLectura(['temperatura' => $temperatura], 'Simulador');

            $this->line(sprintf(
                '[%s] %.2f °C · %s · lotes retenidos: %d',
                now()->format('H:i:s'),
                $temperatura,
                $resultado['lectura']->fuera_de_rango ? 'FUERA DE RANGO' : 'en rango',
                $resultado['lotes_retenidos']
            ));

            if ($intervalo > 0 && $i < $lecturas) {
                sleep($intervalo);
            }
        }

        return self::SUCCESS;
    }
}
