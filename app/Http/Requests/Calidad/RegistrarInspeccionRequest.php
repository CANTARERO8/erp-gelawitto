<?php

namespace App\Http\Requests\Calidad;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarInspeccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'unidades_inspeccionadas' => ['required', 'numeric', 'min:0.5'],
            'unidades_no_conformes' => ['nullable', 'numeric', 'min:0'],
            'defecto' => ['nullable', 'string', 'max:500'],
            'disposicion' => ['required', 'in:Liberación,Retrabajo,Scrap'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
