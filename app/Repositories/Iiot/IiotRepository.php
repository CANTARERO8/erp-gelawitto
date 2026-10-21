<?php

namespace App\Repositories\Iiot;

use App\Models\Iiot\LecturaTemperatura;
use App\Models\Produccion\LoteProduccion;
use Illuminate\Database\Eloquent\Collection;

class IiotRepository
{
    public function getUltimasLecturas(int $limit = 12): Collection
    {
        return LecturaTemperatura::orderByDesc('fecha_lectura')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function createLectura(array $data): LecturaTemperatura
    {
        return LecturaTemperatura::create($data);
    }

    /**
     * Lotes ejecutados que todavía esperan inspección: son los que están en refrigeración.
     */
    public function getLotesEnRefrigeracion(): Collection
    {
        return LoteProduccion::where('estado', 'Finalizado')
            ->where('estado_calidad', 'Pendiente')
            ->lockForUpdate()
            ->get();
    }
}
