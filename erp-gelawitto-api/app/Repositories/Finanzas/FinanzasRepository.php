<?php

namespace App\Repositories\Finanzas;

use App\Models\Finanzas\FinanzasMovimiento;
use App\Repositories\Configuracion\ParametrosRepository;
use Illuminate\Database\Eloquent\Collection;

class FinanzasRepository
{
    public function getMovimientos(): Collection
    {
        return FinanzasMovimiento::with('usuario')->orderByDesc('id')->get();
    }

    public function getCuentasPorPagarTotal(): float
    {
        return (float) FinanzasMovimiento::whereIn('tipo', ['Cuenta por Pagar', 'Egreso'])
            ->where('estado', 'Pendiente')
            ->sum('monto');
    }

    public function getCuentasPorCobrarTotal(): float
    {
        return (float) FinanzasMovimiento::where('tipo', 'Cuenta por Cobrar')
            ->where('estado', 'Pendiente')
            ->sum('monto');
    }

    public function getIngresosCobradosTotal(): float
    {
        return (float) FinanzasMovimiento::where('tipo', 'Ingreso')->where('estado', 'Pagado')->sum('monto');
    }

    public function getEgresosPagadosTotal(): float
    {
        return (float) FinanzasMovimiento::where('tipo', 'Egreso')->where('estado', 'Pagado')->sum('monto');
    }

    public function getNextFolio(): string
    {
        return ParametrosRepository::siguienteFolio('folio_finanzas_seq', 'FIN-2026-%03d', 'finanzas_movimientos', 'folio');
    }

    public function createMovimiento(array $data): FinanzasMovimiento
    {
        return FinanzasMovimiento::create($data);
    }

    public function findMovimientoForUpdate(int $id): ?FinanzasMovimiento
    {
        return FinanzasMovimiento::where('id', $id)->lockForUpdate()->first();
    }
}
