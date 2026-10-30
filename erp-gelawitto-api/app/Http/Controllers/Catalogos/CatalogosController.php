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
use Illuminate\Http\JsonResponse;

class CatalogosController extends Controller
{
    public function __construct(
        protected CatalogosRepository $catalogosRepository,
        protected CatalogosService $catalogosService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'productos' => ProductoResource::collection($this->catalogosRepository->getProductos())->resolve(),
            'insumos' => InsumoResource::collection($this->catalogosRepository->getInsumos())->resolve(),
            'proveedores' => ProveedorResource::collection($this->catalogosRepository->getProveedores())->resolve(),
            'clientes' => ClienteResource::collection($this->catalogosRepository->getClientes())->resolve(),
            'unidades' => UnidadMedidaResource::collection($this->catalogosRepository->getUnidades())->resolve(),
        ]);
    }

    public function storeProducto(StoreProductoRequest $request): JsonResponse
    {
        $producto = $this->catalogosService->crearProducto(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Producto registrado correctamente en el catálogo.',
            'producto' => (new ProductoResource($producto))->resolve(),
        ], 201);
    }

    public function storeInsumo(StoreInsumoRequest $request): JsonResponse
    {
        $insumo = $this->catalogosService->crearInsumo(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Insumo registrado correctamente en el catálogo.',
            'insumo' => (new InsumoResource($insumo))->resolve(),
        ], 201);
    }

    public function storeProveedor(StoreProveedorRequest $request): JsonResponse
    {
        $proveedor = $this->catalogosService->crearProveedor(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Proveedor registrado correctamente en el catálogo.',
            'proveedor' => (new ProveedorResource($proveedor))->resolve(),
        ], 201);
    }

    public function storeCliente(StoreClienteRequest $request): JsonResponse
    {
        $cliente = $this->catalogosService->crearCliente(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Cliente registrado correctamente en el catálogo.',
            'cliente' => (new ClienteResource($cliente))->resolve(),
        ], 201);
    }
}
