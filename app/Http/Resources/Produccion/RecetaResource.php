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
            'sabor' => $this->producto?->sabor ?? ($this->sabor ?? ''),
            'rendimiento_unidades' => (string) $this->rendimiento_unidades,
            'tiempo_preparacion_min' => (int) $this->tiempo_preparacion_min,
            'instrucciones' => $this->instrucciones ?? '',
            'ingredientes' => $this->ingredientes->map(function ($ing) {
                return [
                    'insumo_nombre' => $ing->insumo?->nombre ?? ($ing->insumo_nombre ?? ''),
                    'cantidad_requerida' => (string) $ing->cantidad_requerida,
                    'merma_estimada_porcentaje' => (string) $ing->merma_estimada_porcentaje,
                    'stock_actual' => (string) ($ing->insumo?->stock_actual ?? ($ing->stock_actual ?? 0)),
                    'costo_promedio' => (string) ($ing->insumo?->costo_promedio ?? ($ing->costo_promedio ?? 0)),
                    'unidad' => $ing->insumo?->unidad?->abreviatura ?? ($ing->unidad ?? ''),
                ];
            }),
        ];
    }
}
