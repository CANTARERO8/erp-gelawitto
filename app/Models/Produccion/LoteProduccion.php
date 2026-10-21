<?php

namespace App\Models\Produccion;

use App\Models\Calidad\ControlCalidad;
use App\Models\Catalogos\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteProduccion extends Model
{
    protected $table = 'lotes_produccion';

    protected $fillable = [
        'codigo_lote',
        'receta_id',
        'producto_id',
        'user_id',
        'fecha_produccion',
        'cantidad_programada',
        'cantidad_obtenida',
        'merma',
        'costo_total_lote',
        'costo_unitario_resultante',
        'estado',
        'estado_calidad',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_produccion' => 'date',
            'cantidad_programada' => 'decimal:3',
            'cantidad_obtenida' => 'decimal:3',
            'merma' => 'decimal:3',
            'costo_total_lote' => 'decimal:2',
            'costo_unitario_resultante' => 'decimal:2',
        ];
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function controles(): HasMany
    {
        return $this->hasMany(ControlCalidad::class, 'lote_id');
    }
}
