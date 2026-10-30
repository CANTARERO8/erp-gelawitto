<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Valida que el usuario autenticado posea el rol o permiso de módulo en base de datos.
     * El rol Administrador siempre tiene autorización irrestricta.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }

            return redirect('/login');
        }

        if ($user->rol === 'Administrador') {
            return $next($request);
        }

        if (in_array($user->rol, $roles, true)) {
            $segmentos = explode('/', trim($request->path(), '/'));
            $moduloClave = ($segmentos[0] ?? '') === 'api' ? ($segmentos[1] ?? '') : ($segmentos[0] ?? '');

            $modulosEnBd = $user->modulosPermitidos();
            if ($modulosEnBd->isEmpty() || $modulosEnBd->contains('clave', $moduloClave)) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'No tienes permisos asignados para acceder a este módulo del ERP.',
            ], 403);
        }

        abort(403, 'No tienes permisos asignados para acceder a este módulo del ERP.');
    }
}
