<?php

namespace App\Http\Resources\Catalogos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'identificacion' => $this->identificacion,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'tipo' => $this->tipo,
            'activo' => (bool) $this->activo,
        ];
    }
}
