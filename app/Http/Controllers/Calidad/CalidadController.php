<?php

namespace App\Http\Controllers\Calidad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calidad\RegistrarInspeccionRequest;
use App\Models\Produccion\LoteProduccion;
use App\Repositories\Calidad\CalidadRepository;
use App\Repositories\Iiot\IiotRepository;
use App\Services\Calidad\CalidadService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CalidadController extends Controller
{
    public function __construct(
        protected CalidadRepository $calidadRepository,
        protected CalidadService $calidadService,
        protected IiotRepository $iiotRepository
    ) {}

    public function index(): Response
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

        return Inertia::render('Erp/Calidad', [
            'trazabilidad' => $trazabilidad,
            'pendientes' => $pendientes,
            'lecturas' => $lecturas,
            'rango' => [
                'minimo' => (float) config('iiot.refrigerador.temperatura_minima'),
                'maximo' => (float) config('iiot.refrigerador.temperatura_maxima'),
                'dispositivo' => config('iiot.refrigerador.codigo'),
            ],
        ]);
    }

    public function inspeccionar(RegistrarInspeccionRequest $request, LoteProduccion $lote): RedirectResponse
    {
        $this->calidadService->registrarInspeccion(
            $lote,
            $request->validated(),
            $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Inspección registrada y disposición aplicada al inventario.');
    }
}
