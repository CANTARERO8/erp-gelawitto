<?php

namespace App\Http\Requests\Produccion;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'receta_id' => ['required', 'exists:recetas,id'],
            'cantidad_programada' => ['required', 'numeric', 'min:1'],
            'cantidad_obtenida' => ['required', 'numeric', 'min:0.5'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
