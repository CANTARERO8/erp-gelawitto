<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Valida que el usuario autenticado posea uno de los roles permitidos.
     * El rol Administrador siempre tiene autorización irrestricta.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->rol === 'Administrador' || in_array($user->rol, $roles, true)) {
            return $next($request);
        }

        abort(403, 'No tienes permisos asignados para acceder a este módulo del ERP.');
    }
}
