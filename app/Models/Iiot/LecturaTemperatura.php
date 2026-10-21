<?php

namespace App\Models\Iiot;

use Illuminate\Database\Eloquent\Model;

class LecturaTemperatura extends Model
{
    protected $table = 'lecturas_temperatura';

    protected $fillable = [
        'dispositivo',
        'temperatura',
        'fuera_de_rango',
        'origen',
        'fecha_lectura',
    ];

    protected function casts(): array
    {
        return [
            'temperatura' => 'decimal:2',
            'fuera_de_rango' => 'boolean',
            'fecha_lectura' => 'datetime',
        ];
    }
}
