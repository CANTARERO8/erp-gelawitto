<?php

namespace App\Http\Requests\Produccion;

use Illuminate\Foundation\Http\FormRequest;

class EjecutarLoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cantidad_obtenida' => ['required', 'numeric', 'min:0.1'],
            'inicio_real' => ['nullable', 'date'],
            'fin_real' => ['nullable', 'date'],
            'tiempo_paro_min' => ['nullable', 'numeric', 'min:0'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
