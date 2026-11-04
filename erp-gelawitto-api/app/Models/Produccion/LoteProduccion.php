<?php

namespace App\Models\Produccion;

use App\Models\Calidad\ControlCalidad;
use App\Models\Catalogos\Producto;
use App\Models\User;
use App\Models\Ventas\VentaLinea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteProduccion extends Model
{
    protected $table = 'lotes_produccion';

    protected static function booted(): void
    {
        static::creating(function (self $lote) {
            if ($lote->estado === 'Finalizado' && $lote->cantidad_obtenida !== null && (float) ($lote->cantidad_disponible ?? 0) == 0.0) {
                $lote->cantidad_disponible = $lote->cantidad_obtenida;
            }
            if ($lote->estado === 'Finalizado' && empty($lote->fecha_vencimiento)) {
                $baseDate = $lote->fecha_produccion ? \Carbon\Carbon::parse($lote->fecha_produccion) : now();
                $lote->fecha_vencimiento = $baseDate->copy()->addDays(15)->toDateString();
            }
        });
    }

    protected $fillable = [
        'codigo_lote',
        'receta_id',
        'producto_id',
        'user_id',
        'fecha_produccion',
        'fecha_vencimiento',
        'inicio_real',
        'fin_real',
        'tiempo_paro_min',
        'cantidad_programada',
        'cantidad_obtenida',
        'cantidad_disponible',
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
            'fecha_vencimiento' => 'date',
            'inicio_real' => 'datetime',
            'fin_real' => 'datetime',
            'tiempo_paro_min' => 'decimal:2',
            'cantidad_programada' => 'decimal:3',
            'cantidad_obtenida' => 'decimal:3',
            'cantidad_disponible' => 'decimal:3',
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

    public function consumos(): HasMany
    {
        return $this->hasMany(ConsumoLote::class, 'lote_produccion_id');
    }

    public function ventaLineas(): HasMany
    {
        return $this->hasMany(VentaLinea::class, 'lote_produccion_id');
    }
}
