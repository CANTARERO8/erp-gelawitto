<?php

namespace App\Models;

use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Rol;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'rol_id',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->rol && ! $user->rol_id && Schema::hasTable('roles')) {
                $rol = Rol::where('nombre', $user->rol)->first();
                if ($rol) {
                    $user->rol_id = $rol->id;
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'activo' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function rolEntidad(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function modulosPermitidos(): Collection
    {
        if (! Schema::hasTable('modulos') || ! Schema::hasTable('roles')) {
            return collect();
        }

        if ($this->rol === 'Administrador') {
            return Modulo::orderBy('orden')->get(['id', 'clave', 'nombre', 'ruta', 'seccion', 'orden']);
        }

        $rol = $this->rolEntidad ?? Rol::where('nombre', $this->rol)->first();

        return $rol
            ? $rol->modulos()->get(['modulos.id', 'modulos.clave', 'modulos.nombre', 'modulos.ruta', 'modulos.seccion', 'modulos.orden'])
            : collect();
    }
}
