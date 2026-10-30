<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Repositories\Configuracion\ParametrosRepository;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected SeguridadService $seguridadService,
        protected ParametrosRepository $parametrosRepository
    ) {}

    public function showLogin(Request $request): JsonResponse
    {
        $throttleKey = $this->throttleKey($request);
        $attempts = RateLimiter::attempts($throttleKey);
        $lockoutSeconds = RateLimiter::availableIn($throttleKey);

        return response()->json([
            'failedAttempts' => $attempts,
            'lockoutSeconds' => $attempts >= 5 && $lockoutSeconds > 0 ? $lockoutSeconds : 0,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $user->rol,
                'rol_id' => $user->rol_id,
            ],
            'modulos' => $user->modulosPermitidos()->values(),
            'parametros' => $this->parametrosRepository->obtenerTodos(),
        ]);
    }

    public function parametros(): JsonResponse
    {
        return response()->json([
            'parametros' => $this->parametrosRepository->obtenerTodos(),
            'iva_porcentaje' => $this->parametrosRepository->valorDecimal('iva_porcentaje', 15.0),
            'tasa_iva' => $this->parametrosRepository->tasaIva(),
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'lockout' => "Límite de intentos superados, por favor espere {$seconds} segundos.",
                'timer' => (string) $seconds,
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->activo) {
            throw ValidationException::withMessages([
                'email' => 'Este usuario no tiene permitido acceder al sistema.',
            ]);
        }

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);
            $attempts = RateLimiter::attempts($throttleKey);
            $seconds = RateLimiter::availableIn($throttleKey);

            if ($attempts >= 5) {
                throw ValidationException::withMessages([
                    'lockout' => "Límite de intentos superados, por favor espere {$seconds} segundos.",
                    'timer' => (string) $seconds,
                ]);
            }

            throw ValidationException::withMessages([
                'email' => 'Las credenciales ingresadas no coinciden con nuestros registros.',
                'attempts' => (string) $attempts,
            ]);
        }

        RateLimiter::clear($throttleKey);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $authenticatedUser = $request->user();
        $authenticatedUser->update(['ultimo_acceso' => now()]);

        $this->seguridadService->registrarAuditoria(
            $authenticatedUser->id,
            'Seguridad',
            'Inicio de Sesión',
            "Acceso concedido al usuario {$authenticatedUser->name} ({$authenticatedUser->rol}).",
            $request->ip()
        );

        return response()->json([
            'message' => "Bienvenido al sistema, {$authenticatedUser->name}.",
            'user' => [
                'id' => $authenticatedUser->id,
                'name' => $authenticatedUser->name,
                'email' => $authenticatedUser->email,
                'rol' => $authenticatedUser->rol,
                'rol_id' => $authenticatedUser->rol_id,
            ],
            'modulos' => $authenticatedUser->modulosPermitidos()->values(),
            'parametros' => $this->parametrosRepository->obtenerTodos(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $this->seguridadService->registrarAuditoria(
                $request->user()->id,
                'Seguridad',
                'Cierre de Sesión',
                "El usuario {$request->user()->name} cerró su sesión.",
                $request->ip()
            );
        }

        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate('gelawitto-login|'.$request->ip());
    }
}
