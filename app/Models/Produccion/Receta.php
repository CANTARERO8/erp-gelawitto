<?php

namespace App\Models\Produccion;

use App\Models\Catalogos\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receta extends Model
{
    protected $table = 'recetas';

    protected $fillable = [
        'producto_id',
        'nombre',
        'rendimiento_unidades',
        'tiempo_preparacion_min',
        'instrucciones',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'rendimiento_unidades' => 'decimal:3',
            'tiempo_preparacion_min' => 'integer',
            'activa' => 'boolean',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function ingredientes(): HasMany
    {
        return $this->hasMany(RecetaInsumo::class, 'receta_id');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(LoteProduccion::class, 'receta_id');
    }
}
