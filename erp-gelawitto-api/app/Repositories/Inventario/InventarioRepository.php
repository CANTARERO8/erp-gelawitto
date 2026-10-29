<?php

namespace App\Repositories\Inventario;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Inventario\MovimientoInventario;
use Illuminate\Database\Eloquent\Collection;

class InventarioRepository
{
    public function getKardex(int $limit = 40): Collection
    {
        return MovimientoInventario::with('usuario')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function getUltimosMovimientos(int $limit = 6): Collection
    {
        return MovimientoInventario::with('usuario')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function getInsumosCriticos(): Collection
    {
        return Insumo::with('unidad')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->get();
    }

    public function getValorInventarioInsumos(): float
    {
        return (float) Insumo::where('activo', true)
            ->selectRaw('COALESCE(SUM(stock_actual * costo_promedio), 0) as total')
            ->value('total');
    }

    public function getValorInventarioProductos(): float
    {
        return (float) Producto::where('activo', true)
            ->selectRaw('COALESCE(SUM(stock_actual * costo_unitario), 0) as total')
            ->value('total');
    }

    public function registrarMovimiento(array $data): MovimientoInventario
    {
        return MovimientoInventario::create($data);
    }
}
