<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        protected SeguridadService $seguridadService
    ) {}

    public function showLogin(Request $request): Response
    {
        $throttleKey = $this->throttleKey($request);
        $attempts = RateLimiter::attempts($throttleKey);
        $lockoutSeconds = RateLimiter::availableIn($throttleKey);

        return Inertia::render('Auth/Login', [
            'failedAttempts' => $attempts,
            'lockoutSeconds' => $attempts >= 5 && $lockoutSeconds > 0 ? $lockoutSeconds : 0,
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
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
        $request->session()->regenerate();

        $authenticatedUser = $request->user();
        $authenticatedUser->update(['ultimo_acceso' => now()]);

        $this->seguridadService->registrarAuditoria(
            $authenticatedUser->id,
            'Seguridad',
            'Inicio de Sesión',
            "Acceso concedido al usuario {$authenticatedUser->name} ({$authenticatedUser->rol}).",
            $request->ip()
        );

        return redirect()->intended(route('dashboard'))
            ->with('success', "Bienvenido al sistema, {$authenticatedUser->name}.");
    }

    public function logout(Request $request): RedirectResponse
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
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate('gelawitto-login|'.$request->ip());
    }
}
