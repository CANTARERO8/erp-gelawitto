"""Tarjetas KPI y tabla de filas con chips de estado."""
from nicegui import ui

CHIP = {"verde": "chip-verde", "azul": "chip-azul", "rojo": "chip-rojo", "gris": "chip-gris"}


def kpi(clave: str, rotulo: str, valor: float, dec: int = 0, sufijo: str = "", nota: str = "", i: int = 0, animar: bool = True):
    with ui.element("div").classes("tarjeta kpi" + (" entra" if animar else "")).style(f"--i:{i}"):
        ui.html(f'<div class="rotulo">{rotulo}</div>', sanitize=False)
        ui.html(
            f'<div class="valor"><span class="kpi-num" data-k="{clave}" data-v="{valor}" data-d="{dec}">0</span>{sufijo}</div>',
            sanitize=False,
        )
        if nota:
            ui.html(f'<div class="nota">{nota}</div>', sanitize=False)


def tarjeta(titulo: str, i: int = 0, animar: bool = True, extra: str = ""):
    t = ui.element("div").classes("tarjeta" + (" entra" if animar else "") + (f" {extra}" if extra else "")).style(f"--i:{i}")
    with t:
        ui.html(f"<h3>{titulo}</h3>", sanitize=False)
    return t


def tabla(columnas: list[tuple], filas: list[dict], vacio: str = "Sin registros"):
    """columnas: (titulo, clave, ancho_css, formato opcional, tono opcional por fila)."""
    if not filas:
        ui.label(vacio).classes("vacio")
        return
    plantilla = " ".join(c[2] for c in columnas)
    with ui.element("div").classes("tabla"):
        with ui.element("div").classes("fila cab").style(f"grid-template-columns:{plantilla}"):
            for c in columnas:
                ui.label(c[0])
        for f in filas:
            with ui.element("div").classes("fila").style(f"grid-template-columns:{plantilla}"):
                for c in columnas:
                    texto = c[3](f[c[1]]) if len(c) > 3 and c[3] else f[c[1]]
                    tono = c[4](f) if len(c) > 4 and c[4] else None
                    lbl = ui.label("" if texto is None else str(texto))
                    if tono:
                        lbl.classes(f"chip {CHIP[tono]}")
