<?php

namespace App\Repositories\Compras;

use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraLinea;
use Illuminate\Database\Eloquent\Collection;

class ComprasRepository
{
    public function getOrdenes(): Collection
    {
        return OrdenCompra::with(['proveedor', 'usuario', 'lineas.insumo'])
            ->orderByDesc('id')
            ->get();
    }

    public function getNextFolio(): string
    {
        $consecutivo = OrdenCompra::count() + 1;
        return sprintf('OC-2026-%04d', $consecutivo);
    }

    public function createOrden(array $ordenData, array $lineaData): OrdenCompra
    {
        $orden = OrdenCompra::create($ordenData);
        OrdenCompraLinea::create([
            'orden_compra_id' => $orden->id,
            ...$lineaData,
        ]);
        return $orden->load(['proveedor', 'usuario', 'lineas.insumo']);
    }
}
