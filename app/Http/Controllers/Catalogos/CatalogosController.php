<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogos\StoreClienteRequest;
use App\Http\Requests\Catalogos\StoreInsumoRequest;
use App\Http\Requests\Catalogos\StoreProductoRequest;
use App\Http\Requests\Catalogos\StoreProveedorRequest;
use App\Http\Resources\Catalogos\ClienteResource;
use App\Http\Resources\Catalogos\InsumoResource;
use App\Http\Resources\Catalogos\ProductoResource;
use App\Http\Resources\Catalogos\ProveedorResource;
use App\Http\Resources\Catalogos\UnidadMedidaResource;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Services\Catalogos\CatalogosService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CatalogosController extends Controller
{
    public function __construct(
        protected CatalogosRepository $catalogosRepository,
        protected CatalogosService $catalogosService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Erp/Catalogos', [
            'productos' => ProductoResource::collection($this->catalogosRepository->getProductos())->resolve(),
            'insumos' => InsumoResource::collection($this->catalogosRepository->getInsumos())->resolve(),
            'proveedores' => ProveedorResource::collection($this->catalogosRepository->getProveedores())->resolve(),
            'clientes' => ClienteResource::collection($this->catalogosRepository->getClientes())->resolve(),
            'unidades' => UnidadMedidaResource::collection($this->catalogosRepository->getUnidades())->resolve(),
        ]);
    }

    public function storeProducto(StoreProductoRequest $request): RedirectResponse
    {
        $this->catalogosService->crearProducto(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Producto registrado correctamente en el catálogo.');
    }

    public function storeInsumo(StoreInsumoRequest $request): RedirectResponse
    {
        $this->catalogosService->crearInsumo(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Insumo registrado correctamente en el catálogo.');
    }

    public function storeProveedor(StoreProveedorRequest $request): RedirectResponse
    {
        $this->catalogosService->crearProveedor(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Proveedor registrado correctamente en el catálogo.');
    }

    public function storeCliente(StoreClienteRequest $request): RedirectResponse
    {
        $this->catalogosService->crearCliente(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Cliente registrado correctamente en el catálogo.');
    }
}
