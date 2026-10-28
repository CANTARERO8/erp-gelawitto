<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produccion\EjecutarLoteRequest;
use App\Http\Requests\Produccion\StoreLoteRequest;
use App\Http\Requests\Produccion\StoreRecetaRequest;
use App\Http\Resources\Catalogos\InsumoResource;
use App\Http\Resources\Catalogos\ProductoResource;
use App\Http\Resources\Produccion\LoteResource;
use App\Http\Resources\Produccion\RecetaResource;
use App\Models\Produccion\LoteProduccion;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Produccion\ProduccionRepository;
use App\Services\Produccion\ProduccionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProduccionController extends Controller
{
    public function __construct(
        protected ProduccionRepository $produccionRepository,
        protected ProduccionService $produccionService,
        protected CatalogosRepository $catalogosRepository
    ) {}

    public function index(): Response
    {
        return Inertia::render('Erp/Produccion', [
            'recetas' => RecetaResource::collection($this->produccionRepository->getRecetasConIngredientes())->resolve(),
            'lotes' => LoteResource::collection($this->produccionRepository->getLotes())->resolve(),
            'productos' => ProductoResource::collection($this->catalogosRepository->getProductosActivos())->resolve(),
            'insumos' => InsumoResource::collection($this->catalogosRepository->getInsumosActivos())->resolve(),
        ]);
    }

    public function storeReceta(StoreRecetaRequest $request): RedirectResponse
    {
        $this->produccionService->crearReceta(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Fórmula técnica registrada correctamente.');
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
