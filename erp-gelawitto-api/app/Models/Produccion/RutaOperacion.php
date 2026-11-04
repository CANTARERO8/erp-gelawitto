<?php

namespace App\Models\Produccion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RutaOperacion extends Model
{
    protected $table = 'rutas_operacion';

    protected $fillable = [
        'receta_id',
        'centro_trabajo_id',
        'secuencia',
        'operacion',
        'tiempo_preparacion_min',
        'tiempo_ciclo_min_por_unidad',
    ];

    protected function casts(): array
    {
        return [
            'secuencia' => 'integer',
            'tiempo_preparacion_min' => 'decimal:2',
            'tiempo_ciclo_min_por_unidad' => 'decimal:3',
        ];
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function centroTrabajo(): BelongsTo
    {
        return $this->belongsTo(CentroTrabajo::class, 'centro_trabajo_id');
    }
}
