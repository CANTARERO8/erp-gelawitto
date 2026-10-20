<?php

namespace App\Models\Compras;

use App\Models\Catalogos\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenCompra extends Model
{
    protected $table = 'ordenes_compra';

    protected $fillable = [
        'folio',
        'proveedor_id',
        'user_id',
        'fecha_emision',
        'fecha_recepcion',
        'estado',
        'subtotal',
        'impuestos',
        'total',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_recepcion' => 'date',
            'subtotal' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(OrdenCompraLinea::class, 'orden_compra_id');
    }
}
