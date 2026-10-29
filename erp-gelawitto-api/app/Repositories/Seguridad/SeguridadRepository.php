<?php

namespace App\Repositories\Seguridad;

use App\Models\Seguridad\BitacoraAuditoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SeguridadRepository
{
    public function getUsuarios(): Collection
    {
        return User::orderBy('id')->get(['id', 'name', 'email', 'rol', 'activo', 'ultimo_acceso', 'created_at']);
    }

    public function getBitacora(int $limit = 40): Collection
    {
        return BitacoraAuditoria::with('usuario')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function createAuditoria(array $data): BitacoraAuditoria
    {
        return BitacoraAuditoria::create($data);
    }

    public function createUsuario(array $data): User
    {
        return User::create($data);
    }

    public function findUsuario(int $id): ?User
    {
        return User::find($id);
    }
}
