<?php

namespace App\Http\Controllers\Calidad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calidad\RegistrarInspeccionRequest;
use App\Models\Produccion\LoteProduccion;
use App\Repositories\Calidad\CalidadRepository;
use App\Repositories\Configuracion\ParametrosRepository;
use App\Repositories\Iiot\IiotRepository;
use App\Services\Calidad\CalidadService;
use Illuminate\Http\JsonResponse;

class CalidadController extends Controller
{
    public function __construct(
        protected CalidadRepository $calidadRepository,
        protected CalidadService $calidadService,
        protected IiotRepository $iiotRepository,
        protected ParametrosRepository $parametrosRepository
    ) {}

    public function index(): JsonResponse
    {
        $lotes = $this->calidadRepository->getLotesConTrazabilidad();
        $consumos = $this->calidadRepository->getConsumosPorLote($lotes->pluck('codigo_lote')->all());

        $trazabilidad = $lotes->map(function (LoteProduccion $lote) use ($consumos) {
            return [
                'id' => $lote->id,
                'codigo_lote' => $lote->codigo_lote,
                'producto_nombre' => $lote->producto?->nombre ?? '',
                'estado' => $lote->estado,
                'estado_calidad' => $lote->estado_calidad,
                'cantidad_programada' => (string) $lote->cantidad_programada,
                'cantidad_obtenida' => $lote->cantidad_obtenida !== null ? (string) $lote->cantidad_obtenida : null,
                'insumos' => ($consumos->get($lote->codigo_lote) ?? collect())->map(fn ($m) => [
                    'nombre' => $m->nombre_item,
                    'cantidad' => (string) abs((float) $m->cantidad),
                ])->values()->all(),
                'controles' => $lote->controles->map(fn ($c) => [
                    'codigo_control' => $c->codigo_control,
                    'fecha' => $c->fecha_inspeccion->format('Y-m-d H:i'),
                    'inspector' => $c->inspector?->name ?? '',
                    'unidades_inspeccionadas' => (string) $c->unidades_inspeccionadas,
                    'unidades_no_conformes' => (string) $c->unidades_no_conformes,
                    'defecto' => $c->defecto ?? '',
                    'disposicion' => $c->disposicion,
                    'costo_perdido' => (string) $c->costo_perdido,
                ])->values()->all(),
            ];
        })->values()->all();

        $pendientes = $lotes
            ->where('estado', 'Finalizado')
            ->whereIn('estado_calidad', ['Pendiente', 'Retenido'])
            ->values()
            ->map(fn (LoteProduccion $lote) => [
                'id' => $lote->id,
                'codigo_lote' => $lote->codigo_lote,
                'producto_nombre' => $lote->producto?->nombre ?? '',
                'cantidad_obtenida' => (string) $lote->cantidad_obtenida,
            ])
            ->all();

        $lecturas = $this->iiotRepository->getUltimasLecturas()->map(fn ($l) => [
            'fecha' => $l->fecha_lectura->format('Y-m-d H:i:s'),
            'dispositivo' => $l->dispositivo,
            'origen' => $l->origen,
            'temperatura' => (string) $l->temperatura,
            'fuera_de_rango' => (bool) $l->fuera_de_rango,
        ])->values()->all();

        return response()->json([
            'trazabilidad' => $trazabilidad,
            'pendientes' => $pendientes,
            'lecturas' => $lecturas,
            'rango' => [
                'minimo' => $this->parametrosRepository->valorDecimal('refrigerador_temp_min', 2.0),
                'maximo' => $this->parametrosRepository->valorDecimal('refrigerador_temp_max', 6.0),
                'dispositivo' => $this->parametrosRepository->valor('refrigerador_codigo', 'REF-01'),
            ],
        ]);
    }

    public function inspeccionar(RegistrarInspeccionRequest $request, LoteProduccion $lote): JsonResponse
    {
        $control = $this->calidadService->registrarInspeccion(
            $lote,
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Inspección registrada y disposición aplicada al inventario.',
            'control' => $control,
        ]);
    }
}
