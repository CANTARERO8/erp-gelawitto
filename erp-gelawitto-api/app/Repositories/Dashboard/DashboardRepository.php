<?php

namespace App\Repositories\Dashboard;

use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getVentasPorProducto(): array
    {
        return DB::select("
            select p.nombre, sum(vl.cantidad*vl.precio_unitario)::float as ingreso,
                   sum(vl.cantidad*vl.costo_unitario)::float as costo
            from venta_lineas vl
            join productos p on p.id = vl.producto_id
            join ventas v on v.id = vl.venta_id
            where v.estado = 'Completada'
            group by p.nombre order by ingreso desc
        ");
    }

    public function getCosteoRecetas(): array
    {
        return DB::select("
            select r.nombre as receta, p.nombre as producto, p.precio_venta::float as precio,
                   (sum(ri.cantidad_requerida*(1+ri.merma_estimada_porcentaje/100)*i.costo_promedio)
                   / nullif(r.rendimiento_unidades,0))::float as costo
            from recetas r
            join productos p on p.id = r.producto_id
            join receta_insumos ri on ri.receta_id = r.id
            join insumos i on i.id = ri.insumo_id
            where r.activa
            group by r.id, p.id order by r.nombre
        ");
    }

    public function getPlanInsumos(): array
    {
        return DB::select("
            select i.id, i.codigo, i.nombre, i.stock_actual::float as stock, i.stock_minimo::float as minimo,
                   i.costo_promedio::float as costo,
                   coalesce((select sum(-m.cantidad) from movimientos_inventario m where m.tipo_item='insumo' and m.item_id=i.id and m.cantidad<0 and m.fecha >= now() - interval '30 days'),0)::float/30 as consumo,
                   (select pr.nombre from orden_compra_lineas l join ordenes_compra o on o.id=l.orden_compra_id join proveedores pr on pr.id=o.proveedor_id where l.insumo_id=i.id order by o.fecha_emision desc limit 1) as proveedor
            from insumos i where i.activo and (i.stock_minimo > 0 or i.stock_actual > 0) order by i.nombre
        ");
    }

    public function getHistorialPrecios(): array
    {
        return DB::select("
            select i.nombre, o.fecha_emision::date as fecha, l.costo_unitario::float as costo
            from orden_compra_lineas l
            join ordenes_compra o on o.id=l.orden_compra_id
            join insumos i on i.id=l.insumo_id
            order by o.fecha_emision
        ");
    }
}
