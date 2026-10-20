<?php

namespace App\Http\Resources\Produccion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_lote' => $this->codigo_lote,
            'producto_nombre' => $this->producto?->nombre ?? ($this->producto_nombre ?? ''),
            'producto_sku' => $this->producto?->sku ?? ($this->producto_sku ?? ''),
            'operador_nombre' => $this->operador?->name ?? ($this->operador_nombre ?? 'Operador'),
            'fecha_produccion' => is_string($this->fecha_produccion) ? $this->fecha_produccion : $this->fecha_produccion?->toDateString(),
            'cantidad_programada' => (string) $this->cantidad_programada,
            'cantidad_obtenida' => (string) $this->cantidad_obtenida,
            'merma' => (string) $this->merma,
            'costo_total_lote' => (string) $this->costo_total_lote,
            'costo_unitario_resultante' => (string) $this->costo_unitario_resultante,
            'estado' => $this->estado,
            'notas' => $this->notas ?? '',
        ];
    }
}
