<?php

namespace App\Repositories\Produccion;

use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use Illuminate\Database\Eloquent\Collection;

class ProduccionRepository
{
    public function getRecetasConIngredientes(): Collection
    {
        return Receta::with(['producto', 'ingredientes.insumo.unidad'])->orderBy('id')->get();
    }

    public function findReceta(int $id): ?Receta
    {
        return Receta::with(['producto', 'ingredientes.insumo.unidad'])->find($id);
    }

    public function getLotes(): Collection
    {
        return LoteProduccion::with(['producto', 'operador'])->orderByDesc('id')->get();
    }

    public function getNextCodigoLote(): string
    {
        $consecutivo = LoteProduccion::count() + 1;
        return sprintf('LOT-2026-%03d', $consecutivo);
    }

    public function createLote(array $data): LoteProduccion
    {
        return LoteProduccion::create($data);
    }

    public function createReceta(array $recetaData, array $ingredientes): Receta
    {
        $receta = Receta::create($recetaData);
        foreach ($ingredientes as $ing) {
            $receta->ingredientes()->create([
                'insumo_id' => $ing['insumo_id'],
                'cantidad_requerida' => $ing['cantidad_requerida'],
                'merma_estimada_porcentaje' => $ing['merma_estimada_porcentaje'] ?? 0,
            ]);
        }

        return $receta->load(['producto', 'ingredientes.insumo.unidad']);
    }

    public function findLoteForUpdate(int $id): LoteProduccion
    {
        return LoteProduccion::whereKey($id)->lockForUpdate()->firstOrFail();
    }
}
