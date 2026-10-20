<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
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

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

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

        DB::table('bitacora_auditoria')->insert([
            'user_id' => $authenticatedUser->id,
            'modulo' => 'Seguridad',
            'accion' => 'Inicio de Sesión',
            'descripcion' => "Acceso concedido al usuario {$authenticatedUser->name} ({$authenticatedUser->rol}).",
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->intended(route('dashboard'))
            ->with('success', "Bienvenido al sistema, {$authenticatedUser->name}.");
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($request->user()) {
            DB::table('bitacora_auditoria')->insert([
                'user_id' => $request->user()->id,
                'modulo' => 'Seguridad',
                'accion' => 'Cierre de Sesión',
                'descripcion' => "El usuario {$request->user()->name} cerró su sesión.",
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
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
