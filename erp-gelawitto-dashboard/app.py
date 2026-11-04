"""Dashboard Gelawitto: analisis financiero de rentabilidad y abastecimiento MRP."""
from pathlib import Path

import psycopg2
from nicegui import app, ui

import config
from componentes import graficos
from componentes.tarjetas import kpi, tabla, tarjeta
from datos import compras, rentabilidad

app.add_static_files("/estatico", str(Path(__file__).parent / "estatico"))
dinero = lambda v: f"{v:,.2f}"
ALTURA = "height:320px;width:100%"


def seguro(fn):
    """Si la base no responde, la seccion muestra el aviso en vez de romper la pagina."""
    def envoltura(*a, **k):
        try:
            return fn(*a, **k)
        except psycopg2.Error as e:
            tarjeta("Sin conexion con la base de datos", animar=False)
            ui.label(str(e).splitlines()[0]).classes("vacio")
    envoltura.__name__ = fn.__name__
    return envoltura


@ui.refreshable
@seguro
def vista_rentabilidad(dark: bool, animar: bool = False):
    m = rentabilidad.margen_por_producto()
    p = rentabilidad.pareto(m)
    r = rentabilidad.costo_receta()
    ingreso = sum(f["ingreso"] for f in m); utilidad = sum(f["utilidad"] for f in m)
    mejor = max(m, key=lambda f: f["margen"]) if m else None

    with ui.element("div").classes("rejilla-kpi"):
        kpi("ingreso", "Ingreso por ventas", ingreso, 2, " C$", "Ventas completadas en caja", 0, animar)
        kpi("utilidad", "Utilidad bruta neta", utilidad, 2, " C$", "Ingresos menos costo de materia prima", 1, animar)
        kpi("margen", "Margen bruto promedio", round(100 * utilidad / ingreso, 1) if ingreso else 0, 1, " %", "Rendimiento comercial global", 2, animar)
        kpi("mejor", "Sabor más rentable", mejor["margen"] if mejor else 0, 1, " %", mejor["nombre"] if mejor else "", 3, animar)

    with ui.element("div").classes("rejilla-2"):
        with tarjeta("Margen porcentual por producto", 4, animar):
            ui.echart(graficos.margen(m, dark)).style(ALTURA) if m else ui.label("Sin ventas registradas").classes("vacio")
        with tarjeta("Curva de Pareto de ingresos", 5, animar):
            ui.echart(graficos.pareto(p, dark)).style(ALTURA) if p else ui.label("Sin ventas registradas").classes("vacio")
        with tarjeta("Costo unitario de fórmula frente a precio de venta", 6, animar):
            ui.echart(graficos.receta(r, dark)).style(ALTURA) if r else ui.label("Sin recetas activas").classes("vacio")
        with tarjeta("Rentabilidad y costeo detallado de recetas", 7, animar):
            tabla([("Producto", "producto", "2.2fr"), ("Costo", "costo", "1fr", dinero), ("Precio", "precio", "1fr", dinero),
                   ("Margen", "margen", "1fr", lambda v: f"{v} %", lambda f: "verde" if f["margen"] >= 60 else "azul" if f["margen"] >= 40 else "rojo")], r)


@ui.refreshable
@seguro
def vista_compras(dark: bool, animar: bool = False):
    plan = compras.plan_compras()
    reorden = [f for f in plan if f["estado"] == "Reordenar"]
    vigilar = [f for f in plan if f["estado"] == "Vigilar"]
    sugeridas = [f for f in plan if f["sugerida"] > 0]
    hist = compras.historial_precios()

    with ui.element("div").classes("rejilla-kpi"):
        kpi("quiebre", "Insumos bajo mínimo crítico", len(reorden), 0, "", f"{len(vigilar)} adicionales por vigilar", 0, animar)
        kpi("valorc", "Inversión estimada de compra", sum(f["valor"] for f in sugeridas), 2, " C$", f"Cobertura para {config.HORIZONTE_DIAS} días", 1, animar)
        kpi("insumos", "Catálogo activo de insumos", len(plan), 0, "", "Materias primas y empaques en kardex", 2, animar)

    with ui.element("div").classes("rejilla-2"):
        with tarjeta("Existencia en kardex frente a stock mínimo", 3, animar):
            ui.echart(graficos.stock(plan, dark)).style(ALTURA) if plan else ui.label("Sin insumos").classes("vacio")
        with tarjeta("Histórico de precios de compra unitario", 4, animar):
            ui.echart(graficos.precios(hist, dark)).style(ALTURA) if hist else ui.label("Sin ordenes de compra").classes("vacio")
    with tarjeta("Sugerencia de abastecimiento por proveedor", 5, animar):
        tono = {"Reordenar": "rojo", "Vigilar": "azul", "Suficiente": "verde"}
        tabla([("Insumo", "nombre", "2.4fr"), ("Proveedor", "proveedor", "2fr"), ("Stock", "stock", "0.8fr", lambda v: f"{v:g}"),
               ("Mínimo", "minimo", "0.8fr", lambda v: f"{v:g}"), ("Días cobertura", "dias", "1fr", lambda v: "Sin consumo" if v is None else f"{v:g}"),
               ("Estado", "estado", "1fr", None, lambda f: tono[f["estado"]]), ("Sugerido", "sugerida", "0.8fr", lambda v: f"{v:g}"),
               ("Presupuesto", "valor", "1fr", dinero)], sorted(plan, key=lambda f: ("Reordenar", "Vigilar", "Suficiente").index(f["estado"])))


