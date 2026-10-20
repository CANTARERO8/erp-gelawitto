<?php

namespace App\Services\Seguridad;

use App\Models\Seguridad\BitacoraAuditoria;
use App\Models\User;
use App\Repositories\Seguridad\SeguridadRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SeguridadService
{
    public function __construct(
        protected SeguridadRepository $seguridadRepository
    ) {}

    public function registrarAuditoria(?int $userId, string $modulo, string $accion, string $descripcion, ?string $ip = null): BitacoraAuditoria
    {
        return $this->seguridadRepository->createAuditoria([
            'user_id' => $userId,
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'ip_address' => $ip ?? '127.0.0.1',
            'created_at' => now(),
        ]);
    }

    public function crearUsuario(array $data, ?int $actorId, ?string $ip): User
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $user = $this->seguridadRepository->createUsuario([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'rol' => $data['rol'],
                'activo' => true,
            ]);

            $this->registrarAuditoria(
                $actorId,
                'Seguridad',
                'Alta de Usuario',
                "Se creó la cuenta para {$data['name']} con rol {$data['rol']}.",
                $ip
            );

            return $user;
        });
    }

    public function toggleUsuario(int $id, int $actorId, ?string $ip): User
    {
        $usuario = $this->seguridadRepository->findUsuario($id);

        if (! $usuario) {
            throw ValidationException::withMessages(['id' => 'Usuario no encontrado.']);
        }

        if ($usuario->id === $actorId) {
            throw ValidationException::withMessages(['id' => 'No puedes desactivar tu propia sesión activa.']);
        }

        $usuario->update(['activo' => ! $usuario->activo]);
        $estadoTexto = $usuario->activo ? 'habilitado' : 'suspendido';

        $this->registrarAuditoria(
            $actorId,
            'Seguridad',
            'Cambio de Estado',
            "Acceso {$estadoTexto} para {$usuario->name}.",
            $ip
        );

        return $usuario;
    }
}
