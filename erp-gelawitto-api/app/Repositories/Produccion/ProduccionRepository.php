<?php

namespace App\Repositories\Produccion;

use App\Models\Produccion\CentroTrabajo;
use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Repositories\Configuracion\ParametrosRepository;
use Illuminate\Database\Eloquent\Collection;

class ProduccionRepository
{
    public function getRecetasConIngredientes(): Collection
    {
        return Receta::with([
            'producto',
            'ingredientes.insumo.unidad',
            'ingredientes.subproducto.receta.ingredientes.insumo.unidad',
            'rutas.centroTrabajo',
        ])->orderBy('id')->get();
    }

    public function findReceta(int $id): ?Receta
    {
        return Receta::with([
            'producto',
            'ingredientes.insumo.unidad',
            'ingredientes.subproducto.receta.ingredientes.insumo.unidad',
            'rutas.centroTrabajo',
        ])->find($id);
    }

    public function getCentrosTrabajo(): Collection
    {
        return CentroTrabajo::with('rutas.receta')->orderBy('codigo')->get();
    }

    public function getLotes(): Collection
    {
        return LoteProduccion::with(['producto', 'operador', 'receta.rutas.centroTrabajo', 'controles'])->orderByDesc('id')->get();
    }

    public function getNextCodigoLote(): string
    {
        return ParametrosRepository::siguienteFolio('folio_lotes_seq', 'LOT-2026-%03d', 'lotes_produccion', 'codigo_lote');
    }

    public function createLote(array $data): LoteProduccion
    {
        return LoteProduccion::create($data);
    }

    public function createReceta(array $recetaData, array $ingredientes, array $rutas = []): Receta
    {
        $receta = Receta::create($recetaData);
        foreach ($ingredientes as $ing) {
            $receta->ingredientes()->create([
                'insumo_id' => $ing['insumo_id'] ?? null,
                'subproducto_id' => $ing['subproducto_id'] ?? null,
                'nivel' => $ing['nivel'] ?? 1,
                'cantidad_requerida' => $ing['cantidad_requerida'],
                'merma_estimada_porcentaje' => $ing['merma_estimada_porcentaje'] ?? 0,
            ]);
        }
        foreach (array_values($rutas) as $idx => $ruta) {
            $receta->rutas()->create([
                'centro_trabajo_id' => $ruta['centro_trabajo_id'],
                'secuencia' => $ruta['secuencia'] ?? ($idx + 1),
                'operacion' => $ruta['operacion'],
                'tiempo_preparacion_min' => $ruta['tiempo_preparacion_min'] ?? 5.0,
                'tiempo_ciclo_min_por_unidad' => $ruta['tiempo_ciclo_min_por_unidad'] ?? 1.5,
            ]);
        }

        return $receta->load([
            'producto',
            'ingredientes.insumo.unidad',
            'ingredientes.subproducto.receta.ingredientes.insumo.unidad',
            'rutas.centroTrabajo',
        ]);
    }

    public function findLoteForUpdate(int $id): LoteProduccion
    {
        return LoteProduccion::whereKey($id)->lockForUpdate()->firstOrFail();
    }
}
