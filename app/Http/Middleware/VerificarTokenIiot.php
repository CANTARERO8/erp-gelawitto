<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarTokenIiot
{
    /**
     * Acepta solo dispositivos que envían el token configurado en X-IIoT-Token.
     * Si el token no está definido, ningún dispositivo puede enviar lecturas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tokenConfigurado = (string) config('iiot.token');
        $tokenRecibido = (string) $request->header('X-IIoT-Token', '');

        if ($tokenConfigurado === '' || ! hash_equals($tokenConfigurado, $tokenRecibido)) {
            return response()->json(['message' => 'Token de dispositivo inválido.'], 401);
        }

        return $next($request);
    }
}
