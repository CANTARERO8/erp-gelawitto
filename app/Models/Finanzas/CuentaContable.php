<?php

namespace App\Models\Finanzas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaContable extends Model
{
    protected $table = 'cuentas_contables';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'naturaleza',
    ];

    public function partidas(): HasMany
    {
        return $this->hasMany(PartidaAsiento::class, 'cuenta_contable_id');
    }
}
