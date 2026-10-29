<?php

namespace App\Services\Compras;

use App\Models\Compras\OrdenCompra;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Compras\ComprasRepository;
use App\Repositories\Finanzas\FinanzasRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Services\Finanzas\ContabilidadService;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ComprasService
{
    public function __construct(
        protected ComprasRepository $comprasRepository,
        protected CatalogosRepository $catalogosRepository,
        protected InventarioRepository $inventarioRepository,
        protected FinanzasRepository $finanzasRepository,
        protected SeguridadService $seguridadService,
        protected ContabilidadService $contabilidadService
    ) {}

    /**
     * Paso 1: registra la orden de compra al proveedor (1 o múltiples líneas). No mueve stock ni finanzas.
     */
    public function registrarOrden(array $data, int $actorId, ?string $ip): OrdenCompra
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $lineasInput = $data['lineas'] ?? [[
                'insumo_id' => $data['insumo_id'],
                'cantidad' => $data['cantidad'],
                'costo_unitario' => $data['costo_unitario'],
            ]];

            $lineasPreparadas = [];
            $nombresInsumos = [];
            $subtotalOrden = 0.0;

            foreach ($lineasInput as $item) {
                $insumo = $this->catalogosRepository->findInsumoForUpdate((int) $item['insumo_id']);
                if (! $insumo) {
                    throw ValidationException::withMessages(['insumo_id' => 'El insumo seleccionado no existe.']);
                }

                $subLinea = round((float) $item['cantidad'] * (float) $item['costo_unitario'], 2);
                $subtotalOrden += $subLinea;
                $nombresInsumos[] = $insumo->nombre;

                $lineasPreparadas[] = [
                    'insumo_id' => $insumo->id,
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo_unitario'],
                    'subtotal' => $subLinea,
                ];
            }

            $subtotalOrden = round($subtotalOrden, 2);
            $impuestos = ! empty($data['aplicar_iva']) ? round($subtotalOrden * 0.15, 2) : 0.00;
            $total = round($subtotalOrden + $impuestos, 2);
            $folio = $this->comprasRepository->getNextFolio();

            $orden = $this->comprasRepository->createOrden(
                [
                    'folio' => $folio,
                    'proveedor_id' => $data['proveedor_id'],
                    'user_id' => $actorId,
                    'fecha_emision' => now()->toDateString(),
                    'fecha_recepcion' => null,
                    'estado' => 'Pendiente',
                    'subtotal' => $subtotalOrden,
                    'impuestos' => $impuestos,
                    'total' => $total,
                    'notas' => $data['notas'] ?? 'Orden pendiente de recepción.',
                ],
                $lineasPreparadas
            );

            $resumenInsumos = implode(', ', $nombresInsumos);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Compras',
                'Alta de Orden de Compra',
                "Orden {$folio} creada por C$ {$total} ({$resumenInsumos}). Pendiente de recepción.",
                $ip
            );

            return $orden;
        });
    }

    /**
     * Paso 2: recibe en almacén una orden pendiente. Incrementa stock y recalcula costo promedio
     * de todas sus líneas, registra Kardex, movimiento financiero y asiento contable de partida doble.
     */
    public function recibirOrden(OrdenCompra $orden, array $data, int $actorId, ?string $ip): OrdenCompra
    {
        return DB::transaction(function () use ($orden, $data, $actorId, $ip) {
            $ordenBloqueada = $this->comprasRepository->findOrdenForUpdate($orden->id);

            if ($ordenBloqueada->estado !== 'Pendiente') {
                throw ValidationException::withMessages([
                    'orden' => "La orden {$ordenBloqueada->folio} ya fue {$ordenBloqueada->estado}.",
                ]);
            }

            $lineas = $ordenBloqueada->lineas()->get();
            if ($lineas->isEmpty()) {
                throw ValidationException::withMessages(['orden' => 'La orden no contiene líneas de insumos.']);
            }

            $folio = $ordenBloqueada->folio;
            $nombresRecibidos = [];

            foreach ($lineas as $linea) {
                $insumo = $this->catalogosRepository->findInsumoForUpdate((int) $linea->insumo_id);

                if (! $insumo) {
                    throw ValidationException::withMessages(['orden' => 'El insumo de la orden ya no existe.']);
                }

                $cantidad = (float) $linea->cantidad;
                $costoUnitario = (float) $linea->costo_unitario;
                $subtotalLinea = (float) $linea->subtotal;

                $nuevoStock = round((float) $insumo->stock_actual + $cantidad, 3);
                $valorActual = (float) $insumo->stock_actual * (float) $insumo->costo_promedio;
                $nuevoCostoPromedio = $nuevoStock > 0
                    ? round(($valorActual + $subtotalLinea) / $nuevoStock, 2)
                    : $costoUnitario;

                $insumo->update([
                    'stock_actual' => $nuevoStock,
                    'costo_promedio' => $nuevoCostoPromedio,
                ]);

                $this->inventarioRepository->registrarMovimiento([
                    'fecha' => now(),
                    'tipo_item' => 'insumo',
                    'item_id' => $insumo->id,
                    'nombre_item' => $insumo->nombre,
                    'tipo_movimiento' => 'Entrada por Compra',
                    'referencia' => $folio,
                    'cantidad' => $cantidad,
                    'stock_resultante' => $nuevoStock,
                    'costo_unitario' => $costoUnitario,
                    'user_id' => $actorId,
                ]);

                $nombresRecibidos[] = $insumo->nombre;
            }

            $ordenBloqueada->update([
                'estado' => 'Recibida',
                'fecha_recepcion' => now()->toDateString(),
                'notas' => $this->notasRecepcion($ordenBloqueada->notas, $data['notas'] ?? null),
            ]);

            $modalidad = $data['modalidad_pago'] ?? 'Pagado';
            $totalOrden = (float) $ordenBloqueada->total;
            $resumenNombres = implode(', ', $nombresRecibidos);

            $this->finanzasRepository->createMovimiento([
                'folio' => $this->finanzasRepository->getNextFolio(),
                'fecha' => now()->toDateString(),
                'tipo' => $modalidad === 'Pagado' ? 'Egreso' : 'Cuenta por Pagar',
                'categoria' => 'Compra de Insumos',
                'concepto' => "Recepción {$folio} ({$resumenNombres})",
                'referencia' => $folio,
                'monto' => $totalOrden,
                'estado' => $modalidad,
                'fecha_vencimiento' => $modalidad === 'Pendiente' ? now()->addDays(15)->toDateString() : null,
                'user_id' => $actorId,
            ]);

            $this->contabilidadService->asentarCompraRecibida(
                $folio,
                (float) $ordenBloqueada->subtotal,
                (float) $ordenBloqueada->impuestos,
                $totalOrden,
                $modalidad,
                $actorId
            );

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Compras',
                'Recepción de Orden de Compra',
                "Orden {$folio} recibida en almacén por C$ {$totalOrden} ({$resumenNombres}, {$modalidad}).",
                $ip
            );

            return $ordenBloqueada->fresh(['proveedor', 'usuario', 'lineas.insumo']);
        });
    }

    private function notasRecepcion(?string $notasOrden, ?string $notasRecepcion): string
    {
        $base = $notasOrden ?? '';

        if ($notasRecepcion === null || $notasRecepcion === '') {
            return $base;
        }

        return trim($base.' | Recepción: '.$notasRecepcion);
    }
}
