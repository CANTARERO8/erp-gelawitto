<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finanzas\StoreGastoRequest;
use App\Http\Resources\Finanzas\FinanzasMovimientoResource;
use App\Repositories\Finanzas\FinanzasRepository;
use App\Services\Finanzas\ContabilidadService;
use App\Services\Finanzas\FinanzasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanzasController extends Controller
{
    public function __construct(
        protected FinanzasRepository $finanzasRepository,
        protected FinanzasService $finanzasService,
        protected ContabilidadService $contabilidadService
    ) {}

    public function index(): JsonResponse
    {
        $ingresos = $this->finanzasRepository->getIngresosCobradosTotal();
        $egresos = $this->finanzasRepository->getEgresosPagadosTotal();
        $cuentasPorPagar = $this->finanzasRepository->getCuentasPorPagarTotal();
        $cuentasPorCobrar = $this->finanzasRepository->getCuentasPorCobrarTotal();

        return response()->json([
            'movimientos' => FinanzasMovimientoResource::collection($this->finanzasRepository->getMovimientos())->resolve(),
            'resumen' => [
                'ingresos' => round($ingresos, 2),
                'egresos' => round($egresos, 2),
                'flujo_neto' => round($ingresos - $egresos, 2),
                'cuentas_por_pagar' => round($cuentasPorPagar, 2),
            ],
            'cuentas_por_cobrar' => round($cuentasPorCobrar, 2),
            'contabilidad' => $this->contabilidadService->obtenerEstadosFinancieros(),
        ]);
    }

    public function storeGasto(StoreGastoRequest $request): JsonResponse
    {
        $movimiento = $this->finanzasService->registrarGasto(
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Movimiento financiero registrado correctamente.',
            'movimiento' => (new FinanzasMovimientoResource($movimiento->load('usuario')))->resolve(),
        ], 201);
    }

    public function liquidar(Request $request, int $id): JsonResponse
    {
        $movimiento = $this->finanzasService->liquidarCuenta(
            $id,
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Cuenta liquidada y asentada en Libro Diario.',
            'movimiento' => (new FinanzasMovimientoResource($movimiento->load('usuario')))->resolve(),
        ]);
    }
}
