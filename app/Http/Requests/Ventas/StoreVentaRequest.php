<?php

namespace App\Http\Requests\Ventas;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'metodo_pago' => ['required', 'in:Efectivo,Tarjeta,Transferencia,Crédito'],
            'aplicar_iva' => ['nullable', 'boolean'],
            'producto_id' => ['required_without:lineas', 'nullable', 'exists:productos,id'],
            'cantidad' => ['required_without:lineas', 'nullable', 'numeric', 'min:1'],
            'lineas' => ['required_without:producto_id', 'nullable', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required_with:lineas', 'exists:productos,id'],
            'lineas.*.cantidad' => ['required_with:lineas', 'numeric', 'min:1'],
        ];
    }
}
