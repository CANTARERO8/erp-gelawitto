<?php

namespace App\Http\Resources\Compras;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenCompraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'proveedor_nombre' => $this->proveedor?->nombre ?? ($this->proveedor_nombre ?? 'Proveedor General'),
            'comprador_nombre' => $this->usuario?->name ?? ($this->comprador_nombre ?? 'Administrador'),
            'fecha_emision' => is_string($this->fecha_emision) ? $this->fecha_emision : $this->fecha_emision?->toDateString(),
            'estado' => $this->estado,
            'total' => (string) $this->total,
            'notas' => $this->notas ?? '',
        ];
    }
}
