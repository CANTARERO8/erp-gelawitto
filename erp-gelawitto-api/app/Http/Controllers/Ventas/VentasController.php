<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ventas\GestionarCajaRequest;
use App\Http\Requests\Ventas\StoreVentaRequest;
use App\Http\Resources\Ventas\CajaTurnoResource;
use App\Http\Resources\Ventas\VentaResource;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Configuracion\ParametrosRepository;
use App\Repositories\Ventas\VentasRepository;
use App\Services\Ventas\VentasService;
use Illuminate\Http\JsonResponse;

class VentasController extends Controller
{
    public function __construct(
        protected VentasRepository $ventasRepository,
        protected CatalogosRepository $catalogosRepository,
        protected VentasService $ventasService,
        protected ParametrosRepository $parametrosRepository
    ) {}

    public function index(): JsonResponse
    {
        $turnoActivo = $this->ventasRepository->getTurnoActivo();

        $productos = $this->catalogosRepository->getProductosActivos()->map(function ($p) {
            $enCuarentena = $this->ventasRepository->getStockEnCuarentena($p->id);
            $disponible = max(0, round((float) $p->stock_actual - $enCuarentena, 3));

            return [
                'id' => $p->id,
                'sku' => $p->sku,
                'nombre' => $p->nombre,
                'presentacion' => $p->presentacion,
                'precio_venta' => (string) $p->precio_venta,
                'stock_actual' => (string) $p->stock_actual,
                'stock_en_cuarentena' => (string) $enCuarentena,
                'stock_disponible' => (string) $disponible,
            ];
        })->values();

        $clientes = $this->catalogosRepository->getClientesActivos()->map(function ($c) {
            return [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'tipo' => $c->tipo,
            ];
        })->values();

        return response()->json([
            'turnoActivo' => $turnoActivo ? (new CajaTurnoResource($turnoActivo))->resolve() : null,
            'ventas' => VentaResource::collection($this->ventasRepository->getVentasRecientes(25))->resolve(),
            'productos' => $productos,
            'clientes' => $clientes,
            'historialTurnos' => CajaTurnoResource::collection($this->ventasRepository->getHistorialTurnos(10))->resolve(),
            'iva_porcentaje' => $this->parametrosRepository->valorDecimal('iva_porcentaje', 15.0),
        ]);
    }

    public function store(StoreVentaRequest $request): JsonResponse
    {
        $venta = $this->ventasService->registrarVenta(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Venta registrada, stock descontado y caja actualizada.',
            'venta' => (new VentaResource($venta))->resolve(),
        ], 201);
    }

    public function gestionarCaja(GestionarCajaRequest $request): JsonResponse
    {
        if ($request->input('accion') === 'abrir') {
            $turno = $this->ventasService->abrirTurno(
                $request->validated(),
                $request->user()->id,
                $request->ip()
            );

            return response()->json([
                'message' => 'Turno de caja abierto correctamente.',
                'turno' => (new CajaTurnoResource($turno->load('cajero')))->resolve(),
            ]);
        }

        $turnoCerrado = $this->ventasService->cerrarTurno(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Corte de caja ejecutado y registrado en auditoría.',
            'turno' => (new CajaTurnoResource($turnoCerrado->load('cajero')))->resolve(),
        ]);
    }
}
