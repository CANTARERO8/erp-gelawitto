<?php

namespace App\Services\Calidad;

use App\Models\Calidad\ControlCalidad;
use App\Models\Produccion\LoteProduccion;
use App\Repositories\Calidad\CalidadRepository;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Repositories\Produccion\ProduccionRepository;
use App\Services\Finanzas\ContabilidadService;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalidadService
{
    public function __construct(
        protected CalidadRepository $calidadRepository,
        protected ProduccionRepository $produccionRepository,
        protected CatalogosRepository $catalogosRepository,
        protected InventarioRepository $inventarioRepository,
        protected SeguridadService $seguridadService,
        protected ?ContabilidadService $contabilidadService = null
    ) {
        $this->contabilidadService ??= app(ContabilidadService::class);
    }

    /**
     * Registra la inspección de un lote ejecutado y aplica su disposición:
     * - Liberación: el lote cumple y todas las unidades quedan disponibles.
     * - Retrabajo: las unidades no conformes salen del stock vendible en espera de corrección.
     * - Scrap: las unidades no conformes se dan de baja y se registra el costo perdido.
     */
    public function registrarInspeccion(LoteProduccion $lote, array $data, int $actorId, ?string $ip): ControlCalidad
    {
        return DB::transaction(function () use ($lote, $data, $actorId, $ip) {
            $loteBloqueado = $this->produccionRepository->findLoteForUpdate($lote->id);

            if ($loteBloqueado->estado !== 'Finalizado') {
                throw ValidationException::withMessages([
                    'lote' => "La orden {$loteBloqueado->codigo_lote} debe ejecutarse antes de inspeccionarse.",
                ]);
            }

            // Un lote retenido por temperatura también puede inspeccionarse: Calidad decide su disposición.
            if (! in_array($loteBloqueado->estado_calidad, ['Pendiente', 'Retenido'], true)) {
                throw ValidationException::withMessages([
                    'lote' => "El lote {$loteBloqueado->codigo_lote} ya tiene disposición: {$loteBloqueado->estado_calidad}.",
                ]);
            }

            $inspeccionadas = (float) $data['unidades_inspeccionadas'];
            $noConformes = (float) ($data['unidades_no_conformes'] ?? 0);
            $disposicion = $data['disposicion'];
            $defecto = trim((string) ($data['defecto'] ?? ''));

            if ($inspeccionadas > (float) $loteBloqueado->cantidad_obtenida) {
                throw ValidationException::withMessages([
                    'unidades_inspeccionadas' => "No puede inspeccionar más de las {$loteBloqueado->cantidad_obtenida} unidades obtenidas.",
                ]);
            }

            if ($noConformes > $inspeccionadas) {
                throw ValidationException::withMessages([
                    'unidades_no_conformes' => 'Las unidades no conformes no pueden superar las inspeccionadas.',
                ]);
            }

            if ($noConformes == 0 && $disposicion !== 'Liberación') {
                throw ValidationException::withMessages([
                    'disposicion' => 'Sin unidades no conformes, la disposición debe ser Liberación.',
                ]);
            }

            if ($noConformes > 0 && $disposicion === 'Liberación') {
                throw ValidationException::withMessages([
                    'disposicion' => 'Con unidades no conformes, la disposición debe ser Retrabajo o Scrap.',
                ]);
            }

            if ($noConformes > 0 && $defecto === '') {
                throw ValidationException::withMessages([
                    'defecto' => 'Describa la no conformidad encontrada.',
                ]);
            }

            $costoPerdido = 0.0;
            $estadoCalidad = 'Liberado';

            if ($noConformes > 0) {
                $producto = $this->catalogosRepository->findProductoForUpdate($loteBloqueado->producto_id);

                if ((float) $producto->stock_actual < $noConformes) {
                    throw ValidationException::withMessages([
                        'unidades_no_conformes' => "Stock insuficiente de {$producto->nombre} para retirar {$noConformes} unidades. Disponible: {$producto->stock_actual}.",
                    ]);
                }

                $nuevoStock = round((float) $producto->stock_actual - $noConformes, 3);
                $costoPerdido = round($noConformes * (float) $producto->costo_unitario, 2);
                $tipoMovimiento = $disposicion === 'Scrap' ? 'Scrap por Calidad' : 'Retrabajo por Calidad';

                $producto->update(['stock_actual' => $nuevoStock]);

                $this->inventarioRepository->registrarMovimiento([
                    'fecha' => now(),
                    'tipo_item' => 'producto',
                    'item_id' => $producto->id,
                    'nombre_item' => $producto->nombre,
                    'tipo_movimiento' => $tipoMovimiento,
                    'referencia' => $loteBloqueado->codigo_lote,
                    'cantidad' => -1 * $noConformes,
                    'stock_resultante' => $nuevoStock,
                    'costo_unitario' => $producto->costo_unitario,
                    'user_id' => $actorId,
                ]);

                $this->contabilidadService->asentarBajaCalidadOMerma(
                    $loteBloqueado->codigo_lote,
                    'producto',
                    $costoPerdido,
                    "{$tipoMovimiento}: {$defecto}",
                    $actorId
                );

                $estadoCalidad = $disposicion;
            }

            $control = $this->calidadRepository->createControl([
                'codigo_control' => $this->calidadRepository->getNextCodigoControl(),
                'lote_id' => $loteBloqueado->id,
                'user_id' => $actorId,
                'fecha_inspeccion' => now(),
                'unidades_inspeccionadas' => $inspeccionadas,
                'unidades_no_conformes' => $noConformes,
                'defecto' => $defecto !== '' ? $defecto : null,
                'disposicion' => $disposicion,
                'costo_perdido' => $costoPerdido,
                'notas' => $data['notas'] ?? null,
            ]);

            $loteBloqueado->update(['estado_calidad' => $estadoCalidad]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Calidad',
                'Inspección de Lote',
                "Lote {$loteBloqueado->codigo_lote}: {$inspeccionadas} inspeccionadas, {$noConformes} no conformes, disposición {$disposicion} (Costo perdido: C$ {$costoPerdido}).",
                $ip
            );

            return $control;
        });
    }
}
