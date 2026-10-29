<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ventas\GestionarCajaRequest;
use App\Http\Requests\Ventas\StoreVentaRequest;
use App\Http\Resources\Ventas\CajaTurnoResource;
use App\Http\Resources\Ventas\VentaResource;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Ventas\VentasRepository;
use App\Services\Ventas\VentasService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VentasController extends Controller
{
    public function __construct(
        protected VentasRepository $ventasRepository,
        protected CatalogosRepository $catalogosRepository,
        protected VentasService $ventasService
    ) {}

    public function index(): Response
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
        });

        $clientes = $this->catalogosRepository->getClientesActivos()->map(function ($c) {
            return [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'tipo' => $c->tipo,
            ];
        });

        return Inertia::render('Erp/Ventas', [
            'turnoActivo' => $turnoActivo ? (new CajaTurnoResource($turnoActivo))->resolve() : null,
            'ventas' => VentaResource::collection($this->ventasRepository->getVentasRecientes(25))->resolve(),
            'productos' => $productos,
            'clientes' => $clientes,
            'historialTurnos' => CajaTurnoResource::collection($this->ventasRepository->getHistorialTurnos(10))->resolve(),
        ]);
    }

    public function store(StoreVentaRequest $request): RedirectResponse
    {
        $this->ventasService->registrarVenta(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Venta registrada, stock descontado y caja actualizada.');
    }

    public function gestionarCaja(GestionarCajaRequest $request): RedirectResponse
    {
        if ($request->input('accion') === 'abrir') {
            $this->ventasService->abrirTurno(
                $request->validated(),
                $request->user()->id,
                $request->ip()
            );

            return back()->with('success', 'Turno de caja abierto correctamente.');
        }

        $this->ventasService->cerrarTurno(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Corte de caja ejecutado y registrado en auditoría.');
    }
}
