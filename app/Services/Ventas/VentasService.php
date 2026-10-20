<?php

namespace App\Services\Ventas;

use App\Models\Ventas\CajaTurno;
use App\Models\Ventas\Venta;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Finanzas\FinanzasRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Repositories\Ventas\VentasRepository;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentasService
{
    public function __construct(
        protected VentasRepository $ventasRepository,
        protected CatalogosRepository $catalogosRepository,
        protected InventarioRepository $inventarioRepository,
        protected FinanzasRepository $finanzasRepository,
        protected SeguridadService $seguridadService
    ) {}

    public function registrarVenta(array $data, int $actorId, ?string $ip): Venta
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $turno = $this->ventasRepository->getTurnoActivoForUpdate();
            if (! $turno) {
                throw ValidationException::withMessages([
                    'producto_id' => 'Debe abrir un turno de caja antes de registrar ventas.',
                ]);
            }

            $producto = $this->catalogosRepository->findProductoForUpdate((int) $data['producto_id']);
            if (! $producto) {
                throw ValidationException::withMessages([
                    'producto_id' => 'El producto seleccionado no existe.',
                ]);
            }

            if ((float) $producto->stock_actual < (float) $data['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente de {$producto->nombre}. Disponible: {$producto->stock_actual}.",
                ]);
            }

            $folio = $this->ventasRepository->getNextFolioVenta();
            $subtotal = round((float) $data['cantidad'] * (float) $producto->precio_venta, 2);

            $ventaData = [
                'folio' => $folio,
                'caja_turno_id' => $turno->id,
                'cliente_id' => $data['cliente_id'],
                'user_id' => $actorId,
                'fecha_venta' => now(),
                'metodo_pago' => $data['metodo_pago'],
                'subtotal' => $subtotal,
                'impuestos' => 0,
                'total' => $subtotal,
                'estado' => 'Completada',
            ];

            $lineaData = [
                'producto_id' => $producto->id,
                'cantidad' => $data['cantidad'],
                'precio_unitario' => $producto->precio_venta,
                'costo_unitario' => $producto->costo_unitario,
                'subtotal' => $subtotal,
            ];

            $venta = $this->ventasRepository->createVenta($ventaData, $lineaData);

            $nuevoStock = round((float) $producto->stock_actual - (float) $data['cantidad'], 3);
            $producto->update(['stock_actual' => $nuevoStock]);

            $this->inventarioRepository->registrarMovimiento([
                'fecha' => now(),
                'tipo_item' => 'producto',
                'item_id' => $producto->id,
                'nombre_item' => $producto->nombre,
                'tipo_movimiento' => 'Salida por Venta',
                'referencia' => $folio,
                'cantidad' => -1 * (float) $data['cantidad'],
                'stock_resultante' => $nuevoStock,
                'costo_unitario' => $producto->costo_unitario,
                'user_id' => $actorId,
            ]);

            $efectivo = (float) $turno->ventas_efectivo + ($data['metodo_pago'] === 'Efectivo' ? $subtotal : 0);
            $tarjeta = (float) $turno->ventas_tarjeta + ($data['metodo_pago'] === 'Tarjeta' ? $subtotal : 0);
            $transferencia = (float) $turno->ventas_transferencia + ($data['metodo_pago'] === 'Transferencia' ? $subtotal : 0);
            $esperado = round((float) $turno->monto_inicial + $efectivo, 2);

            $turno->update([
                'ventas_efectivo' => round($efectivo, 2),
                'ventas_tarjeta' => round($tarjeta, 2),
                'ventas_transferencia' => round($transferencia, 2),
                'monto_esperado' => $esperado,
            ]);

            $this->finanzasRepository->createMovimiento([
                'folio' => $this->finanzasRepository->getNextFolio(),
                'fecha' => now()->toDateString(),
                'tipo' => 'Ingreso',
                'categoria' => 'Ventas Mostrador',
                'concepto' => "Venta {$folio} ({$producto->nombre} x {$data['cantidad']})",
                'referencia' => $folio,
                'monto' => $subtotal,
                'estado' => 'Pagado',
                'fecha_vencimiento' => null,
                'user_id' => $actorId,
            ]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Ventas y Caja',
                'Registro de Venta',
                "Venta {$folio} por \${$subtotal} en {$data['metodo_pago']}.",
                $ip
            );

            return $venta;
        });
    }

    public function abrirTurno(array $data, int $actorId, ?string $ip): CajaTurno
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $turnoExistente = $this->ventasRepository->getTurnoActivo();
            if ($turnoExistente) {
                throw ValidationException::withMessages([
                    'monto_inicial' => "Ya existe un turno abierto ({$turnoExistente->codigo_turno}). Debe cerrarlo primero.",
                ]);
            }

            $codigo = $this->ventasRepository->getNextCodigoTurno();

            $turno = $this->ventasRepository->createTurno([
                'codigo_turno' => $codigo,
                'user_id' => $actorId,
                'fecha_apertura' => now(),
                'monto_inicial' => $data['monto_inicial'],
                'monto_esperado' => $data['monto_inicial'],
                'estado' => 'Abierta',
                'observaciones' => $data['observaciones'] ?? 'Apertura regular de turno.',
            ]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Ventas y Caja',
                'Apertura de Caja',
                "Turno {$codigo} abierto con \${$data['monto_inicial']}.",
                $ip
            );

            return $turno;
        });
    }

    public function cerrarTurno(array $data, int $actorId, ?string $ip): CajaTurno
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $turno = $this->ventasRepository->getTurnoActivoForUpdate();
            if (! $turno) {
                throw ValidationException::withMessages([
                    'monto_declarado' => 'No hay ningún turno abierto actualmente.',
                ]);
            }

            $diferencia = round((float) $data['monto_declarado'] - (float) $turno->monto_esperado, 2);

            $turno->update([
                'fecha_cierre' => now(),
                'monto_declarado' => $data['monto_declarado'],
                'diferencia' => $diferencia,
                'estado' => 'Cerrada',
                'observaciones' => $data['observaciones'] ?? $turno->observaciones,
            ]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Ventas y Caja',
                'Corte de Caja',
                "Cierre de turno {$turno->codigo_turno}. Esperado: \${$turno->monto_esperado}, Declarado: \${$data['monto_declarado']}, Diferencia: \${$diferencia}.",
                $ip
            );

            return $turno;
        });
    }
}
