"""Rentabilidad por producto y receta."""
from datos.consultas import consultar


def margen_por_producto() -> list[dict]:
    filas = consultar(
        "select p.nombre, sum(vl.cantidad*vl.precio_unitario)::float as ingreso, "
        "sum(vl.cantidad*vl.costo_unitario)::float as costo "
        "from venta_lineas vl join productos p on p.id = vl.producto_id "
        "join ventas v on v.id = vl.venta_id where v.estado = 'Completada' "
        "group by p.nombre order by ingreso desc"
    )
    for f in filas:
        f["utilidad"] = f["ingreso"] - f["costo"]
        f["margen"] = round(100 * f["utilidad"] / f["ingreso"], 1) if f["ingreso"] else 0.0
    return filas


def pareto(filas: list[dict]) -> list[dict]:
    """Agrega porcentaje acumulado de ingreso, ordenado de mayor a menor."""
    orden = sorted(filas, key=lambda f: f["ingreso"], reverse=True)
    total = sum(f["ingreso"] for f in orden) or 1
    acum = 0.0
    salida = []
    for f in orden:
        acum += f["ingreso"]
        salida.append({**f, "acumulado": round(100 * acum / total, 1)})
    return salida


def costo_receta() -> list[dict]:
    """Costo unitario por receta de producto terminado, explotando la BOM de dos niveles.

    Las líneas de insumo se costean con el costo promedio del insumo. Las líneas de
    subensamble se costean con el costo por unidad de la receta del subensamble.
    """
    filas = consultar(
        "with costo_sub as ("
        "  select r.producto_id, "
        "  (sum(ri.cantidad_requerida*(1+ri.merma_estimada_porcentaje/100)*i.costo_promedio) "
        "   / nullif(r.rendimiento_unidades,0))::float as costo_unidad "
        "  from recetas r join receta_insumos ri on ri.receta_id = r.id "
        "  join insumos i on i.id = ri.insumo_id "
        "  where r.activa group by r.id"
        ") "
        "select r.nombre as receta, p.nombre as producto, p.precio_venta::float as precio, "
        "(sum(ri.cantidad_requerida*(1+ri.merma_estimada_porcentaje/100)"
        "     *coalesce(i.costo_promedio, cs.costo_unidad)) "
        " / nullif(r.rendimiento_unidades,0))::float as costo "
        "from recetas r join productos p on p.id = r.producto_id "
        "join receta_insumos ri on ri.receta_id = r.id "
        "left join insumos i on i.id = ri.insumo_id "
        "left join costo_sub cs on cs.producto_id = ri.subproducto_id "
        "where r.activa and p.tipo = 'terminado' "
        "group by r.id, p.id order by r.nombre"
    )
    for f in filas:
        f["costo"] = f["costo"] or 0.0
        f["margen"] = round(100 * (f["precio"] - f["costo"]) / f["precio"], 1) if f["precio"] else 0.0
    return filas
