<?php

namespace App\Services\Dashboard;

use App\Repositories\Dashboard\DashboardRepository;

class DashboardService
{
    public function __construct(protected DashboardRepository $dashboardRepository) {}

    public function getAnalisisRentabilidad(): array
    {
        $margenProductos = $this->dashboardRepository->getVentasPorProducto();
        $totalIngresoVentas = (float) array_sum(array_column($margenProductos, 'ingreso'));

        $acumulado = 0.0;
        foreach ($margenProductos as $m) {
            $m->utilidad = round($m->ingreso - $m->costo, 2);
            $m->margen = $m->ingreso > 0 ? round(($m->utilidad / $m->ingreso) * 100, 1) : 0.0;
            $acumulado += $m->ingreso;
            $m->acumulado = $totalIngresoVentas > 0 ? round(($acumulado / $totalIngresoVentas) * 100, 1) : 0.0;
        }

        $recetas = $this->dashboardRepository->getCosteoRecetas();
        foreach ($recetas as $r) {
            $r->costo = round($r->costo ?? 0, 2);
            $r->margen = $r->precio > 0 ? round((($r->precio - $r->costo) / $r->precio) * 100, 1) : 0.0;
        }

        $totalUtilidad = (float) array_sum(array_column($margenProductos, 'utilidad'));

        return [
            'ingreso_total' => $totalIngresoVentas,
            'utilidad_total' => $totalUtilidad,
            'margen_promedio' => $totalIngresoVentas > 0 ? round(($totalUtilidad / $totalIngresoVentas) * 100, 1) : 0.0,
            'margen_productos' => $margenProductos,
            'recetas' => $recetas,
        ];
    }

    public function getAnalisisCompras(): array
    {
        $plan = $this->dashboardRepository->getPlanInsumos();
        foreach ($plan as $c) {
            $dias = $c->consumo > 0 ? round($c->stock / $c->consumo, 1) : null;
            $c->dias = $dias;

            if ($c->minimo > 0 && $c->stock < $c->minimo) {
                $estado = 'Reordenar';
            } elseif ($dias !== null && $dias < 14) {
                $estado = 'Vigilar';
            } else {
                $estado = 'Suficiente';
            }
            $c->estado = $estado;

            $objetivo = max($c->minimo * 2, $c->consumo * 14);
            $sugerida = $estado !== 'Suficiente' ? round(max($objetivo - $c->stock, 0), 1) : 0.0;
            $c->sugerida = $sugerida;
            $c->valor = round($sugerida * $c->costo, 2);
            $c->proveedor = $c->proveedor ?: 'Sin proveedor';
        }

        return [
            'plan' => $plan,
            'historial_precios' => $this->dashboardRepository->getHistorialPrecios(),
            'insumos_reordenar' => count(array_filter($plan, fn ($p) => $p->estado === 'Reordenar')),
            'insumos_vigilar' => count(array_filter($plan, fn ($p) => $p->estado === 'Vigilar')),
            'valor_compra_sugerida' => round((float) array_sum(array_column($plan, 'valor')), 2),
        ];
    }
}
