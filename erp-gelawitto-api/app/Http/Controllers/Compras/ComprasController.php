<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compras\RecibirCompraRequest;
use App\Http\Requests\Compras\StoreCompraRequest;
use App\Http\Resources\Compras\OrdenCompraResource;
use App\Models\Compras\OrdenCompra;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Compras\ComprasRepository;
use App\Repositories\Configuracion\ParametrosRepository;
use App\Services\Compras\ComprasService;
use Illuminate\Http\JsonResponse;

class ComprasController extends Controller
{
    public function __construct(
        protected ComprasRepository $comprasRepository,
        protected CatalogosRepository $catalogosRepository,
        protected ComprasService $comprasService,
        protected ParametrosRepository $parametrosRepository
    ) {}

    public function index(): JsonResponse
    {
        $ordenes = OrdenCompraResource::collection($this->comprasRepository->getOrdenes())->resolve();

        $proveedores = $this->catalogosRepository->getProveedoresActivos()->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
            ];
        })->values();

        $insumos = $this->catalogosRepository->getInsumosActivos()->map(function ($i) {
            return [
                'id' => $i->id,
                'codigo' => $i->codigo,
                'nombre' => $i->nombre,
                'costo_promedio' => (string) $i->costo_promedio,
                'unidad' => $i->unidad?->abreviatura ?? '',
            ];
        })->values();

        return response()->json([
            'ordenes' => $ordenes,
            'proveedores' => $proveedores,
            'insumos' => $insumos,
            'iva_porcentaje' => $this->parametrosRepository->valorDecimal('iva_porcentaje', 15.0),
        ]);
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        $orden = $this->comprasService->registrarOrden(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Orden de compra creada. Queda pendiente hasta su recepción en almacén.',
            'orden' => (new OrdenCompraResource($orden))->resolve(),
        ], 201);
    }

    public function recibir(RecibirCompraRequest $request, OrdenCompra $orden): JsonResponse
    {
        $ordenActualizada = $this->comprasService->recibirOrden(
            $orden,
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Orden recibida en almacén: stock, Kardex y finanzas actualizados.',
            'orden' => (new OrdenCompraResource($ordenActualizada))->resolve(),
        ]);
    }
}
