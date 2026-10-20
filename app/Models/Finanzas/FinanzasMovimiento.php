<?php

namespace App\Models\Finanzas;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanzasMovimiento extends Model
{
    protected $table = 'finanzas_movimientos';

    protected $fillable = [
        'folio',
        'fecha',
        'tipo',
        'categoria',
        'concepto',
        'referencia',
        'monto',
        'estado',
        'fecha_vencimiento',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_vencimiento' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
