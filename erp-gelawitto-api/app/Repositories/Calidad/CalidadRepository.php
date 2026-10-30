<?php

namespace App\Repositories\Calidad;

use App\Models\Calidad\ControlCalidad;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Produccion\LoteProduccion;
use App\Repositories\Configuracion\ParametrosRepository;
use Illuminate\Database\Eloquent\Collection;

class CalidadRepository
{
    public function getLotesConTrazabilidad(): Collection
    {
        return LoteProduccion::with(['producto', 'controles.inspector'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Insumos consumidos por cada lote, leídos del Kardex por referencia de lote.
     */
    public function getConsumosPorLote(array $codigosLote): Collection
    {
        return MovimientoInventario::where('tipo_movimiento', 'Consumo de Producción')
            ->whereIn('referencia', $codigosLote)
            ->orderBy('id')
            ->get()
            ->groupBy('referencia');
    }

    public function getNextCodigoControl(): string
    {
        return ParametrosRepository::siguienteFolio('folio_calidad_seq', 'CC-2026-%03d', 'controles_calidad', 'codigo_control');
    }

    public function createControl(array $data): ControlCalidad
    {
        return ControlCalidad::create($data);
    }
}
