<?php

namespace App\Http\Requests\Compras;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'insumo_id' => ['required', 'exists:insumos,id'],
            'cantidad' => ['required', 'numeric', 'min:0.001'],
            'costo_unitario' => ['required', 'numeric', 'min:0.01'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
