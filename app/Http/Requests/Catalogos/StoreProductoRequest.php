<?php

namespace App\Http\Requests\Catalogos;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:30', 'unique:productos,sku'],
            'nombre' => ['required', 'string', 'max:120'],
            'sabor' => ['required', 'string', 'max:60'],
            'presentacion' => ['required', 'string', 'max:60'],
            'precio_venta' => ['required', 'numeric', 'min:0.01'],
            'costo_unitario' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['required', 'numeric', 'min:0'],
        ];
    }
}
