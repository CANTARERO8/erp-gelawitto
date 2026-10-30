<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Token de dispositivos IIoT
    |--------------------------------------------------------------------------
    |
    | Los sensores y el simulador envían este token en el encabezado X-IIoT-Token.
    | Los parámetros operativos (código y umbrales de temperatura) residen en la
    | tabla `parametros` de PostgreSQL.
    */
    'token' => env('IIOT_TOKEN'),

];
