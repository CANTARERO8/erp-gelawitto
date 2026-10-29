<?php

namespace App\Models\Catalogos;

use App\Models\Produccion\Receta;
use App\Models\Ventas\VentaLinea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'sku',
        'nombre',
        'sabor',
        'presentacion',
        'precio_venta',
        'costo_unitario',
        'stock_actual',
        'stock_minimo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_venta' => 'decimal:2',
            'costo_unitario' => 'decimal:2',
            'stock_actual' => 'decimal:3',
            'stock_minimo' => 'decimal:3',
            'activo' => 'boolean',
        ];
    }

    public function receta(): HasOne
    {
        return $this->hasOne(Receta::class, 'producto_id');
    }

    public function ventaLineas(): HasMany
    {
        return $this->hasMany(VentaLinea::class, 'producto_id');
    }
}
