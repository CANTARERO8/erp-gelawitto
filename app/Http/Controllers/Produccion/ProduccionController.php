<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produccion\EjecutarLoteRequest;
use App\Http\Requests\Produccion\StoreLoteRequest;
use App\Http\Resources\Produccion\LoteResource;
use App\Http\Resources\Produccion\RecetaResource;
use App\Models\Produccion\LoteProduccion;
use App\Repositories\Produccion\ProduccionRepository;
use App\Services\Produccion\ProduccionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProduccionController extends Controller
{
    public function __construct(
        protected ProduccionRepository $produccionRepository,
        protected ProduccionService $produccionService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Erp/Produccion', [
            'recetas' => RecetaResource::collection($this->produccionRepository->getRecetasConIngredientes())->resolve(),
            'lotes' => LoteResource::collection($this->produccionRepository->getLotes())->resolve(),
        ]);
    }

    public function store(StoreLoteRequest $request): RedirectResponse
    {
        $this->produccionService->programarLote(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Orden de producción programada. Los insumos se descontarán al ejecutarla.');
    }

    public function ejecutar(EjecutarLoteRequest $request, LoteProduccion $lote): RedirectResponse
    {
        $this->produccionService->ejecutarLote(
            $lote,
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Lote ejecutado: insumos descontados y producto terminado ingresado al Kardex.');
    }
}
