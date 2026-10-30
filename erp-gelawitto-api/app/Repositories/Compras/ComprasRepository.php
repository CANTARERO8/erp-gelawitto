<?php

namespace App\Repositories\Compras;

use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraLinea;
use App\Repositories\Configuracion\ParametrosRepository;
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
        return ParametrosRepository::siguienteFolio('folio_compras_seq', 'OC-2026-%04d', 'ordenes_compra', 'folio');
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
