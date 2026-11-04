<?php

namespace App\Http\Resources\Ventas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lotesAsignados = $this->relationLoaded('lineas')
            ? $this->lineas->map(fn ($l) => $l->loteProduccion?->codigo_lote)->filter()->unique()->implode(', ')
            : '';

        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'cliente_nombre' => $this->cliente?->nombre ?? ($this->cliente_nombre ?? 'Público General'),
            'vendedor_nombre' => $this->vendedor?->name ?? ($this->vendedor_nombre ?? 'Vendedor'),
            'fecha_venta' => is_string($this->fecha_venta) ? $this->fecha_venta : $this->fecha_venta?->toDateTimeString(),
            'metodo_pago' => $this->metodo_pago,
            'total' => (string) $this->total,
            'estado' => $this->estado,
            'lotes_fefo' => $lotesAsignados,
            'lineas' => $this->relationLoaded('lineas') ? $this->lineas->map(fn ($l) => [
                'producto_id' => $l->producto_id,
                'producto_nombre' => $l->producto?->nombre ?? '',
                'lote_produccion_id' => $l->lote_produccion_id,
                'codigo_lote' => $l->loteProduccion?->codigo_lote,
                'cantidad' => (string) $l->cantidad,
                'precio_unitario' => (string) $l->precio_unitario,
                'subtotal' => (string) $l->subtotal,
            ])->values()->all() : [],
        ];
    }
}
