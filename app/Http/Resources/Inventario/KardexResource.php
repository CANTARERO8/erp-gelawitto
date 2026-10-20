<?php

namespace App\Http\Resources\Inventario;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KardexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha' => is_string($this->fecha) ? $this->fecha : $this->fecha?->toDateTimeString(),
            'tipo_item' => $this->tipo_item,
            'nombre_item' => $this->nombre_item,
            'tipo_movimiento' => $this->tipo_movimiento,
            'referencia' => $this->referencia,
            'cantidad' => (string) $this->cantidad,
            'stock_resultante' => (string) $this->stock_resultante,
            'costo_unitario' => (string) $this->costo_unitario,
            'responsable' => $this->usuario?->name ?? ($this->responsable ?? 'Sistema'),
        ];
    }
}
