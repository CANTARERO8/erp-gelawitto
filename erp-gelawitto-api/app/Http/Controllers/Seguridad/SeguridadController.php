<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seguridad\StoreUsuarioRequest;
use App\Http\Resources\Seguridad\BitacoraResource;
use App\Http\Resources\Seguridad\UserResource;
use App\Models\Seguridad\Rol;
use App\Repositories\Seguridad\SeguridadRepository;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SeguridadController extends Controller
{
    public function __construct(
        protected SeguridadRepository $seguridadRepository,
        protected SeguridadService $seguridadService
    ) {}

    public function index(): JsonResponse
    {
        $roles = Schema::hasTable('roles')
            ? Rol::orderBy('id')->get(['id', 'nombre', 'descripcion'])->values()
            : collect();

        return response()->json([
            'usuarios' => UserResource::collection($this->seguridadRepository->getUsuarios())->resolve(),
            'bitacora' => BitacoraResource::collection($this->seguridadRepository->getBitacora(40))->resolve(),
            'roles' => $roles,
        ]);
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $usuario = $this->seguridadService->crearUsuario(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Colaborador registrado con acceso al ERP.',
            'usuario' => (new UserResource($usuario))->resolve(),
        ], 201);
    }

    public function toggle(Request $request, int $id): JsonResponse
    {
        $usuario = $this->seguridadService->toggleUsuario(
            $id,
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => "Estado del usuario {$usuario->name} actualizado.",
            'usuario' => (new UserResource($usuario))->resolve(),
        ]);
    }
}
