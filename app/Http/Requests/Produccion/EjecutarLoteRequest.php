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
            'cantidad_obtenida' => ['required', 'numeric', 'min:0.5'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
