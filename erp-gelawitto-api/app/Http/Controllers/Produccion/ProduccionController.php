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
use Illuminate\Http\JsonResponse;

class ProduccionController extends Controller
{
    public function __construct(
        protected ProduccionRepository $produccionRepository,
        protected ProduccionService $produccionService,
        protected CatalogosRepository $catalogosRepository
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'recetas' => RecetaResource::collection($this->produccionRepository->getRecetasConIngredientes())->resolve(),
            'lotes' => LoteResource::collection($this->produccionRepository->getLotes())->resolve(),
            'productos' => ProductoResource::collection($this->catalogosRepository->getProductosActivos())->resolve(),
            'insumos' => InsumoResource::collection($this->catalogosRepository->getInsumosActivos())->resolve(),
        ]);
    }

    public function storeReceta(StoreRecetaRequest $request): JsonResponse
    {
        $receta = $this->produccionService->crearReceta(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Fórmula técnica registrada correctamente.',
            'receta' => (new RecetaResource($receta))->resolve(),
        ], 201);
    }

    public function store(StoreLoteRequest $request): JsonResponse
    {
        $lote = $this->produccionService->programarLote(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Orden de producción programada. Los insumos se descontarán al ejecutarla.',
            'lote' => (new LoteResource($lote->load(['producto', 'operador'])))->resolve(),
        ], 201);
    }

    public function ejecutar(EjecutarLoteRequest $request, LoteProduccion $lote): JsonResponse
    {
        $loteEjecutado = $this->produccionService->ejecutarLote(
            $lote,
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Lote ejecutado: insumos descontados y producto terminado ingresado al Kardex.',
            'lote' => (new LoteResource($loteEjecutado->load(['producto', 'operador'])))->resolve(),
        ]);
    }
}
