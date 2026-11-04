from datos.compras import cantidad_sugerida, dias_restantes, estado_reorden
from datos.rentabilidad import pareto


def test_pareto_ordena_y_acumula_100():
    r = pareto([{"nombre": "a", "ingreso": 10}, {"nombre": "b", "ingreso": 30}, {"nombre": "c", "ingreso": 60}])
    assert [f["nombre"] for f in r] == ["c", "b", "a"]
    assert r[-1]["acumulado"] == 100.0


def test_dias_sin_consumo_es_none():
    assert dias_restantes(10, 0) is None
    assert dias_restantes(10, 2) == 5.0


def test_estado_reorden():
    assert estado_reorden(5, 10, None) == "Reordenar"
    assert estado_reorden(50, 10, 3.0, 14) == "Vigilar"
    assert estado_reorden(50, 10, None) == "Suficiente"


def test_cantidad_sugerida():
    assert cantidad_sugerida(6.5, 10, 0) == 13.5
    assert cantidad_sugerida(100, 10, 1, 14) == 0.0


def test_costo_receta_incluye_subensamble_y_excluye_semielaborados():
    """Requiere la base del ERP cargada; se omite si no hay conexión."""
    import pytest
    from datos.rentabilidad import costo_receta

    try:
        filas = costo_receta()
    except Exception:
        pytest.skip("Base de datos del ERP no disponible")
    productos = {f["producto"]: f for f in filas}
    assert "Base Gelificada de Arroz (Subensamble)" not in productos
    tradicional = productos["Gelawitto Tradicional de Arroz"]
    assert 20.0 < tradicional["costo"] < 22.0
