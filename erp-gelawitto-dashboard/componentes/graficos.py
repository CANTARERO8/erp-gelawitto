"""Opciones ECharts con la paleta de marca. Cada funcion recibe dark para ajustar texto y rejilla."""
FRESA, FRAMBUESA, MORA, DURAZNO = "#e8436b", "#b0244e", "#4a1430", "#ff9e80"
SERIE = [FRESA, DURAZNO, FRAMBUESA, "#8a4a68", "#d97aa0"]


def _base(dark: bool) -> dict:
    texto = "#f5edf0" if dark else "#2a2126"
    return {
        "textStyle": {"fontFamily": "Inter, sans-serif", "color": texto},
        "animationDuration": 1100, "animationEasing": "cubicOut", "animationDurationUpdate": 600,
        "grid": {"left": 54, "right": 54, "top": 40, "bottom": 40},
        "tooltip": {"trigger": "axis"},
        "_linea": "#3a2d36" if dark else "#ead5dc",
    }


def _cerrar(o, dark):
    texto = o["textStyle"]["color"]
    for eje in ("xAxis", "yAxis"):
        for e in (o[eje] if isinstance(o[eje], list) else [o[eje]]):
            e.setdefault("axisLabel", {})["color"] = texto
            e["nameTextStyle"] = {"color": texto}
    if "legend" in o:
        o["legend"].update({"top": 0})
        o["grid"]["top"] = 56
    o["grid"]["bottom"] = 64
    return o


def margen(filas, dark):
    o = _base(dark); linea = o.pop("_linea")
    filas = list(reversed(filas))
    o["grid"].update({"left": 190})
    o.update({
        "tooltip": {"trigger": "axis", ":valueFormatter": "(v) => v + ' %'"},
        "xAxis": {"type": "value", "max": 100, "axisLabel": {"formatter": "{value} %"}, "splitLine": {"lineStyle": {"color": linea}}},
        "yAxis": {"type": "category", "data": [f["nombre"] for f in filas]},
        "series": [{"type": "bar", "data": [f["margen"] for f in filas], "barWidth": 18,
                    "itemStyle": {"borderRadius": [0, 9, 9, 0], "color": {"type": "linear", "x": 0, "y": 0, "x2": 1, "y2": 0,
                                  "colorStops": [{"offset": 0, "color": FRAMBUESA}, {"offset": 1, "color": DURAZNO}]}},
                    "label": {"show": True, "position": "right", "formatter": "{c} %", "textBorderWidth": 0, "color": o["textStyle"]["color"]}}],
    })
    return _cerrar(o, dark)


def pareto(filas, dark):
    o = _base(dark); linea = o.pop("_linea")
    o.update({
        "legend": {"data": ["Ingreso", "Acumulado"], "textStyle": {"color": o["textStyle"]["color"]}},
        "xAxis": {"type": "category", "data": [f["nombre"][:22] for f in filas], "axisLabel": {"interval": 0, "rotate": 12}},
        "yAxis": [{"type": "value", "splitLine": {"lineStyle": {"color": linea}}},
                  {"type": "value", "max": 100, "axisLabel": {"formatter": "{value} %"}, "splitLine": {"show": False}}],
        "series": [
            {"name": "Ingreso", "type": "bar", "data": [f["ingreso"] for f in filas], "itemStyle": {"color": FRESA, "borderRadius": [8, 8, 0, 0]}},
            {"name": "Acumulado", "type": "line", "yAxisIndex": 1, "smooth": True, "data": [f["acumulado"] for f in filas],
             "color": MORA if not dark else DURAZNO, "lineStyle": {"width": 3}},
        ],
    })
    return _cerrar(o, dark)


def receta(filas, dark):
    o = _base(dark); linea = o.pop("_linea")
    nombres = [f["producto"][:24] for f in filas]
    o.update({
        "legend": {"data": ["Costo por unidad", "Precio de venta"], "textStyle": {"color": o["textStyle"]["color"]}},
        "xAxis": {"type": "category", "data": nombres, "axisLabel": {"interval": 0, "rotate": 12}},
        "yAxis": {"type": "value", "splitLine": {"lineStyle": {"color": linea}}},
        "series": [
            {"name": "Costo por unidad", "type": "bar", "data": [round(f["costo"], 2) for f in filas], "itemStyle": {"color": DURAZNO, "borderRadius": [8, 8, 0, 0]}},
            {"name": "Precio de venta", "type": "bar", "data": [f["precio"] for f in filas], "itemStyle": {"color": FRAMBUESA, "borderRadius": [8, 8, 0, 0]}},
        ],
    })
    return _cerrar(o, dark)


def stock(filas, dark):
    o = _base(dark); linea = o.pop("_linea")
    o.update({
        "legend": {"data": ["Stock actual", "Stock minimo"], "textStyle": {"color": o["textStyle"]["color"]}},
        "xAxis": {"type": "category", "data": [f["nombre"][:18] for f in filas], "axisLabel": {"interval": 0, "rotate": 25}},
        "yAxis": {"type": "value", "splitLine": {"lineStyle": {"color": linea}}},
        "series": [
            {"name": "Stock actual", "type": "bar", "data": [f["stock"] for f in filas], "itemStyle": {"color": FRESA, "borderRadius": [8, 8, 0, 0]}},
            {"name": "Stock minimo", "type": "line", "data": [f["minimo"] for f in filas], "color": MORA if not dark else DURAZNO,
             "lineStyle": {"type": "dashed", "width": 2}, "symbol": "diamond", "symbolSize": 9},
        ],
    })
    return _cerrar(o, dark)


def precios(hist, dark):
    o = _base(dark); linea = o.pop("_linea")
    por = {}
    for h in hist:
        por.setdefault(h["nombre"][:26], []).append([h["fecha"].isoformat(), h["costo"]])
    o.update({
        "legend": {"data": list(por), "textStyle": {"color": o["textStyle"]["color"]}, "type": "scroll"},
        "xAxis": {"type": "time", "splitLine": {"show": False}},
        "yAxis": {"type": "value", "splitLine": {"lineStyle": {"color": linea}}},
        "color": SERIE,
        "series": [{"name": n, "type": "line", "smooth": True, "symbolSize": 9, "data": d, "lineStyle": {"width": 3}} for n, d in por.items()],
    })
    return _cerrar(o, dark)
