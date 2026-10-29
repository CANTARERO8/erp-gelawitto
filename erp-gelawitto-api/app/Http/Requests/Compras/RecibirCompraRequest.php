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
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
