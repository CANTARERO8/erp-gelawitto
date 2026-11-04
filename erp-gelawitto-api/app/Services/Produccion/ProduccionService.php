<?php

namespace App\Services\Produccion;

use App\Models\Compras\LoteInsumo;
use App\Models\Produccion\ConsumoLote;
use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Repositories\Produccion\ProduccionRepository;
use App\Services\Finanzas\ContabilidadService;
use App\Services\Seguridad\SeguridadService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProduccionService
{
    public function __construct(
        protected ProduccionRepository $produccionRepository,
        protected CatalogosRepository $catalogosRepository,
        protected InventarioRepository $inventarioRepository,
        protected SeguridadService $seguridadService,
        protected ?ContabilidadService $contabilidadService = null
    ) {
        $this->contabilidadService ??= app(ContabilidadService::class);
    }

    public function crearReceta(array $data, int $actorId, ?string $ip): Receta
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $receta = $this->produccionRepository->createReceta([
                'producto_id' => $data['producto_id'],
                'nombre' => $data['nombre'],
                'rendimiento_unidades' => $data['rendimiento_unidades'],
                'tiempo_preparacion_min' => $data['tiempo_preparacion_min'],
                'instrucciones' => $data['instrucciones'] ?? 'Fórmula técnica estándar de planta.',
                'activa' => true,
            ], $data['ingredientes'], $data['rutas'] ?? []);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Producción',
                'Alta de Receta Técnica',
                "Se creó la fórmula '{$receta->nombre}' con ".count($data['ingredientes']).' componentes.',
                $ip
            );

            return $receta;
        });
    }

    /**
     * Paso 1: programa la orden de producción. No descuenta insumos ni suma producto.
     */
    public function programarLote(array $data, int $actorId, ?string $ip): LoteProduccion
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $receta = $this->produccionRepository->findReceta((int) $data['receta_id']);

            if (! $receta) {
                throw ValidationException::withMessages(['receta_id' => 'La receta seleccionada no existe.']);
            }

            $codigoLote = $this->produccionRepository->getNextCodigoLote();

            $lote = $this->produccionRepository->createLote([
                'codigo_lote' => $codigoLote,
                'receta_id' => $receta->id,
                'producto_id' => $receta->producto_id,
                'user_id' => $actorId,
                'fecha_produccion' => now()->toDateString(),
                'cantidad_programada' => $data['cantidad_programada'],
                'cantidad_obtenida' => null,
                'cantidad_disponible' => 0,
                'merma' => 0,
                'costo_total_lote' => null,
                'costo_unitario_resultante' => null,
                'estado' => 'Programada',
                'notas' => $data['notas'] ?? 'Orden de producción programada.',
            ]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Producción',
                'Alta de Orden de Producción',
                "Orden {$codigoLote} programada: {$data['cantidad_programada']} uds de {$receta->nombre}. Insumos pendientes de descuento.",
                $ip
            );

            return $lote;
        });
    }

    /**
     * Paso 2: ejecuta una orden programada. Descuenta insumos y/o subensambles (BOM 2 niveles)
     * con trazabilidad FEFO, calcula costo en cascada y merma, e ingresa producto al Kardex.
     */
    public function ejecutarLote(LoteProduccion $lote, array $data, int $actorId, ?string $ip): LoteProduccion
    {
        return DB::transaction(function () use ($lote, $data, $actorId, $ip) {
            $loteBloqueado = $this->produccionRepository->findLoteForUpdate($lote->id);

            if ($loteBloqueado->estado !== 'Programada') {
                throw ValidationException::withMessages([
                    'orden' => "La orden {$loteBloqueado->codigo_lote} ya fue {$loteBloqueado->estado}.",
                ]);
            }

            $receta = $this->produccionRepository->findReceta((int) $loteBloqueado->receta_id);

            $producto = $this->catalogosRepository->findProductoForUpdate($receta->producto_id);

            if (! $producto) {
                throw ValidationException::withMessages(['cantidad_obtenida' => 'El producto asociado a la receta no existe.']);
            }

            $codigoLote = $loteBloqueado->codigo_lote;
            $factorEscala = (float) $loteBloqueado->cantidad_programada / (float) $receta->rendimiento_unidades;
            $costoTotalLote = 0.0;

            foreach ($receta->ingredientes as $ing) {
                $requerido = round((float) $ing->cantidad_requerida * $factorEscala, 3);

                if ($ing->subproducto_id !== null) {
                    $subproducto = $this->catalogosRepository->findProductoForUpdate((int) $ing->subproducto_id);

                    if (! $subproducto || (float) $subproducto->stock_actual < $requerido) {
                        $disponible = $subproducto ? $subproducto->stock_actual : 0;
                        $nombre = $subproducto ? $subproducto->nombre : 'Subensamble #'.$ing->subproducto_id;
                        throw ValidationException::withMessages([
                            'cantidad_obtenida' => "Subensamble insuficiente: {$nombre} requiere {$requerido} pero hay {$disponible}.",
                        ]);
                    }

                    $nuevoStockSub = round((float) $subproducto->stock_actual - $requerido, 3);
                    $costoTotalLote += $requerido * (float) $subproducto->costo_unitario;

                    $subproducto->update(['stock_actual' => $nuevoStockSub]);

                    $this->consumirLotesSubproductoFefo($loteBloqueado->id, $subproducto->id, $requerido);

                    $this->inventarioRepository->registrarMovimiento([
                        'fecha' => now(),
                        'tipo_item' => 'producto',
                        'item_id' => $subproducto->id,
                        'nombre_item' => $subproducto->nombre,
                        'tipo_movimiento' => 'Consumo de Producción',
                        'referencia' => $codigoLote,
                        'cantidad' => -1 * $requerido,
                        'stock_resultante' => $nuevoStockSub,
                        'costo_unitario' => $subproducto->costo_unitario,
                        'user_id' => $actorId,
                    ]);

                    continue;
                }

                $insumo = $this->catalogosRepository->findInsumoForUpdate((int) $ing->insumo_id);

                if (! $insumo || (float) $insumo->stock_actual < $requerido) {
                    $disponible = $insumo ? $insumo->stock_actual : 0;
                    $nombre = $insumo ? $insumo->nombre : 'Insumo #'.$ing->insumo_id;
                    throw ValidationException::withMessages([
                        'cantidad_obtenida' => "Insumo insuficiente: {$nombre} requiere {$requerido} pero hay {$disponible}.",
                    ]);
                }

                $nuevoStockInsumo = round((float) $insumo->stock_actual - $requerido, 3);
                $costoTotalLote += $requerido * (float) $insumo->costo_promedio;

                $insumo->update(['stock_actual' => $nuevoStockInsumo]);

                $this->consumirLotesInsumoFefo($loteBloqueado->id, $insumo->id, $requerido);

                $this->inventarioRepository->registrarMovimiento([
                    'fecha' => now(),
                    'tipo_item' => 'insumo',
                    'item_id' => $insumo->id,
                    'nombre_item' => $insumo->nombre,
                    'tipo_movimiento' => 'Consumo de Producción',
                    'referencia' => $codigoLote,
                    'cantidad' => -1 * $requerido,
                    'stock_resultante' => $nuevoStockInsumo,
                    'costo_unitario' => $insumo->costo_promedio,
                    'user_id' => $actorId,
                ]);
            }

            $costoTotalLote = round($costoTotalLote, 2);
            $cantidadObtenida = (float) $data['cantidad_obtenida'];
            $costoUnitarioResultante = round($costoTotalLote / $cantidadObtenida, 2);
            $merma = max(0, round((float) $loteBloqueado->cantidad_programada - $cantidadObtenida, 3));

            $tiempoPlanMin = $this->calcularTiempoPlanificadoMin($receta, (float) $loteBloqueado->cantidad_programada);
            $finReal = ! empty($data['fin_real']) ? Carbon::parse($data['fin_real']) : now();
            $inicioReal = ! empty($data['inicio_real'])
                ? Carbon::parse($data['inicio_real'])
                : $finReal->copy()->subMinutes((int) round(max(1, $tiempoPlanMin)));
            $fechaVencimiento = $data['fecha_vencimiento'] ?? now()->addDays(15)->toDateString();

            $loteBloqueado->update([
                'cantidad_obtenida' => $cantidadObtenida,
                'cantidad_disponible' => $cantidadObtenida,
                'fecha_vencimiento' => $fechaVencimiento,
                'inicio_real' => $inicioReal,
                'fin_real' => $finReal,
                'tiempo_paro_min' => (float) ($data['tiempo_paro_min'] ?? 0),
                'merma' => $merma,
                'costo_total_lote' => $costoTotalLote,
                'costo_unitario_resultante' => $costoUnitarioResultante,
                'estado' => 'Finalizado',
                'notas' => $this->notasEjecucion($loteBloqueado->notas, $data['notas'] ?? null),
            ]);

            $nuevoStockProducto = round((float) $producto->stock_actual + $cantidadObtenida, 3);
            $valorActualProducto = (float) $producto->stock_actual * (float) $producto->costo_unitario;
            $nuevoCostoUnitarioProducto = $nuevoStockProducto > 0
                ? round(($valorActualProducto + $costoTotalLote) / $nuevoStockProducto, 2)
                : $costoUnitarioResultante;

            $producto->update([
                'stock_actual' => $nuevoStockProducto,
                'costo_unitario' => $nuevoCostoUnitarioProducto,
            ]);

            $this->inventarioRepository->registrarMovimiento([
                'fecha' => now(),
                'tipo_item' => 'producto',
                'item_id' => $producto->id,
                'nombre_item' => $producto->nombre,
                'tipo_movimiento' => 'Ingreso de Producción',
                'referencia' => $codigoLote,
                'cantidad' => $cantidadObtenida,
                'stock_resultante' => $nuevoStockProducto,
                'costo_unitario' => $costoUnitarioResultante,
                'user_id' => $actorId,
            ]);

            $this->contabilidadService->asentarLoteProduccion(
                $codigoLote,
                $costoTotalLote,
                $producto->nombre,
                $actorId
            );

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Producción',
                'Ejecución de Lote',
                "Lote {$codigoLote} completado: {$cantidadObtenida} uds de {$producto->nombre} (Costo unitario: C$ {$costoUnitarioResultante}).",
                $ip
            );

            return $loteBloqueado->fresh();
        });
    }

    public function calcularTiempoPlanificadoMin(Receta $receta, float $cantidadProgramada): float
    {
        if ($receta->relationLoaded('rutas') && $receta->rutas->isNotEmpty()) {
            $total = 0.0;
            foreach ($receta->rutas as $ruta) {
                $total += (float) $ruta->tiempo_preparacion_min + ((float) $ruta->tiempo_ciclo_min_por_unidad * $cantidadProgramada);
            }

            return round($total, 2);
        }

        $factor = $cantidadProgramada / max(0.1, (float) $receta->rendimiento_unidades);

        return round((float) $receta->tiempo_preparacion_min * $factor, 2);
    }

    /**
     * Calcula OEE (Disponibilidad x Rendimiento x Calidad) y Tiempo de Ciclo real vs estándar
     * sobre todos los lotes finalizados en planta (Nakajima, 1988).
     */
    public function calcularMetricasOee(): array
    {
        $lotes = LoteProduccion::with(['producto', 'receta.rutas', 'controles'])
            ->where('estado', 'Finalizado')
            ->whereNotNull('cantidad_obtenida')
            ->orderByDesc('id')
            ->get();

        if ($lotes->isEmpty()) {
            return [
                'disponibilidad' => 0.0,
                'rendimiento' => 0.0,
                'calidad' => 0.0,
                'oee_global' => 0.0,
                'tiempo_ciclo_real_promedio_min' => 0.0,
                'tiempo_ciclo_estandar_promedio_min' => 0.0,
                'lotes' => [],
            ];
        }

        $sumDisp = 0.0;
        $sumRend = 0.0;
        $sumCal = 0.0;
        $sumCicloReal = 0.0;
        $sumCicloEst = 0.0;
        $detalleLotes = [];

        foreach ($lotes as $lote) {
            $receta = $lote->receta;
            $cantProg = max(0.001, (float) $lote->cantidad_programada);
            $cantObt = max(0.001, (float) $lote->cantidad_obtenida);

            $tiempoPlanMin = $receta
                ? $this->calcularTiempoPlanificadoMin($receta, $cantProg)
                : 30.0;

            $tiempoRealMin = ($lote->inicio_real && $lote->fin_real)
                ? max(1.0, round(abs($lote->fin_real->diffInSeconds($lote->inicio_real)) / 60, 2))
                : $tiempoPlanMin;

            $paroMin = (float) ($lote->tiempo_paro_min ?? 0);
            $tiempoOperativoMin = max(0.5, $tiempoRealMin - $paroMin);

            $cicloIdealUnidad = $tiempoPlanMin / $cantProg;
            $cicloRealUnidad = $tiempoOperativoMin / $cantObt;

            $disponibilidad = min(1.0, $tiempoOperativoMin / max(1.0, $tiempoRealMin));
            $rendimiento = min(1.0, ($cicloIdealUnidad * $cantObt) / $tiempoOperativoMin);

            $noConformes = (float) $lote->controles->sum('unidades_no_conformes');
            $conformes = max(0.0, $cantObt - $noConformes);
            $calidad = min(1.0, $conformes / $cantObt);

            $oeeLote = round($disponibilidad * $rendimiento * $calidad * 100, 1);

            $sumDisp += $disponibilidad;
            $sumRend += $rendimiento;
            $sumCal += $calidad;
            $sumCicloReal += $cicloRealUnidad;
            $sumCicloEst += $cicloIdealUnidad;

            $detalleLotes[] = [
                'codigo_lote' => $lote->codigo_lote,
                'producto' => $lote->producto?->nombre ?? '',
                'cantidad_obtenida' => round($cantObt, 3),
                'unidades_conformes' => round($conformes, 3),
                'tiempo_planificado_min' => round($tiempoPlanMin, 2),
                'tiempo_real_min' => round($tiempoRealMin, 2),
                'tiempo_paro_min' => round($paroMin, 2),
                'tiempo_operativo_min' => round($tiempoOperativoMin, 2),
                'ciclo_estandar_min' => round($cicloIdealUnidad, 2),
                'ciclo_real_min' => round($cicloRealUnidad, 2),
                'disponibilidad' => round($disponibilidad * 100, 1),
                'rendimiento' => round($rendimiento * 100, 1),
                'calidad' => round($calidad * 100, 1),
                'oee' => $oeeLote,
            ];
        }

        $n = count($detalleLotes);
        $dispProm = $sumDisp / $n;
        $rendProm = $sumRend / $n;
        $calProm = $sumCal / $n;

        return [
            'disponibilidad' => round($dispProm * 100, 1),
            'rendimiento' => round($rendProm * 100, 1),
            'calidad' => round($calProm * 100, 1),
            'oee_global' => round($dispProm * $rendProm * $calProm * 100, 1),
            'tiempo_ciclo_real_promedio_min' => round($sumCicloReal / $n, 2),
            'tiempo_ciclo_estandar_promedio_min' => round($sumCicloEst / $n, 2),
            'lotes' => $detalleLotes,
        ];
    }

    private function consumirLotesInsumoFefo(int $loteProduccionId, int $insumoId, float $requerido): void
    {
        $restante = $requerido;
        $lotesInsumo = LoteInsumo::where('insumo_id', $insumoId)
            ->where('cantidad_disponible', '>', 0)
            ->orderByRaw('fecha_vencimiento ASC NULLS LAST, id ASC')
            ->lockForUpdate()
            ->get();

        foreach ($lotesInsumo as $loteInsumo) {
            if ($restante <= 0) {
                break;
            }
            $disp = (float) $loteInsumo->cantidad_disponible;
            $tomar = round(min($disp, $restante), 3);
            if ($tomar <= 0) {
                continue;
            }

            $loteInsumo->update([
                'cantidad_disponible' => round($disp - $tomar, 3),
            ]);

            ConsumoLote::create([
                'lote_produccion_id' => $loteProduccionId,
                'lote_insumo_id' => $loteInsumo->id,
                'subproducto_lote_id' => null,
                'cantidad' => $tomar,
            ]);

            $restante = round($restante - $tomar, 3);
        }
    }

    private function consumirLotesSubproductoFefo(int $loteProduccionId, int $subproductoId, float $requerido): void
    {
        $restante = $requerido;
        $lotesSub = LoteProduccion::where('producto_id', $subproductoId)
            ->where('estado', 'Finalizado')
            ->where('cantidad_disponible', '>', 0)
            ->orderByRaw('fecha_vencimiento ASC NULLS LAST, id ASC')
            ->lockForUpdate()
            ->get();

        foreach ($lotesSub as $subLote) {
            if ($restante <= 0) {
                break;
            }
            $disp = (float) $subLote->cantidad_disponible;
            $tomar = round(min($disp, $restante), 3);
            if ($tomar <= 0) {
                continue;
            }

            $subLote->update([
                'cantidad_disponible' => round($disp - $tomar, 3),
            ]);

            ConsumoLote::create([
                'lote_produccion_id' => $loteProduccionId,
                'lote_insumo_id' => null,
                'subproducto_lote_id' => $subLote->id,
                'cantidad' => $tomar,
            ]);

            $restante = round($restante - $tomar, 3);
        }
    }

    private function notasEjecucion(?string $notasOrden, ?string $notasEjecucion): string
    {
        $base = $notasOrden ?? '';

        if ($notasEjecucion === null || $notasEjecucion === '') {
            return $base;
        }

        return trim($base.' | Ejecución: '.$notasEjecucion);
    }
}
