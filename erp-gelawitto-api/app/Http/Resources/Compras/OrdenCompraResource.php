<?php

namespace App\Http\Resources\Compras;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenCompraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lotesProveedor = $this->relationLoaded('lineas')
            ? $this->lineas->pluck('lote_proveedor')->filter()->unique()->implode(', ')
            : '';

        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'proveedor_nombre' => $this->proveedor?->nombre ?? ($this->proveedor_nombre ?? 'Proveedor General'),
            'comprador_nombre' => $this->usuario?->name ?? ($this->comprador_nombre ?? 'Administrador'),
            'fecha_emision' => is_string($this->fecha_emision) ? $this->fecha_emision : $this->fecha_emision?->toDateString(),
            'estado' => $this->estado,
            'total' => (string) $this->total,
            'lote_proveedor' => $lotesProveedor,
            'notas' => $this->notas ?? '',
            'lineas' => $this->relationLoaded('lineas') ? $this->lineas->map(fn ($l) => [
                'insumo_id' => $l->insumo_id,
                'insumo_nombre' => $l->insumo?->nombre ?? '',
                'cantidad' => (string) $l->cantidad,
                'costo_unitario' => (string) $l->costo_unitario,
                'subtotal' => (string) $l->subtotal,
                'lote_proveedor' => $l->lote_proveedor,
                'fecha_vencimiento' => is_string($l->fecha_vencimiento) ? $l->fecha_vencimiento : $l->fecha_vencimiento?->toDateString(),
            ])->values()->all() : [],
        ];
    }
}
