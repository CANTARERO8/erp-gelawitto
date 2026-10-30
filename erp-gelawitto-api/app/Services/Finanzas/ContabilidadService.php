<?php

namespace App\Services\Finanzas;

use App\Models\Finanzas\AsientoContable;
use App\Models\Finanzas\CuentaContable;
use App\Repositories\Configuracion\ParametrosRepository;
use Illuminate\Support\Facades\Schema;

class ContabilidadService
{
    /**
     * @param  array<int, array{codigo: string, descripcion: string, debe: float, haber: float}>  $partidas
     */
    public function registrarAsiento(
        string $moduloOrigen,
        ?string $referencia,
        string $concepto,
        array $partidas,
        ?int $userId = null
    ): ?AsientoContable {
        if (! Schema::hasTable('asientos_contables')) {
            return null;
        }

        $partidasLimpias = array_values(array_filter($partidas, fn ($p) => round((float) $p['debe'], 2) > 0 || round((float) $p['haber'], 2) > 0));
        if (count($partidasLimpias) === 0) {
            return null;
        }

        $totalDebe = round(array_sum(array_map(fn ($p) => (float) $p['debe'], $partidasLimpias)), 2);
        $totalHaber = round(array_sum(array_map(fn ($p) => (float) $p['haber'], $partidasLimpias)), 2);

        $numeroAsiento = ParametrosRepository::siguienteFolio('folio_asientos_seq', 'ASI-2026-%04d', 'asientos_contables', 'numero_asiento');

        $asiento = AsientoContable::create([
            'numero_asiento' => $numeroAsiento,
            'fecha' => now()->toDateString(),
            'modulo_origen' => $moduloOrigen,
            'referencia' => $referencia,
            'concepto' => $concepto,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'user_id' => $userId,
        ]);

        foreach ($partidasLimpias as $p) {
            $cuenta = CuentaContable::where('codigo', $p['codigo'])->firstOrFail();
            $asiento->partidas()->create([
                'cuenta_contable_id' => $cuenta->id,
                'descripcion' => $p['descripcion'],
                'debe' => round((float) $p['debe'], 2),
                'haber' => round((float) $p['haber'], 2),
            ]);
        }

        return $asiento->load('partidas.cuenta');
    }

    public function asentarCompraRecibida(
        string $folio,
        float $subtotal,
        float $impuestos,
        float $total,
        string $modalidadPago,
        ?int $userId = null
    ): ?AsientoContable {
        $cuentaAbono = $modalidadPago === 'Pagado' ? '1101' : '2101';
        $descAbono = $modalidadPago === 'Pagado' ? "Pago de contado OC {$folio}" : "Cuenta por pagar proveedor OC {$folio}";

        $partidas = [
            [
                'codigo' => '1104',
                'descripcion' => "Ingreso a almacén de materia prima ({$folio})",
                'debe' => $subtotal,
                'haber' => 0,
            ],
        ];

        if ($impuestos > 0) {
            $partidas[] = [
                'codigo' => '1103',
                'descripcion' => "IVA acreditable 15% ({$folio})",
                'debe' => $impuestos,
                'haber' => 0,
            ];
        }

        $partidas[] = [
            'codigo' => $cuentaAbono,
            'descripcion' => $descAbono,
            'debe' => 0,
            'haber' => $total,
        ];

        return $this->registrarAsiento(
            'Compras',
            $folio,
            "Recepción de orden de compra {$folio} ({$modalidadPago})",
            $partidas,
            $userId
        );
    }

    public function asentarLoteProduccion(
        string $codigoLote,
        float $costoTotalLote,
        string $productoNombre,
        ?int $userId = null
    ): ?AsientoContable {
        if ($costoTotalLote <= 0) {
            return null;
        }

        return $this->registrarAsiento(
            'Producción',
            $codigoLote,
            "Transformación de insumos a producto terminado ({$codigoLote} - {$productoNombre})",
            [
                [
                    'codigo' => '1105',
                    'descripcion' => "Alta de producto terminado {$productoNombre}",
                    'debe' => $costoTotalLote,
                    'haber' => 0,
                ],
                [
                    'codigo' => '1104',
                    'descripcion' => "Consumo de materia prima en lote {$codigoLote}",
                    'debe' => 0,
                    'haber' => $costoTotalLote,
                ],
            ],
            $userId
        );
    }

