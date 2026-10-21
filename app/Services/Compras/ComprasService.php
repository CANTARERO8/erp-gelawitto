<?php

namespace App\Services\Compras;

use App\Models\Compras\OrdenCompra;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Compras\ComprasRepository;
use App\Repositories\Finanzas\FinanzasRepository;
use App\Repositories\Inventario\InventarioRepository;
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
        protected SeguridadService $seguridadService
    ) {}

    /**
     * Paso 1: registra la orden de compra al proveedor. No mueve stock ni finanzas.
     */
    public function registrarOrden(array $data, int $actorId, ?string $ip): OrdenCompra
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $insumo = $this->catalogosRepository->findInsumoForUpdate((int) $data['insumo_id']);

            if (! $insumo) {
                throw ValidationException::withMessages(['insumo_id' => 'El insumo seleccionado no existe.']);
            }

            $folio = $this->comprasRepository->getNextFolio();
            $subtotal = round((float) $data['cantidad'] * (float) $data['costo_unitario'], 2);

            $orden = $this->comprasRepository->createOrden(
                [
                    'folio' => $folio,
                    'proveedor_id' => $data['proveedor_id'],
                    'user_id' => $actorId,
                    'fecha_emision' => now()->toDateString(),
                    'fecha_recepcion' => null,
                    'estado' => 'Pendiente',
                    'subtotal' => $subtotal,
                    'impuestos' => 0,
                    'total' => $subtotal,
                    'notas' => $data['notas'] ?? 'Orden pendiente de recepción.',
                ],
                [
                    'insumo_id' => $insumo->id,
                    'cantidad' => $data['cantidad'],
                    'costo_unitario' => $data['costo_unitario'],
                    'subtotal' => $subtotal,
                ]
            );

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Compras',
                'Alta de Orden de Compra',
                "Orden {$folio} creada por \${$subtotal} de {$insumo->nombre}. Pendiente de recepción.",
                $ip
            );

            return $orden;
        });
    }

    /**
     * Paso 2: recibe en almacén una orden pendiente. Aquí se incrementa el stock,
     * se recalcula el costo promedio, se registra el Kardex y se genera el movimiento financiero.
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

            $linea = $ordenBloqueada->lineas()->firstOrFail();
            $insumo = $this->catalogosRepository->findInsumoForUpdate((int) $linea->insumo_id);

            if (! $insumo) {
                throw ValidationException::withMessages(['orden' => 'El insumo de la orden ya no existe.']);
            }

            $folio = $ordenBloqueada->folio;
            $cantidad = (float) $linea->cantidad;
            $costoUnitario = (float) $linea->costo_unitario;
            $subtotal = (float) $linea->subtotal;

            $nuevoStock = round((float) $insumo->stock_actual + $cantidad, 3);
            $valorActual = (float) $insumo->stock_actual * (float) $insumo->costo_promedio;
            $nuevoCostoPromedio = $nuevoStock > 0
                ? round(($valorActual + $subtotal) / $nuevoStock, 2)
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

            $this->finanzasRepository->createMovimiento([
                'folio' => $this->finanzasRepository->getNextFolio(),
                'fecha' => now()->toDateString(),
                'tipo' => $data['modalidad_pago'] === 'Pagado' ? 'Egreso' : 'Cuenta por Pagar',
                'categoria' => 'Compra de Insumos',
                'concepto' => "Recepción de {$insumo->nombre} ({$folio})",
                'referencia' => $folio,
                'monto' => $subtotal,
                'estado' => $data['modalidad_pago'],
                'fecha_vencimiento' => $data['modalidad_pago'] === 'Pendiente' ? now()->addDays(15)->toDateString() : null,
                'user_id' => $actorId,
            ]);

            $ordenBloqueada->update([
                'estado' => 'Recibida',
                'fecha_recepcion' => now()->toDateString(),
                'notas' => $this->notasRecepcion($ordenBloqueada->notas, $data['notas'] ?? null),
            ]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Compras',
                'Recepción de Orden',
                "Orden {$folio} recibida en almacén por \${$subtotal} de {$insumo->nombre}.",
                $ip
            );

            return $ordenBloqueada->load(['proveedor', 'usuario', 'lineas.insumo']);
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
