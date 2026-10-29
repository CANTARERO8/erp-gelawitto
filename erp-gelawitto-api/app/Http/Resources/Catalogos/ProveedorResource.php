<?php

namespace App\Http\Resources\Catalogos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProveedorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'ruc_nit' => $this->ruc_nit,
            'contacto' => $this->contacto,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'activo' => (bool) $this->activo,
        ];
    }
}
