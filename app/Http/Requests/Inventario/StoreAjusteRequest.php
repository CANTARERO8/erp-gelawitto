<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;

class StoreAjusteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tipo_item' => ['required', 'in:insumo,producto'],
            'item_id' => ['required', 'integer'],
            'tipo_movimiento' => ['required', 'in:Merma,Ajuste Positivo,Ajuste Negativo'],
            'cantidad' => ['required', 'numeric', 'min:0.001'],
            'motivo' => ['required', 'string', 'max:180'],
        ];
    }
}
