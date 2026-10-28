<?php

namespace App\Http\Requests\Catalogos;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'ruc_nit' => ['required', 'string', 'max:40', 'unique:proveedores,ruc_nit'],
            'contacto' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:40'],
            'correo' => ['nullable', 'email', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:200'],
        ];
    }
}
