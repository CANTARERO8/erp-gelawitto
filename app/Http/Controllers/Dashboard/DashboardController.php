<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inventario\KardexResource;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Repositories\Finanzas\FinanzasRepository;
use App\Repositories\Inventario\InventarioRepository;
use App\Repositories\Ventas\VentasRepository;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected VentasRepository $ventasRepository,
        protected InventarioRepository $inventarioRepository,
        protected FinanzasRepository $finanzasRepository,
        protected CatalogosRepository $catalogosRepository
    ) {}

    public function index(): Response
    {
        $ventasHoy = $this->ventasRepository->getVentasHoyTotal();
        $ventasTotales = $this->ventasRepository->getVentasAcumuladasTotal();
        $valorInventarioInsumos = $this->inventarioRepository->getValorInventarioInsumos();
        $valorInventarioProductos = $this->inventarioRepository->getValorInventarioProductos();
        $cuentasPorPagar = $this->finanzasRepository->getCuentasPorPagarTotal();

        $insumosCriticos = $this->inventarioRepository->getInsumosCriticos()->map(function ($insumo) {
            return [
                'id' => $insumo->id,
                'codigo' => $insumo->codigo,
                'nombre' => $insumo->nombre,
                'stock_actual' => (string) $insumo->stock_actual,
                'stock_minimo' => (string) $insumo->stock_minimo,
                'unidad' => $insumo->unidad?->abreviatura ?? '',
            ];
        });

        $ultimosMovimientos = KardexResource::collection($this->inventarioRepository->getUltimosMovimientos(6))->resolve();

        $productosRentabilidad = $this->catalogosRepository->getProductos()->map(function ($producto) {
            $precio = (float) $producto->precio_venta;
            $costo = (float) $producto->costo_unitario;
            $margen = $precio > 0 ? round((($precio - $costo) / $precio) * 100, 1) : 0;

            return [
                'id' => $producto->id,
                'sku' => $producto->sku,
                'nombre' => $producto->nombre,
                'sabor' => $producto->sabor,
                'presentacion' => $producto->presentacion,
                'precio_venta' => (string) $producto->precio_venta,
                'costo_unitario' => (string) $producto->costo_unitario,
                'stock_actual' => (string) $producto->stock_actual,
                'margen_porcentaje' => (string) $margen,
            ];
        })->sortByDesc('stock_actual')->values();

        return Inertia::render('Erp/Dashboard', [
            'kpis' => [
                'ventas_hoy' => round($ventasHoy, 2),
                'ventas_acumuladas' => round($ventasTotales, 2),
                'valor_inventario_insumos' => round($valorInventarioInsumos, 2),
                'valor_inventario_productos' => round($valorInventarioProductos, 2),
                'alertas_stock' => $insumosCriticos->count(),
                'cuentas_por_pagar' => round($cuentasPorPagar, 2),
            ],
            'insumosCriticos' => $insumosCriticos,
            'ultimosMovimientos' => $ultimosMovimientos,
            'productosRentabilidad' => $productosRentabilidad,
        ]);
    }
}
