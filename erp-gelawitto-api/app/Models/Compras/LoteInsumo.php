<?php

namespace App\Models\Compras;

use App\Models\Catalogos\Insumo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteInsumo extends Model
{
    protected $table = 'lotes_insumo';

    protected $fillable = [
        'codigo_lote_insumo',
        'insumo_id',
        'orden_compra_linea_id',
        'lote_proveedor',
        'cantidad_inicial',
        'cantidad_disponible',
        'fecha_ingreso',
        'fecha_vencimiento',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_inicial' => 'decimal:3',
            'cantidad_disponible' => 'decimal:3',
            'fecha_ingreso' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function lineaCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompraLinea::class, 'orden_compra_linea_id');
    }
}