    public function asentarBajaCalidadOMerma(
        string $referencia,
        string $tipoItem,
        float $costoPerdido,
        string $concepto,
        ?int $userId = null
    ): ?AsientoContable {
        if ($costoPerdido <= 0) {
            return null;
        }

        $cuentaInventario = $tipoItem === 'producto' ? '1105' : '1104';

        return $this->registrarAsiento(
            'Calidad / Inventario',
            $referencia,
            $concepto,
            [
                [
                    'codigo' => '5202',
                    'descripcion' => $concepto,
                    'debe' => $costoPerdido,
                    'haber' => 0,
                ],
                [
                    'codigo' => $cuentaInventario,
                    'descripcion' => "Baja de inventario ({$referencia})",
                    'debe' => 0,
                    'haber' => $costoPerdido,
                ],
            ],
            $userId
        );
    }

    public function asentarVenta(
        string $folio,
        float $subtotal,
        float $impuestos,
        float $total,
        float $costoTotalVenta,
        string $metodoPago,
        ?int $userId = null
    ): ?AsientoContable {
        $cuentaCargo = $metodoPago === 'Crédito' ? '1102' : '1101';
        $descCargo = $metodoPago === 'Crédito'
            ? "Cuenta por cobrar cliente mayorista ({$folio})"
            : "Cobro en {$metodoPago} ({$folio})";

        $partidas = [
            [
                'codigo' => $cuentaCargo,
                'descripcion' => $descCargo,
                'debe' => $total,
                'haber' => 0,
            ],
            [
                'codigo' => '4101',
                'descripcion' => "Ingreso por venta {$folio}",
                'debe' => 0,
                'haber' => $subtotal,
            ],
        ];

        if ($impuestos > 0) {
            $partidas[] = [
                'codigo' => '2102',
                'descripcion' => "IVA trasladado 15% ({$folio})",
                'debe' => 0,
                'haber' => $impuestos,
            ];
        }

        if ($costoTotalVenta > 0) {
            $partidas[] = [
                'codigo' => '5101',
                'descripcion' => "Reconocimiento de costo de lo vendido ({$folio})",
                'debe' => $costoTotalVenta,
                'haber' => 0,
            ];
            $partidas[] = [
                'codigo' => '1105',
                'descripcion' => "Descargo de inventario de helados ({$folio})",
                'debe' => 0,
                'haber' => $costoTotalVenta,
            ];
        }

        return $this->registrarAsiento(
            'Ventas',
            $folio,
            "Póliza de venta {$folio} ({$metodoPago})",
            $partidas,
            $userId
        );
    }

    public function asentarGastoOperativo(
        string $folio,
        string $concepto,
        float $monto,
        string $estado,
        ?int $userId = null
    ): ?AsientoContable {
        $cuentaAbono = $estado === 'Pagado' ? '1101' : '2101';

        return $this->registrarAsiento(
            'Finanzas',
            $folio,
            "Gasto operativo: {$concepto}",
            [
                [
                    'codigo' => '5201',
                    'descripcion' => $concepto,
                    'debe' => $monto,
                    'haber' => 0,
                ],
                [
                    'codigo' => $cuentaAbono,
                    'descripcion' => $estado === 'Pagado' ? "Pago directo {$folio}" : "Pasivo devengado {$folio}",
                    'debe' => 0,
                    'haber' => $monto,
                ],
            ],
            $userId
        );
    }

    public function asentarLiquidacionCuenta(
        string $folio,
        string $tipoOriginal,
        float $monto,
        ?int $userId = null
    ): ?AsientoContable {
        if ($tipoOriginal === 'Cuenta por Cobrar') {
            return $this->registrarAsiento(
                'Finanzas',
                $folio,
                "Cobro de factura a crédito {$folio}",
                [
                    [
                        'codigo' => '1101',
                        'descripcion' => "Ingreso a bancos por cobro {$folio}",
                        'debe' => $monto,
                        'haber' => 0,
                    ],
                    [
                        'codigo' => '1102',
                        'descripcion' => "Cancelación de cuenta por cobrar {$folio}",
                        'debe' => 0,
                        'haber' => $monto,
                    ],
                ],
                $userId
            );
        }

        return $this->registrarAsiento(
            'Finanzas',
            $folio,
            "Liquidación de cuenta por pagar {$folio}",
            [
                [
                    'codigo' => '2101',
                    'descripcion' => "Cancelación de pasivo proveedor {$folio}",
                    'debe' => $monto,
                    'haber' => 0,
                ],
                [
                    'codigo' => '1101',
                    'descripcion' => "Egreso de caja/bancos {$folio}",
                    'debe' => 0,
                    'haber' => $monto,
                ],
            ],
            $userId
        );
    }

