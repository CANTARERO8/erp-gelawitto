<?php

namespace App\Models\Catalogos;

use App\Models\Produccion\RecetaInsumo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insumo extends Model
{
    protected $table = 'insumos';

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'unidad_id',
        'stock_actual',
        'stock_minimo',
        'costo_promedio',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'stock_actual' => 'decimal:3',
            'stock_minimo' => 'decimal:3',
            'costo_promedio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_id');
    }

    public function recetaInsumos(): HasMany
    {
        return $this->hasMany(RecetaInsumo::class, 'insumo_id');
    }
}
