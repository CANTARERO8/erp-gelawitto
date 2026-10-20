<?php

namespace App\Services\Catalogos;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Repositories\Catalogos\CatalogosRepository;
use App\Services\Seguridad\SeguridadService;
use Illuminate\Support\Facades\DB;

class CatalogosService
{
    public function __construct(
        protected CatalogosRepository $catalogosRepository,
        protected SeguridadService $seguridadService
    ) {}

    public function crearProducto(array $data, int $actorId, ?string $ip): Producto
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $producto = $this->catalogosRepository->createProducto([
                ...$data,
                'stock_actual' => 0,
                'activo' => true,
            ]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Catálogos',
                'Alta de Producto',
                "Se registró el producto {$producto->sku} ({$producto->nombre}).",
                $ip
            );

            return $producto;
        });
    }

    public function crearInsumo(array $data, int $actorId, ?string $ip): Insumo
    {
        return DB::transaction(function () use ($data, $actorId, $ip) {
            $insumo = $this->catalogosRepository->createInsumo([
                ...$data,
                'stock_actual' => 0,
                'activo' => true,
            ]);

            $this->seguridadService->registrarAuditoria(
                $actorId,
                'Catálogos',
                'Alta de Insumo',
                "Se registró el insumo {$insumo->codigo} ({$insumo->nombre}).",
                $ip
            );

            return $insumo;
        });
    }
}
