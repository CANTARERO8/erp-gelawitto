<?php

namespace App\Models\Finanzas;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsientoContable extends Model
{
    protected $table = 'asientos_contables';

    protected $fillable = [
        'numero_asiento',
        'fecha',
        'modulo_origen',
        'referencia',
        'concepto',
        'total_debe',
        'total_haber',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'total_debe' => 'decimal:2',
            'total_haber' => 'decimal:2',
        ];
    }

    public function partidas(): HasMany
    {
        return $this->hasMany(PartidaAsiento::class, 'asiento_contable_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
