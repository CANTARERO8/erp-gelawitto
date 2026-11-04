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
            'rendimiento_unidades' => ['required', 'numeric', 'min:0.1'],
            'tiempo_preparacion_min' => ['required', 'integer', 'min:1'],
            'instrucciones' => ['nullable', 'string', 'max:1500'],
            'ingredientes' => ['required', 'array', 'min:1'],
            'ingredientes.*.insumo_id' => ['required_without:ingredientes.*.subproducto_id', 'nullable', 'integer', 'exists:insumos,id'],
            'ingredientes.*.subproducto_id' => ['required_without:ingredientes.*.insumo_id', 'nullable', 'integer', 'exists:productos,id'],
            'ingredientes.*.nivel' => ['nullable', 'integer', 'min:1', 'max:3'],
            'ingredientes.*.cantidad_requerida' => ['required', 'numeric', 'min:0.001'],
            'ingredientes.*.merma_estimada_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rutas' => ['nullable', 'array'],
            'rutas.*.centro_trabajo_id' => ['required', 'integer', 'exists:centros_trabajo,id'],
            'rutas.*.secuencia' => ['nullable', 'integer', 'min:1'],
            'rutas.*.operacion' => ['required', 'string', 'max:160'],
            'rutas.*.tiempo_preparacion_min' => ['nullable', 'numeric', 'min:0'],
            'rutas.*.tiempo_ciclo_min_por_unidad' => ['nullable', 'numeric', 'min:0.01'],
        ];
    }
}
