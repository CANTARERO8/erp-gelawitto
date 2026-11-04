<?php

namespace App\Http\Requests\Compras;

use Illuminate\Foundation\Http\FormRequest;

class RecibirCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'modalidad_pago' => ['required', 'in:Pagado,Pendiente'],
            'lote_proveedor' => ['nullable', 'string', 'max:50'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
