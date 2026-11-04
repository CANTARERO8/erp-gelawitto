<?php

namespace App\Models\Produccion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentroTrabajo extends Model
{
    protected $table = 'centros_trabajo';

    protected $fillable = [
        'codigo',
        'nombre',
        'estacion',
        'capacidad_unidades_hora',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'capacidad_unidades_hora' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function rutas(): HasMany
    {
        return $this->hasMany(RutaOperacion::class, 'centro_trabajo_id');
    }
}
