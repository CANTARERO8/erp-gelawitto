<?php

namespace App\Http\Requests\Catalogos;

use Illuminate\Foundation\Http\FormRequest;

class StoreInsumoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:30', 'unique:insumos,codigo'],
            'nombre' => ['required', 'string', 'max:120'],
            'categoria' => ['required', 'string', 'max:60'],
            'unidad_id' => ['required', 'exists:unidades_medida,id'],
            'stock_minimo' => ['required', 'numeric', 'min:0'],
            'costo_promedio' => ['required', 'numeric', 'min:0'],
        ];
    }
}
