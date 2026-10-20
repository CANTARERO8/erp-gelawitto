<?php

namespace App\Models\Inventario;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'fecha',
        'tipo_item',
        'item_id',
        'nombre_item',
        'tipo_movimiento',
        'referencia',
        'cantidad',
        'stock_resultante',
        'costo_unitario',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'cantidad' => 'decimal:3',
            'stock_resultante' => 'decimal:3',
            'costo_unitario' => 'decimal:2',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
