<?php

namespace App\Http\Resources\Ventas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'cliente_nombre' => $this->cliente?->nombre ?? ($this->cliente_nombre ?? 'Público General'),
            'vendedor_nombre' => $this->vendedor?->name ?? ($this->vendedor_nombre ?? 'Vendedor'),
            'fecha_venta' => is_string($this->fecha_venta) ? $this->fecha_venta : $this->fecha_venta?->toDateTimeString(),
            'metodo_pago' => $this->metodo_pago,
            'total' => (string) $this->total,
            'estado' => $this->estado,
        ];
    }
}
