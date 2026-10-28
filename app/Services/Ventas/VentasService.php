<?php

namespace App\Services\Ventas;

use App\Models\Ventas\CajaTurno;
use App\Models\Ventas\Venta;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Finanzas\FinanzasRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Repositories\Ventas\VentasRepository;
use App\Services\Finanzas\ContabilidadService;
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
        protected SeguridadService $seguridadService,
        protected ?ContabilidadService $contabilidadService = null
    ) {
        $this->contabilidadService ??= app(ContabilidadService::class);
    }

    public function registrarVenta(array $data, int $actorId, ?string $ip): Venta
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $turno = $this->ventasRepository->getTurnoActivoForUpdate();
            if (! $turno) {
                throw ValidationException::withMessages([
                    'producto_id' => 'Debe abrir un turno de caja antes de registrar ventas.',
                ]);
            }

            $lineasInput = ! empty($data['lineas'])
                ? $data['lineas']
                : [[
                    'producto_id' => $data['producto_id'],
                    'cantidad' => $data['cantidad'],
                ]];

            $lineasParaCrear = [];
            $productosBloqueados = [];
            $acumuladoSolicitado = [];
            $subtotal = 0.0;
            $costoTotalVenta = 0.0;
            $nombresResumen = [];

            foreach ($lineasInput as $item) {
                $pid = (int) $item['producto_id'];
                $cant = (float) $item['cantidad'];

                $producto = $productosBloqueados[$pid] ?? $this->catalogosRepository->findProductoForUpdate($pid);
                if (! $producto) {
                    throw ValidationException::withMessages([
                        'producto_id' => 'El producto seleccionado no existe.',
                    ]);
                }
                $productosBloqueados[$pid] = $producto;

                $acumuladoSolicitado[$pid] = ($acumuladoSolicitado[$pid] ?? 0.0) + $cant;
                $enCuarentena = $this->ventasRepository->getStockEnCuarentena($producto->id);
                $disponible = max(0, round((float) $producto->stock_actual - $enCuarentena, 3));

                if ($acumuladoSolicitado[$pid] > $disponible) {
                    $detalleCuarentena = $enCuarentena > 0
                        ? " ({$enCuarentena} uds retenidas en cuarentena de Calidad)"
                        : '';
                    throw ValidationException::withMessages([
                        'cantidad' => "Stock liberado insuficiente de {$producto->nombre}. Disponible para venta: {$disponible}{$detalleCuarentena}.",
                    ]);
                }

                $subLinea = round($cant * (float) $producto->precio_venta, 2);
                $subtotal += $subLinea;
                $costoTotalVenta += round($cant * (float) $producto->costo_unitario, 2);
                $nombresResumen[] = "{$producto->nombre} x {$cant}";

                $lineasParaCrear[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $cant,
                    'precio_unitario' => $producto->precio_venta,
                    'costo_unitario' => $producto->costo_unitario,
                    'subtotal' => $subLinea,
                ];
            }

            $subtotal = round($subtotal, 2);
            $costoTotalVenta = round($costoTotalVenta, 2);
            $aplicarIva = ! empty($data['aplicar_iva']);
            $impuestos = $aplicarIva ? round($subtotal * 0.15, 2) : 0.00;
            $total = round($subtotal + $impuestos, 2);

            $folio = $this->ventasRepository->getNextFolioVenta();

            $ventaData = [
                'folio' => $folio,
                'caja_turno_id' => $turno->id,
                'cliente_id' => $data['cliente_id'],
                'user_id' => $actorId,
                'fecha_venta' => now(),
                'metodo_pago' => $data['metodo_pago'],
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total,
                'estado' => 'Completada',
            ];

            $venta = $this->ventasRepository->createVenta($ventaData, $lineasParaCrear);

            foreach ($lineasParaCrear as $linea) {
                $producto = $productosBloqueados[$linea['producto_id']];
                $nuevoStock = round((float) $producto->stock_actual - (float) $linea['cantidad'], 3);
                $producto->update(['stock_actual' => $nuevoStock]);

                $this->inventarioRepository->registrarMovimiento([
                    'fecha' => now(),
                    'tipo_item' => 'producto',
                    'item_id' => $producto->id,
                    'nombre_item' => $producto->nombre,
                    'tipo_movimiento' => 'Salida por Venta',
                    'referencia' => $folio,
                    'cantidad' => -1 * (float) $linea['cantidad'],
                    'stock_resultante' => $nuevoStock,
                    'costo_unitario' => $producto->costo_unitario,
                    'user_id' => $actorId,
                ]);
            }

            $resumenConcepto = implode(', ', $nombresResumen);

            if ($data['metodo_pago'] === 'Crédito') {
                $this->finanzasRepository->createMovimiento([
                    'folio' => $this->finanzasRepository->getNextFolio(),
                    'fecha' => now()->toDateString(),
                    'tipo' => 'Cuenta por Cobrar',
                    'categoria' => 'Ventas a Crédito',
                    'concepto' => "Venta a crédito {$folio} ({$resumenConcepto})",
                    'referencia' => $folio,
                    'monto' => $total,
                    'estado' => 'Pendiente',
                    'fecha_vencimiento' => now()->addDays(15)->toDateString(),
                    'user_id' => $actorId,
                ]);
            } else {
                $efectivo = (float) $turno->ventas_efectivo + ($data['metodo_pago'] === 'Efectivo' ? $total : 0);
                $tarjeta = (float) $turno->ventas_tarjeta + ($data['metodo_pago'] === 'Tarjeta' ? $total : 0);
                $transferencia = (float) $turno->ventas_transferencia + ($data['metodo_pago'] === 'Transferencia' ? $total : 0);
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
                    'concepto' => "Venta {$folio} ({$resumenConcepto})",
                    'referencia' => $folio,
                    'monto' => $total,
                    'estado' => 'Pagado',
                    'fecha_vencimiento' => null,
                    'user_id' => $actorId,
                ]);
            }

            $this->contabilidadService->asentarVenta(
                $folio,
                $subtotal,
                $impuestos,
                $total,
                $costoTotalVenta,
                $data['metodo_pago'],
                $actorId
            );

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Ventas y Caja',
                'Registro de Venta',
                "Venta {$folio} por C$ {$total} en {$data['metodo_pago']}.",
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
                "Turno {$codigo} abierto con C$ {$data['monto_inicial']}.",
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
                "Cierre de turno {$turno->codigo_turno}. Esperado: C$ {$turno->monto_esperado}, Declarado: C$ {$data['monto_declarado']}, Diferencia: C$ {$diferencia}.",
                $ip
            );

            return $turno;
        });
    }
}
