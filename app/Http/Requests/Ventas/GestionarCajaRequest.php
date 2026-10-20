<?php

namespace App\Http\Requests\Ventas;

use Illuminate\Foundation\Http\FormRequest;

class GestionarCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        if ($this->input('accion') === 'abrir') {
            return [
                'accion' => ['required', 'in:abrir,cerrar'],
                'monto_inicial' => ['required', 'numeric', 'min:0'],
                'observaciones' => ['nullable', 'string', 'max:200'],
            ];
        }

        return [
            'accion' => ['required', 'in:abrir,cerrar'],
            'monto_declarado' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:200'],
        ];
    }
}
