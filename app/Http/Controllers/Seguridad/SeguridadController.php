<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seguridad\StoreUsuarioRequest;
use App\Http\Resources\Seguridad\BitacoraResource;
use App\Http\Resources\Seguridad\UserResource;
use App\Repositories\Seguridad\SeguridadRepository;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeguridadController extends Controller
{
    public function __construct(
        protected SeguridadRepository $seguridadRepository,
        protected SeguridadService $seguridadService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Erp/Seguridad', [
            'usuarios' => UserResource::collection($this->seguridadRepository->getUsuarios())->resolve(),
            'bitacora' => BitacoraResource::collection($this->seguridadRepository->getBitacora(40))->resolve(),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $this->seguridadService->crearUsuario(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Colaborador registrado con acceso al ERP.');
    }

    public function toggle(Request $request, int $id): RedirectResponse
    {
        $usuario = $this->seguridadService->toggleUsuario(
            $id,
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', "Estado del usuario {$usuario->name} actualizado.");
    }
}
