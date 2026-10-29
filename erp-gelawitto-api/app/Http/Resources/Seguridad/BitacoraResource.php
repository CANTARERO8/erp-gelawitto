<?php

namespace App\Http\Resources\Seguridad;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BitacoraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'modulo' => $this->modulo,
            'accion' => $this->accion,
            'descripcion' => $this->descripcion,
            'ip_address' => $this->ip_address,
            'usuario_nombre' => $this->usuario?->name ?? ($this->usuario_nombre ?? 'Sistema'),
            'usuario_rol' => $this->usuario?->rol ?? ($this->usuario_rol ?? ''),
            'created_at' => is_string($this->created_at) ? $this->created_at : $this->created_at?->toDateTimeString(),
        ];
    }
}
