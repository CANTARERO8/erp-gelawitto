<?php

namespace App\Models\Produccion;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecetaInsumo extends Model
{
    protected $table = 'receta_insumos';

    protected $fillable = [
        'receta_id',
        'insumo_id',
        'subproducto_id',
        'nivel',
        'cantidad_requerida',
        'merma_estimada_porcentaje',
    ];

    protected function casts(): array
    {
        return [
            'nivel' => 'integer',
            'cantidad_requerida' => 'decimal:3',
            'merma_estimada_porcentaje' => 'decimal:2',
        ];
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function subproducto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'subproducto_id');
    }
}
