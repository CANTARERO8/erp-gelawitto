<?php

namespace App\Repositories\Ventas;

use App\Models\Produccion\LoteProduccion;
use App\Models\Ventas\CajaTurno;
use App\Models\Ventas\Venta;
use App\Models\Ventas\VentaLinea;
use Illuminate\Database\Eloquent\Collection;

class VentasRepository
{
    public function getTurnoActivo(): ?CajaTurno
    {
        return CajaTurno::with('cajero')
            ->where('estado', 'Abierta')
            ->orderByDesc('id')
            ->first();
    }

    public function getTurnoActivoForUpdate(): ?CajaTurno
    {
        return CajaTurno::where('estado', 'Abierta')
            ->lockForUpdate()
            ->first();
    }

    public function getHistorialTurnos(int $limit = 10): Collection
    {
        return CajaTurno::with('cajero')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function getVentasRecientes(int $limit = 25): Collection
    {
        return Venta::with(['cliente', 'vendedor', 'lineas.producto'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function getVentasHoyTotal(): float
    {
        return (float) Venta::whereDate('fecha_venta', now()->toDateString())
            ->where('estado', 'Completada')
            ->sum('total');
    }

    public function getVentasAcumuladasTotal(): float
    {
        return (float) Venta::where('estado', 'Completada')->sum('total');
    }

    public function getStockEnCuarentena(int $productoId): float
    {
        return (float) LoteProduccion::where('producto_id', $productoId)
            ->where('estado', 'Finalizado')
            ->whereIn('estado_calidad', ['Pendiente', 'Retenido'])
            ->sum('cantidad_obtenida');
    }

    public function getNextFolioVenta(): string
    {
        $consecutivo = Venta::count() + 1;
        return sprintf('VTA-2026-%04d', $consecutivo);
    }

    public function getNextCodigoTurno(): string
    {
        $consecutivo = CajaTurno::count() + 1;
        return sprintf('TUR-2026-%03d', $consecutivo);
    }

    public function createVenta(array $ventaData, array $lineasData): Venta
    {
        $venta = Venta::create($ventaData);
        $lineas = isset($lineasData[0]) ? $lineasData : [$lineasData];

        foreach ($lineas as $linea) {
            VentaLinea::create([
                'venta_id' => $venta->id,
                ...$linea,
            ]);
        }

        return $venta->load(['cliente', 'vendedor', 'lineas.producto']);
    }

    public function createTurno(array $data): CajaTurno
    {
        return CajaTurno::create($data);
    }

    public function actualizarTurno(CajaTurno $turno, array $data): bool
    {
        return $turno->update($data);
    }
}
