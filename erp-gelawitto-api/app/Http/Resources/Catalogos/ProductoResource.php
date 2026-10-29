<?php

namespace App\Http\Resources\Catalogos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'nombre' => $this->nombre,
            'sabor' => $this->sabor,
            'presentacion' => $this->presentacion,
            'precio_venta' => (string) $this->precio_venta,
            'costo_unitario' => (string) $this->costo_unitario,
            'stock_actual' => (string) $this->stock_actual,
            'stock_minimo' => (string) $this->stock_minimo,
            'activo' => (bool) $this->activo,
        ];
    }
}
