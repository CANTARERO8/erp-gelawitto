<?php

namespace App\Models\Ventas;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CajaTurno extends Model
{
    protected $table = 'cajas_turnos';

    protected $fillable = [
        'codigo_turno',
        'user_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'ventas_efectivo',
        'ventas_tarjeta',
        'ventas_transferencia',
        'monto_esperado',
        'monto_declarado',
        'diferencia',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'datetime',
            'fecha_cierre' => 'datetime',
            'monto_inicial' => 'decimal:2',
            'ventas_efectivo' => 'decimal:2',
            'ventas_tarjeta' => 'decimal:2',
            'ventas_transferencia' => 'decimal:2',
            'monto_esperado' => 'decimal:2',
            'monto_declarado' => 'decimal:2',
            'diferencia' => 'decimal:2',
        ];
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'caja_turno_id');
    }
}
