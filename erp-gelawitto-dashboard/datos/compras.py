"""Planificacion de compras y MRP."""
from config import HORIZONTE_DIAS
from datos.consultas import consultar


def dias_restantes(stock: float, consumo_diario: float) -> float | None:
    """None cuando no hay consumo registrado: no se inventa un ritmo."""
    return round(stock / consumo_diario, 1) if consumo_diario > 0 else None


def estado_reorden(stock: float, minimo: float, dias: float | None, horizonte: int = HORIZONTE_DIAS) -> str:
    if minimo > 0 and stock < minimo:
        return "Reordenar"
    if dias is not None and dias < horizonte:
        return "Vigilar"
    return "Suficiente"


def cantidad_sugerida(stock: float, minimo: float, consumo_diario: float, horizonte: int = HORIZONTE_DIAS) -> float:
    objetivo = max(minimo * 2, consumo_diario * horizonte)
    return round(max(objetivo - stock, 0), 1)


def plan_compras() -> list[dict]:
    filas = consultar(
        "select i.id, i.codigo, i.nombre, i.stock_actual::float as stock, i.stock_minimo::float as minimo, "
        "i.costo_promedio::float as costo, "
        "coalesce((select sum(-m.cantidad) from movimientos_inventario m where m.tipo_item='insumo' "
        "and m.item_id=i.id and m.cantidad<0 and m.fecha >= now() - interval '30 days'),0)::float/30 as consumo, "
        "(select pr.nombre from orden_compra_lineas l join ordenes_compra o on o.id=l.orden_compra_id "
        "join proveedores pr on pr.id=o.proveedor_id where l.insumo_id=i.id order by o.fecha_emision desc limit 1) as proveedor "
        "from insumos i where i.activo and (i.stock_minimo > 0 or i.stock_actual > 0) order by i.nombre"
    )
    for f in filas:
        f["dias"] = dias_restantes(f["stock"], f["consumo"])
        f["estado"] = estado_reorden(f["stock"], f["minimo"], f["dias"])
        f["sugerida"] = cantidad_sugerida(f["stock"], f["minimo"], f["consumo"]) if f["estado"] != "Suficiente" else 0.0
        f["valor"] = round(f["sugerida"] * f["costo"], 2)
        f["proveedor"] = f["proveedor"] or "Sin proveedor"
    return filas


def historial_precios() -> list[dict]:
    return consultar(
        "select i.nombre, o.fecha_emision::date as fecha, l.costo_unitario::float as costo "
        "from orden_compra_lineas l join ordenes_compra o on o.id=l.orden_compra_id "
        "join insumos i on i.id=l.insumo_id order by o.fecha_emision"
    )
