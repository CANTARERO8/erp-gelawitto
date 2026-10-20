<?php

namespace App\Http\Resources\Seguridad;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'rol' => $this->rol,
            'activo' => (bool) $this->activo,
            'ultimo_acceso' => is_string($this->ultimo_acceso) ? $this->ultimo_acceso : $this->ultimo_acceso?->toDateTimeString(),
        ];
    }
}
