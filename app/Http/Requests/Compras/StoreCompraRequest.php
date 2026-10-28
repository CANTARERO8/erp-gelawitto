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
        if ($this->has('lineas') && is_array($this->input('lineas'))) {
            return [
                'proveedor_id' => ['required', 'exists:proveedores,id'],
                'aplicar_iva' => ['nullable', 'boolean'],
                'notas' => ['nullable', 'string', 'max:255'],
                'lineas' => ['required', 'array', 'min:1'],
                'lineas.*.insumo_id' => ['required', 'exists:insumos,id'],
                'lineas.*.cantidad' => ['required', 'numeric', 'min:0.001'],
                'lineas.*.costo_unitario' => ['required', 'numeric', 'min:0.01'],
            ];
        }

        return [
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'insumo_id' => ['required', 'exists:insumos,id'],
            'cantidad' => ['required', 'numeric', 'min:0.001'],
            'costo_unitario' => ['required', 'numeric', 'min:0.01'],
            'aplicar_iva' => ['nullable', 'boolean'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
