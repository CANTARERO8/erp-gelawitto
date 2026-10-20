<?php

namespace App\Http\Resources\Ventas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CajaTurnoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_turno' => $this->codigo_turno,
            'cajero_nombre' => $this->cajero?->name ?? ($this->cajero_nombre ?? 'Cajero'),
            'fecha_apertura' => is_string($this->fecha_apertura) ? $this->fecha_apertura : $this->fecha_apertura?->toDateTimeString(),
            'fecha_cierre' => is_string($this->fecha_cierre) ? $this->fecha_cierre : $this->fecha_cierre?->toDateTimeString(),
            'monto_inicial' => (string) $this->monto_inicial,
            'ventas_efectivo' => (string) $this->ventas_efectivo,
            'ventas_tarjeta' => (string) $this->ventas_tarjeta,
            'ventas_transferencia' => (string) $this->ventas_transferencia,
            'monto_esperado' => (string) $this->monto_esperado,
            'monto_declarado' => $this->monto_declarado !== null ? (string) $this->monto_declarado : null,
            'diferencia' => $this->diferencia !== null ? (string) $this->diferencia : null,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones ?? '',
        ];
    }
}
