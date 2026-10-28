<?php

namespace App\Services\Inventario;

use App\Models\Inventario\MovimientoInventario;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Services\Finanzas\ContabilidadService;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioService
{
    public function __construct(
        protected InventarioRepository $inventarioRepository,
        protected CatalogosRepository $catalogosRepository,
        protected SeguridadService $seguridadService,
        protected ?ContabilidadService $contabilidadService = null
    ) {
        $this->contabilidadService ??= app(ContabilidadService::class);
    }

    public function registrarAjuste(array $data, int $actorId, ?string $ip): MovimientoInventario
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $esInsumo = $data['tipo_item'] === 'insumo';
            $item = $esInsumo
                ? $this->catalogosRepository->findInsumoForUpdate((int) $data['item_id'])
                : $this->catalogosRepository->findProductoForUpdate((int) $data['item_id']);

            if (! $item) {
                throw ValidationException::withMessages(['item_id' => 'El artículo seleccionado no existe.']);
            }

            $esSalida = in_array($data['tipo_movimiento'], ['Merma', 'Ajuste Negativo'], true);
            $delta = $esSalida ? -1 * (float) $data['cantidad'] : (float) $data['cantidad'];
            $nuevoStock = round((float) $item->stock_actual + $delta, 3);

            if ($nuevoStock < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente en {$item->nombre}. Disponible: {$item->stock_actual}.",
                ]);
            }

            $item->update(['stock_actual' => $nuevoStock]);

            $costo = $esInsumo ? $item->costo_promedio : $item->costo_unitario;
            $ref = 'AJU-'.now()->format('His');

            $movimiento = $this->inventarioRepository->registrarMovimiento([
                'fecha' => now(),
                'tipo_item' => $data['tipo_item'],
                'item_id' => $item->id,
                'nombre_item' => $item->nombre,
                'tipo_movimiento' => $data['tipo_movimiento'],
                'referencia' => $ref,
                'cantidad' => $delta,
                'stock_resultante' => $nuevoStock,
                'costo_unitario' => $costo,
                'user_id' => $actorId,
            ]);

            if ($esSalida) {
                $this->contabilidadService->asentarBajaCalidadOMerma(
                    $ref,
                    $data['tipo_item'],
                    round(abs($delta) * (float) $costo, 2),
                    "{$data['tipo_movimiento']} de inventario: {$data['motivo']}",
                    $actorId
                );
            }

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Inventario',
                $data['tipo_movimiento'],
                "{$data['tipo_movimiento']} sobre {$item->nombre} ({$delta}): {$data['motivo']}.",
                $ip
            );

            return $movimiento;
        });
    }
}
