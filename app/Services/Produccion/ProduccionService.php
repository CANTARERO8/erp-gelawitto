<?php

namespace App\Services\Produccion;

use App\Models\Produccion\LoteProduccion;
use App\Models\Produccion\Receta;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Repositories\Produccion\ProduccionRepository;
use App\Services\Finanzas\ContabilidadService;
use App\Services\Seguridad\SeguridadService;
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
            ], $data['ingredientes']);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Producción',
                'Alta de Receta Técnica',
                "Se creó la fórmula '{$receta->nombre}' con ".count($data['ingredientes']).' ingredientes.',
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
     * Paso 2: ejecuta una orden programada. Aquí se descuentan los insumos de la receta,
     * se calcula el costo y la merma, y el producto terminado entra al Kardex.
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
                $insumo = $this->catalogosRepository->findInsumoForUpdate($ing->insumo_id);
                $requerido = round((float) $ing->cantidad_requerida * $factorEscala, 3);

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
            $costoUnitarioResultante = round($costoTotalLote / (float) $data['cantidad_obtenida'], 2);
            $merma = max(0, round((float) $loteBloqueado->cantidad_programada - (float) $data['cantidad_obtenida'], 3));

            $loteBloqueado->update([
                'cantidad_obtenida' => $data['cantidad_obtenida'],
                'merma' => $merma,
                'costo_total_lote' => $costoTotalLote,
                'costo_unitario_resultante' => $costoUnitarioResultante,
                'estado' => 'Finalizado',
                'notas' => $this->notasEjecucion($loteBloqueado->notas, $data['notas'] ?? null),
            ]);

            $nuevoStockProducto = round((float) $producto->stock_actual + (float) $data['cantidad_obtenida'], 3);
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
                'cantidad' => $data['cantidad_obtenida'],
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
                "Lote {$codigoLote} completado: {$data['cantidad_obtenida']} uds de {$producto->nombre} (Costo unitario: C$ {$costoUnitarioResultante}).",
                $ip
            );

            return $loteBloqueado->fresh();
        });
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
