<?php

namespace App\Http\Resources\Catalogos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsumoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'categoria' => $this->categoria,
            'unidad' => $this->unidad?->abreviatura ?? (is_string($this->unidad) ? $this->unidad : ''),
            'unidad_id' => $this->unidad_id,
            'stock_actual' => (string) $this->stock_actual,
            'stock_minimo' => (string) $this->stock_minimo,
            'costo_promedio' => (string) $this->costo_promedio,
            'activo' => (bool) $this->activo,
        ];
    }
}
