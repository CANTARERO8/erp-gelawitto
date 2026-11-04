<?php

namespace App\Models\Ventas;

use App\Models\Catalogos\Producto;
use App\Models\Produccion\LoteProduccion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaLinea extends Model
{
    protected $table = 'venta_lineas';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'lote_produccion_id',
        'cantidad',
        'precio_unitario',
        'costo_unitario',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'precio_unitario' => 'decimal:2',
            'costo_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function loteProduccion(): BelongsTo
    {
        return $this->belongsTo(LoteProduccion::class, 'lote_produccion_id');
    }
}
