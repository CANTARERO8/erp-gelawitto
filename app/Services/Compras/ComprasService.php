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

    public function registrarCompra(array $data, int $actorId, ?string $ip): OrdenCompra
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $insumo = $this->catalogosRepository->findInsumoForUpdate((int) $data['insumo_id']);

            if (! $insumo) {
                throw ValidationException::withMessages(['insumo_id' => 'El insumo seleccionado no existe.']);
            }

            $folio = $this->comprasRepository->getNextFolio();
            $subtotal = round((float) $data['cantidad'] * (float) $data['costo_unitario'], 2);

            $ordenData = [
                'folio' => $folio,
                'proveedor_id' => $data['proveedor_id'],
                'user_id' => $actorId,
                'fecha_emision' => now()->toDateString(),
                'fecha_recepcion' => now()->toDateString(),
                'estado' => 'Recibida',
                'subtotal' => $subtotal,
                'impuestos' => 0,
                'total' => $subtotal,
                'notas' => $data['notas'] ?? 'Recepción directa a almacén.',
            ];

            $lineaData = [
                'insumo_id' => $insumo->id,
                'cantidad' => $data['cantidad'],
                'costo_unitario' => $data['costo_unitario'],
                'subtotal' => $subtotal,
            ];

            $orden = $this->comprasRepository->createOrden($ordenData, $lineaData);

            $nuevoStock = round((float) $insumo->stock_actual + (float) $data['cantidad'], 3);
            $valorActual = (float) $insumo->stock_actual * (float) $insumo->costo_promedio;
            $nuevoCostoPromedio = $nuevoStock > 0
                ? round(($valorActual + $subtotal) / $nuevoStock, 2)
                : (float) $data['costo_unitario'];

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
                'cantidad' => $data['cantidad'],
                'stock_resultante' => $nuevoStock,
                'costo_unitario' => $data['costo_unitario'],
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

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Compras',
                'Recepción de Orden',
                "Orden {$folio} ingresada por \${$subtotal} de {$insumo->nombre}.",
                $ip
            );

            return $orden;
        });
    }
}
