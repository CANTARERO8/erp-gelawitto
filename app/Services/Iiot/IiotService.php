<?php

namespace App\Services\Iiot;

use App\Models\Iiot\LecturaTemperatura;
use App\Repositories\Iiot\IiotRepository;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Support\Facades\DB;

class IiotService
{
    public function __construct(
        protected IiotRepository $iiotRepository,
        protected SeguridadService $seguridadService
    ) {}

    /**
     * Registra una lectura de temperatura del refrigerador. Si está fuera del rango
     * configurado, retiene los lotes que esperan inspección hasta que Calidad decida.
     *
     * @return array{lectura: LecturaTemperatura, lotes_retenidos: int}
     */
    public function registrarLectura(array $data, string $origen, ?string $ip = null): array
    {
        return DB::transaction(function () use ($data, $origen, $ip) {
            $dispositivo = $data['dispositivo'] ?? config('iiot.refrigerador.codigo');
            $temperatura = round((float) $data['temperatura'], 2);
            $minimo = (float) config('iiot.refrigerador.temperatura_minima');
            $maximo = (float) config('iiot.refrigerador.temperatura_maxima');
            $fueraDeRango = $temperatura < $minimo || $temperatura > $maximo;

            $lectura = $this->iiotRepository->createLectura([
                'dispositivo' => $dispositivo,
                'temperatura' => $temperatura,
                'fuera_de_rango' => $fueraDeRango,
                'origen' => $origen,
                'fecha_lectura' => $data['fecha_lectura'] ?? now(),
            ]);

            $lotesRetenidos = 0;

            if ($fueraDeRango) {
                $lotes = $this->iiotRepository->getLotesEnRefrigeracion();

                foreach ($lotes as $lote) {
                    $lote->update(['estado_calidad' => 'Retenido']);
                }

                $lotesRetenidos = $lotes->count();

                $this->seguridadService->registrarAuditoria(
                    null,
                    'IIoT',
                    'Alerta de Temperatura',
                    "Refrigerador {$dispositivo} registró {$temperatura} °C fuera del rango {$minimo} a {$maximo} °C. Lotes retenidos: {$lotesRetenidos}.",
                    $ip
                );
            }

            return [
                'lectura' => $lectura,
                'lotes_retenidos' => $lotesRetenidos,
            ];
        });
    }
}
