<?php

namespace App\Models\Calidad;

use App\Models\Produccion\LoteProduccion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlCalidad extends Model
{
    protected $table = 'controles_calidad';

    protected $fillable = [
        'codigo_control',
        'lote_id',
        'user_id',
        'fecha_inspeccion',
        'unidades_inspeccionadas',
        'unidades_no_conformes',
        'defecto',
        'disposicion',
        'costo_perdido',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inspeccion' => 'datetime',
            'unidades_inspeccionadas' => 'decimal:3',
            'unidades_no_conformes' => 'decimal:3',
            'costo_perdido' => 'decimal:2',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteProduccion::class, 'lote_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
