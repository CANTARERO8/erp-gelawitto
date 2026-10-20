<?php

namespace App\Services\Produccion;

use App\Models\Produccion\LoteProduccion;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Repositories\Produccion\ProduccionRepository;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProduccionService
{
    public function __construct(
        protected ProduccionRepository $produccionRepository,
        protected CatalogosRepository $catalogosRepository,
        protected InventarioRepository $inventarioRepository,
        protected SeguridadService $seguridadService
    ) {}

    public function registrarLote(array $data, int $actorId, ?string $ip): LoteProduccion
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $receta = $this->produccionRepository->findReceta((int) $data['receta_id']);

            if (! $receta) {
                throw ValidationException::withMessages(['receta_id' => 'La receta seleccionada no existe.']);
            }

            $producto = $this->catalogosRepository->findProductoForUpdate($receta->producto_id);

            if (! $producto) {
                throw ValidationException::withMessages(['receta_id' => 'El producto asociado a la receta no existe.']);
            }

            $factorEscala = (float) $data['cantidad_programada'] / (float) $receta->rendimiento_unidades;
            $codigoLote = $this->produccionRepository->getNextCodigoLote();
            $costoTotalLote = 0.0;

            foreach ($receta->ingredientes as $ing) {
                $insumo = $this->catalogosRepository->findInsumoForUpdate($ing->insumo_id);
                $requerido = round((float) $ing->cantidad_requerida * $factorEscala, 3);

                if (! $insumo || (float) $insumo->stock_actual < $requerido) {
                    $disponible = $insumo ? $insumo->stock_actual : 0;
                    $nombre = $insumo ? $insumo->nombre : 'Insumo #'.$ing->insumo_id;
                    throw ValidationException::withMessages([
                        'receta_id' => "Insumo insuficiente: {$nombre} requiere {$requerido} pero hay {$disponible}.",
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
            $merma = max(0, round((float) $data['cantidad_programada'] - (float) $data['cantidad_obtenida'], 3));

            $lote = $this->produccionRepository->createLote([
                'codigo_lote' => $codigoLote,
                'receta_id' => $receta->id,
                'producto_id' => $producto->id,
                'user_id' => $actorId,
                'fecha_produccion' => now()->toDateString(),
                'cantidad_programada' => $data['cantidad_programada'],
                'cantidad_obtenida' => $data['cantidad_obtenida'],
                'merma' => $merma,
                'costo_total_lote' => $costoTotalLote,
                'costo_unitario_resultante' => $costoUnitarioResultante,
                'estado' => 'Finalizado',
                'notas' => $data['notas'] ?? 'Producción estándar verificada.',
            ]);

            $nuevoStockProducto = round((float) $producto->stock_actual + (float) $data['cantidad_obtenida'], 3);
            $producto->update([
                'stock_actual' => $nuevoStockProducto,
                'costo_unitario' => $costoUnitarioResultante,
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

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Producción',
                'Registro de Lote',
                "Lote {$codigoLote} completado: {$data['cantidad_obtenida']} uds de {$producto->nombre} (Costo unitario: \${$costoUnitarioResultante}).",
                $ip
            );

            return $lote;
        });
    }
}
