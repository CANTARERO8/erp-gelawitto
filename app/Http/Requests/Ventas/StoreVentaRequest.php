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
            'producto_id' => ['required', 'exists:productos,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'cantidad' => ['required', 'numeric', 'min:1'],
            'metodo_pago' => ['required', 'in:Efectivo,Tarjeta,Transferencia'],
        ];
    }
}