@ui.page("/")
def principal(embed: bool = False, dark_mode: bool = False):
    ui.add_head_html(
        '<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">'
        '<link rel="stylesheet" href="/estatico/tema.css"><script src="/estatico/tema.js" defer></script>'
    )
    if embed:
        ui.add_head_html(
            '<script>'
            'document.addEventListener("DOMContentLoaded", () => {'
            '  document.body.classList.add("modo-embed");'
            '  const emitirAltura = () => {'
            '    const h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);'
            '    window.parent.postMessage({ tipo: "GELAWITTO_DASH_HEIGHT", height: h }, "*");'
            '  };'
            '  window.addEventListener("resize", emitirAltura);'
            '  new ResizeObserver(emitirAltura).observe(document.body);'
            '  setTimeout(emitirAltura, 300);'
            '  setTimeout(emitirAltura, 1000);'
            '  setInterval(emitirAltura, 3000);'
            '});'
            '</script>'
        )

    dark = ui.dark_mode(dark_mode)
    vistas = (vista_rentabilidad, vista_compras)

    if not embed:
        with ui.element("header").classes("cabecera"):
            with ui.element("div"):
                ui.html('<div class="sub">Gelawitto ERP</div><h1>Dashboard</h1>', sanitize=False)
            with ui.element("nav"):
                ui.html('<a href="#rentabilidad">Rentabilidad</a><a href="#compras">Compras</a>', sanitize=False)
                ui.button("Cambiar tema", on_click=lambda: dark.toggle()).props("flat no-caps").classes("btn-tema")

    with ui.element("main").classes("contenido"):
        # Seccion 1: Rentabilidad
        with ui.element("section").classes("seccion").props("id=rentabilidad"):
            ui.html(
                '<div class="seccion-header">'
                '  <div>'
                '    <span class="seccion-tag">Control Financiero y Comercial</span>'
                '    <h2 class="seccion-titulo">Rentabilidad Real por Producto y Receta</h2>'
                '    <p class="seccion-bajada">Margen bruto obtenido por ventas y costeo unitario de fórmulas maestras contra precio al público</p>'
                '  </div>'
                '  <div class="seccion-badge">Cálculo en vivo sobre libro de ventas</div>'
                '</div>',
                sanitize=False,
            )
            vista_rentabilidad(dark.value, True)

        # Seccion 2: Compras
        with ui.element("section").classes("seccion").props("id=compras"):
            ui.html(
                '<div class="seccion-header">'
                '  <div>'
                '    <span class="seccion-tag">Abastecimiento y Operaciones</span>'
                '    <h2 class="seccion-titulo">Planificación de Compras y Reorden (MRP)</h2>'
                '    <p class="seccion-bajada">Días de inventario restante, insumos bajo mínimo operativo y presupuesto sugerido por proveedor</p>'
                '  </div>'
                '  <div class="seccion-badge">Horizonte proyectado a 14 días</div>'
                '</div>',
                sanitize=False,
            )
            vista_compras(dark.value, True)

    dark.on_value_change(lambda e: [v.refresh(e.value, False) for v in vistas])
    ui.timer(30, lambda: [v.refresh(dark.value, False) for v in vistas])


if __name__ in {"__main__", "__mp_main__"}:
    ui.run(port=config.PUERTO, title="Dashboard Gelawitto", reload=False, show=False)
