<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compras\RecibirCompraRequest;
use App\Http\Requests\Compras\StoreCompraRequest;
use App\Http\Resources\Compras\OrdenCompraResource;
use App\Models\Compras\OrdenCompra;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Compras\ComprasRepository;
use App\Services\Compras\ComprasService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ComprasController extends Controller
{
    public function __construct(
        protected ComprasRepository $comprasRepository,
        protected CatalogosRepository $catalogosRepository,
        protected ComprasService $comprasService
    ) {}

    public function index(): Response
    {
        $ordenes = OrdenCompraResource::collection($this->comprasRepository->getOrdenes())->resolve();

        $proveedores = $this->catalogosRepository->getProveedoresActivos()->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
            ];
        });

        $insumos = $this->catalogosRepository->getInsumosActivos()->map(function ($i) {
            return [
                'id' => $i->id,
                'codigo' => $i->codigo,
                'nombre' => $i->nombre,
                'costo_promedio' => (string) $i->costo_promedio,
                'unidad' => $i->unidad?->abreviatura ?? '',
            ];
        });

        return Inertia::render('Erp/Compras', [
            'ordenes' => $ordenes,
            'proveedores' => $proveedores,
            'insumos' => $insumos,
        ]);
    }

    public function store(StoreCompraRequest $request): RedirectResponse
    {
        $this->comprasService->registrarOrden(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Orden de compra creada. Queda pendiente hasta su recepción en almacén.');
    }

    public function recibir(RecibirCompraRequest $request, OrdenCompra $orden): RedirectResponse
    {
        $this->comprasService->recibirOrden(
            $orden,
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Orden recibida en almacén: stock, Kardex y finanzas actualizados.');
    }
}
