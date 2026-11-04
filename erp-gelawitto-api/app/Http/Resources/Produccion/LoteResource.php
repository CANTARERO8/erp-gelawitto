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
            'fecha_vencimiento' => is_string($this->fecha_vencimiento) ? $this->fecha_vencimiento : $this->fecha_vencimiento?->toDateString(),
            'inicio_real' => $this->inicio_real?->format('Y-m-d H:i'),
            'fin_real' => $this->fin_real?->format('Y-m-d H:i'),
            'tiempo_paro_min' => (string) ($this->tiempo_paro_min ?? 0),
            'cantidad_programada' => (string) $this->cantidad_programada,
            'cantidad_obtenida' => $this->cantidad_obtenida !== null ? (string) $this->cantidad_obtenida : null,
            'cantidad_disponible' => $this->cantidad_disponible !== null ? (string) $this->cantidad_disponible : null,
            'merma' => (string) $this->merma,
            'costo_total_lote' => $this->costo_total_lote !== null ? (string) $this->costo_total_lote : null,
            'costo_unitario_resultante' => $this->costo_unitario_resultante !== null ? (string) $this->costo_unitario_resultante : null,
            'estado' => $this->estado,
            'estado_calidad' => $this->estado_calidad ?? 'Pendiente',
            'notas' => $this->notas ?? '',
        ];
    }
}
