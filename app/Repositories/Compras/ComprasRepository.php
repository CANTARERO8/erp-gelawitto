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

    public function findOrdenForUpdate(int $id): OrdenCompra
    {
        return OrdenCompra::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function createOrden(array $ordenData, array $lineaData): OrdenCompra
    {
        $orden = OrdenCompra::create($ordenData);

        $lineas = isset($lineaData[0]) && is_array($lineaData[0]) ? $lineaData : [$lineaData];

        foreach ($lineas as $linea) {
            OrdenCompraLinea::create([
                'orden_compra_id' => $orden->id,
                ...$linea,
            ]);
        }

        return $orden->load(['proveedor', 'usuario', 'lineas.insumo']);
    }
}
