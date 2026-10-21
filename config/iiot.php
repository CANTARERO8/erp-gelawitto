<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Token de dispositivos IIoT
    |--------------------------------------------------------------------------
    |
    | Los sensores y el simulador envían este token en el encabezado X-IIoT-Token.
    | Si no está definido, la ingesta queda cerrada para cualquier dispositivo.
    */
    'token' => env('IIOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Refrigerador de maduración y enfriamiento de lotes
    |--------------------------------------------------------------------------
    |
    | Rango de temperatura aceptable en °C. Una lectura fuera de este rango
    | retiene los lotes que todavía esperan inspección.
    | Valores provisionales: confirmar con el equipo.
    */
    'refrigerador' => [
        'codigo' => env('IIOT_DISPOSITIVO', 'REF-01'),
        'temperatura_minima' => 2.0,
        'temperatura_maxima' => 6.0,
    ],

];
