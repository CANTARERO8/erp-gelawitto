<?php

namespace App\Models\Produccion;

use App\Models\Compras\LoteInsumo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumoLote extends Model
{
    protected $table = 'consumos_lote';

    protected $fillable = [
        'lote_produccion_id',
        'lote_insumo_id',
        'subproducto_lote_id',
        'cantidad',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
        ];
    }

    public function loteProduccion(): BelongsTo
    {
        return $this->belongsTo(LoteProduccion::class, 'lote_produccion_id');
    }

    public function loteInsumo(): BelongsTo
    {
        return $this->belongsTo(LoteInsumo::class, 'lote_insumo_id');
    }

    public function subproductoLote(): BelongsTo
    {
        return $this->belongsTo(LoteProduccion::class, 'subproducto_lote_id');
    }
}
