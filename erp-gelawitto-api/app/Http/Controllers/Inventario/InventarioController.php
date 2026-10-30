<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventario\StoreAjusteRequest;
use App\Http\Resources\Catalogos\InsumoResource;
use App\Http\Resources\Catalogos\ProductoResource;
use App\Http\Resources\Inventario\KardexResource;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Services\Inventario\InventarioService;
use Illuminate\Http\JsonResponse;

class InventarioController extends Controller
{
    public function __construct(
        protected InventarioRepository $inventarioRepository,
        protected CatalogosRepository $catalogosRepository,
        protected InventarioService $inventarioService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'insumos' => InsumoResource::collection($this->catalogosRepository->getInsumos())->resolve(),
            'productos' => ProductoResource::collection($this->catalogosRepository->getProductos())->resolve(),
            'kardex' => KardexResource::collection($this->inventarioRepository->getKardex(40))->resolve(),
        ]);
    }

    public function storeAjuste(StoreAjusteRequest $request): JsonResponse
    {
        $movimiento = $this->inventarioService->registrarAjuste(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Movimiento asentado en el Kardex inmutable.',
            'movimiento' => (new KardexResource($movimiento))->resolve(),
        ], 201);
    }
}
