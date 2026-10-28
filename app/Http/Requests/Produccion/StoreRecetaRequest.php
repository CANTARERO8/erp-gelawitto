<?php

namespace App\Http\Requests\Produccion;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'nombre' => ['required', 'string', 'max:140'],
            'rendimiento_unidades' => ['required', 'numeric', 'min:0.5'],
            'tiempo_preparacion_min' => ['required', 'integer', 'min:1'],
            'instrucciones' => ['nullable', 'string', 'max:1500'],
            'ingredientes' => ['required', 'array', 'min:1'],
            'ingredientes.*.insumo_id' => ['required', 'integer', 'exists:insumos,id'],
            'ingredientes.*.cantidad_requerida' => ['required', 'numeric', 'min:0.001'],
            'ingredientes.*.merma_estimada_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
