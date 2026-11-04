# Gelawitto ERP - Dashboard Analitico en Python

Aplicacion independiente en Python para rentabilidad por producto y planificacion de compras (la telemetria de la camara fria se consulta en el modulo de Calidad del ERP) conectada en modo solo lectura a PostgreSQL.

## Requisitos

1. Python 3.12 o superior (probado con Python 3.14).
2. Instancia de PostgreSQL de Gelawitto ERP ejecutandose en el puerto 5433.

## Instalacion y Puesta en Marcha

1. Crear entorno virtual e instalar dependencias:

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install nicegui psycopg2-binary pytest
```

2. Ejecutar pruebas unitarias de calculos:

```bash
pytest tests/test_calculos.py
```

3. Iniciar el servidor del dashboard:

```bash
python app.py
```

El dashboard estara disponible en `http://localhost:8501`.

## Estructura del Modulo

1. `app.py`: Servidor NiceGUI, enrutamiento, diseno responsive, timers de refresco y cambio de tema.
2. `config.py`: Resolucion de variables de entorno y conexion de solo lectura a la base de datos.
3. `datos/`: Modulos de consulta SQL y formulas de negocio para frio, rentabilidad y compras.
4. `componentes/`: Tarjetas de KPI animadas, tablas de datos y configuracion de graficos ECharts con paleta de marca.
5. `estatico/`: Estilos CSS de tema claro y oscuro, y script JavaScript para conteo numerico animado.
