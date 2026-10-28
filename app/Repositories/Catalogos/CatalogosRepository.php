<?php

namespace App\Repositories\Catalogos;

use App\Models\Catalogos\Cliente;
use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Proveedor;
use App\Models\Catalogos\UnidadMedida;
use Illuminate\Database\Eloquent\Collection;

class CatalogosRepository
{
    public function getProductos(): Collection
    {
        return Producto::orderBy('sku')->get();
    }

    public function getProductosActivos(): Collection
    {
        return Producto::where('activo', true)->orderBy('nombre')->get();
    }

    public function getInsumos(): Collection
    {
        return Insumo::with('unidad')->orderBy('codigo')->get();
    }

    public function getInsumosActivos(): Collection
    {
        return Insumo::with('unidad')->where('activo', true)->orderBy('nombre')->get();
    }

    public function getProveedores(): Collection
    {
        return Proveedor::orderBy('nombre')->get();
    }

    public function getProveedoresActivos(): Collection
    {
        return Proveedor::where('activo', true)->orderBy('nombre')->get();
    }

    public function getClientes(): Collection
    {
        return Cliente::orderBy('nombre')->get();
    }

    public function getClientesActivos(): Collection
    {
        return Cliente::where('activo', true)->orderBy('nombre')->get();
    }

    public function getUnidades(): Collection
    {
        return UnidadMedida::orderBy('nombre')->get();
    }

    public function createProducto(array $data): Producto
    {
        return Producto::create($data);
    }

    public function createInsumo(array $data): Insumo
    {
        return Insumo::create($data);
    }

    public function createProveedor(array $data): Proveedor
    {
        return Proveedor::create($data);
    }

    public function createCliente(array $data): Cliente
    {
        return Cliente::create($data);
    }

    public function findCliente(int $id): ?Cliente
    {
        return Cliente::find($id);
    }

    public function findProducto(int $id): ?Producto
    {
        return Producto::find($id);
    }

    public function findProductoForUpdate(int $id): ?Producto
    {
        return Producto::where('id', $id)->lockForUpdate()->first();
    }

    public function findInsumo(int $id): ?Insumo
    {
        return Insumo::find($id);
    }

    public function findInsumoForUpdate(int $id): ?Insumo
    {
        return Insumo::where('id', $id)->lockForUpdate()->first();
    }
}
