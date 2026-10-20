<?php

namespace App\Services\Finanzas;

use App\Models\Finanzas\FinanzasMovimiento;
use App\Repositories\Finanzas\FinanzasRepository;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanzasService
{
    public function __construct(
        protected FinanzasRepository $finanzasRepository,
        protected SeguridadService $seguridadService
    ) {}

    public function registrarGasto(array $data, int $actorId, ?string $ip): FinanzasMovimiento
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $folio = $this->finanzasRepository->getNextFolio();

            $movimiento = $this->finanzasRepository->createMovimiento([
                'folio' => $folio,
                'fecha' => now()->toDateString(),
                'tipo' => $data['estado'] === 'Pagado' ? 'Egreso' : 'Cuenta por Pagar',
                'categoria' => $data['categoria'],
                'concepto' => $data['concepto'],
                'referencia' => 'GASTO-OP',
                'monto' => $data['monto'],
                'estado' => $data['estado'],
                'fecha_vencimiento' => $data['estado'] === 'Pendiente' ? now()->addDays(15)->toDateString() : null,
                'user_id' => $actorId,
            ]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Finanzas',
                'Registro de Egreso',
                "Folio {$folio}: {$data['concepto']} por \${$data['monto']}.",
                $ip
            );

            return $movimiento;
        });
    }

    public function liquidarCuenta(int $id, int $actorId, ?string $ip): FinanzasMovimiento
    {
        return DB::transaction(function () use ($id, $actorId, $ip) {
            $mov = $this->finanzasRepository->findMovimientoForUpdate($id);

            if (! $mov) {
                throw ValidationException::withMessages(['id' => 'El movimiento financiero no existe.']);
            }

            if ($mov->estado !== 'Pendiente') {
                throw ValidationException::withMessages(['id' => 'El movimiento ya no se encuentra pendiente de liquidación.']);
            }

            $mov->update([
                'tipo' => 'Egreso',
                'estado' => 'Pagado',
            ]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Finanzas',
                'Liquidación de Cuenta',
                "Se liquidó la cuenta por pagar {$mov->folio} (\${$mov->monto}).",
                $ip
            );

            return $mov;
        });
    }
}
