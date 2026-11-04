<?php

namespace App\Http\Resources\Produccion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecetaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'producto_nombre' => $this->producto?->nombre ?? ($this->producto_nombre ?? ''),
            'producto_sku' => $this->producto?->sku ?? ($this->producto_sku ?? ''),
            'producto_tipo' => $this->producto?->tipo ?? 'terminado',
            'sabor' => $this->producto?->sabor ?? ($this->sabor ?? ''),
            'rendimiento_unidades' => (string) $this->rendimiento_unidades,
            'tiempo_preparacion_min' => (int) $this->tiempo_preparacion_min,
            'instrucciones' => $this->instrucciones ?? '',
            'ingredientes' => $this->ingredientes->map(function ($ing) {
                if ($ing->subproducto_id !== null && $ing->subproducto) {
                    $sub = $ing->subproducto;
                    $subReceta = $sub->receta;
                    $nivel2 = $subReceta ? $subReceta->ingredientes->map(fn ($subIng) => [
                        'insumo_nombre' => $subIng->insumo?->nombre ?? '',
                        'cantidad_requerida' => (string) $subIng->cantidad_requerida,
                        'unidad' => $subIng->insumo?->unidad?->abreviatura ?? '',
                        'costo_promedio' => (string) ($subIng->insumo?->costo_promedio ?? 0),
                        'nivel' => 2,
                    ])->values()->all() : [];

                    return [
                        'insumo_nombre' => $sub->nombre,
                        'es_subensamble' => true,
                        'subproducto_id' => $sub->id,
                        'subproducto_sku' => $sub->sku,
                        'nivel' => 1,
                        'cantidad_requerida' => (string) $ing->cantidad_requerida,
                        'merma_estimada_porcentaje' => (string) $ing->merma_estimada_porcentaje,
                        'stock_actual' => (string) $sub->stock_actual,
                        'costo_promedio' => (string) $sub->costo_unitario,
                        'unidad' => 'L',
                        'componentes_nivel_2' => $nivel2,
                    ];
                }

                return [
                    'insumo_nombre' => $ing->insumo?->nombre ?? ($ing->insumo_nombre ?? ''),
                    'es_subensamble' => false,
                    'subproducto_id' => null,
                    'subproducto_sku' => null,
                    'nivel' => (int) ($ing->nivel ?? 1),
                    'cantidad_requerida' => (string) $ing->cantidad_requerida,
                    'merma_estimada_porcentaje' => (string) $ing->merma_estimada_porcentaje,
                    'stock_actual' => (string) ($ing->insumo?->stock_actual ?? ($ing->stock_actual ?? 0)),
                    'costo_promedio' => (string) ($ing->insumo?->costo_promedio ?? ($ing->costo_promedio ?? 0)),
                    'unidad' => $ing->insumo?->unidad?->abreviatura ?? ($ing->unidad ?? ''),
                    'componentes_nivel_2' => [],
                ];
            }),
            'rutas' => $this->relationLoaded('rutas') ? $this->rutas->map(fn ($r) => [
                'secuencia' => (int) $r->secuencia,
                'centro_codigo' => $r->centroTrabajo?->codigo ?? '',
                'centro_nombre' => $r->centroTrabajo?->nombre ?? '',
                'operacion' => $r->operacion,
                'tiempo_preparacion_min' => (string) $r->tiempo_preparacion_min,
                'tiempo_ciclo_min_por_unidad' => (string) $r->tiempo_ciclo_min_por_unidad,
            ])->values()->all() : [],
        ];
    }
}