    public function obtenerEstadosFinancieros(): array
    {
        if (! Schema::hasTable('cuentas_contables')) {
            return [
                'asientos' => [],
                'balanza' => [],
                'estado_resultados' => [
                    'ingresos_ventas' => 0,
                    'costo_ventas' => 0,
                    'utilidad_bruta' => 0,
                    'gastos_operativos' => 0,
                    'mermas_scrap' => 0,
                    'utilidad_neta' => 0,
                ],
                'balance_general' => [
                    'total_activos' => 0,
                    'total_pasivos' => 0,
                    'capital_patrimonio' => 0,
                    'utilidad_ejercicio' => 0,
                    'total_pasivo_mas_patrimonio' => 0,
                ],
            ];
        }

        $asientos = AsientoContable::with(['partidas.cuenta', 'usuario'])
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (AsientoContable $a) => [
                'id' => $a->id,
                'numero_asiento' => $a->numero_asiento,
                'fecha' => $a->fecha->format('Y-m-d'),
                'modulo_origen' => $a->modulo_origen,
                'referencia' => $a->referencia ?? '—',
                'concepto' => $a->concepto,
                'total_debe' => (string) $a->total_debe,
                'total_haber' => (string) $a->total_haber,
                'responsable' => $a->usuario?->name ?? 'Sistema',
                'partidas' => $a->partidas->map(fn ($p) => [
                    'codigo' => $p->cuenta?->codigo ?? '',
                    'cuenta' => $p->cuenta?->nombre ?? '',
                    'descripcion' => $p->descripcion ?? '',
                    'debe' => (string) $p->debe,
                    'haber' => (string) $p->haber,
                ])->values()->all(),
            ])->values()->all();

        $cuentas = CuentaContable::with('partidas')->orderBy('codigo')->get();

        $saldosPorCodigo = [];
        $balanza = [];

        foreach ($cuentas as $c) {
            $debe = round((float) $c->partidas->sum('debe'), 2);
            $haber = round((float) $c->partidas->sum('haber'), 2);
            $saldo = $c->naturaleza === 'Deudora'
                ? round($debe - $haber, 2)
                : round($haber - $debe, 2);

            $saldosPorCodigo[$c->codigo] = $saldo;

            $balanza[] = [
                'codigo' => $c->codigo,
                'nombre' => $c->nombre,
                'tipo' => $c->tipo,
                'naturaleza' => $c->naturaleza,
                'debe' => $debe,
                'haber' => $haber,
                'saldo' => $saldo,
            ];
        }

        $ingresosVentas = $saldosPorCodigo['4101'] ?? 0.0;
        $costoVentas = $saldosPorCodigo['5101'] ?? 0.0;
        $utilidadBruta = round($ingresosVentas - $costoVentas, 2);
        $gastosOperativos = $saldosPorCodigo['5201'] ?? 0.0;
        $mermasScrap = $saldosPorCodigo['5202'] ?? 0.0;
        $utilidadNeta = round($utilidadBruta - $gastosOperativos - $mermasScrap, 2);

        $totalActivos = round(
            ($saldosPorCodigo['1101'] ?? 0) +
            ($saldosPorCodigo['1102'] ?? 0) +
            ($saldosPorCodigo['1103'] ?? 0) +
            ($saldosPorCodigo['1104'] ?? 0) +
            ($saldosPorCodigo['1105'] ?? 0),
            2
        );

        $totalPasivos = round(
            ($saldosPorCodigo['2101'] ?? 0) +
            ($saldosPorCodigo['2102'] ?? 0),
            2
        );

        $capitalPatrimonio = round($saldosPorCodigo['3101'] ?? 0, 2);

        return [
            'asientos' => $asientos,
            'balanza' => $balanza,
            'estado_resultados' => [
                'ingresos_ventas' => $ingresosVentas,
                'costo_ventas' => $costoVentas,
                'utilidad_bruta' => $utilidadBruta,
                'gastos_operativos' => $gastosOperativos,
                'mermas_scrap' => $mermasScrap,
                'utilidad_neta' => $utilidadNeta,
            ],
            'balance_general' => [
                'total_activos' => $totalActivos,
                'total_pasivos' => $totalPasivos,
                'capital_patrimonio' => $capitalPatrimonio,
                'utilidad_ejercicio' => $utilidadNeta,
                'total_pasivo_mas_patrimonio' => round($totalPasivos + $capitalPatrimonio + $utilidadNeta, 2),
            ],
        ];
    }
}
