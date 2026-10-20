<?php

namespace App\Http\Resources\Finanzas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanzasMovimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'fecha' => is_string($this->fecha) ? $this->fecha : $this->fecha?->toDateString(),
            'tipo' => $this->tipo,
            'categoria' => $this->categoria,
            'concepto' => $this->concepto,
            'referencia' => $this->referencia ?? '',
            'monto' => (string) $this->monto,
            'estado' => $this->estado,
            'fecha_vencimiento' => is_string($this->fecha_vencimiento) ? $this->fecha_vencimiento : $this->fecha_vencimiento?->toDateString(),
            'responsable' => $this->usuario?->name ?? ($this->responsable ?? 'Administrador'),
        ];
    }
}
