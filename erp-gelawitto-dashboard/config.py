"""Credenciales y parametros del Dashboard. Solo lectura sobre la base del ERP."""
import os
from pathlib import Path

_RAIZ = Path(__file__).resolve().parent


def _leer_env(ruta: Path) -> dict:
    valores = {}
    if ruta.is_file():
        for linea in ruta.read_text().splitlines():
            if "=" in linea and not linea.lstrip().startswith("#"):
                k, v = linea.split("=", 1)
                valores[k.strip()] = v.strip().strip('"')
    return valores


# Prioridad: variables de entorno, .env propio, .env de Laravel en la raíz del repositorio (solo desarrollo local).
_env = {**_leer_env(_RAIZ.parent / ".env"), **_leer_env(_RAIZ / ".env"), **os.environ}

DB = {
    "host": _env.get("DB_HOST", "127.0.0.1"),
    "port": int(_env.get("DB_PORT", 5433)),
    "dbname": _env.get("DB_DATABASE", "gelawitto_erp"),
    "user": _env.get("DB_USERNAME", "gelawitto"),
    "password": _env.get("DB_PASSWORD", ""),
}
PUERTO = int(_env.get("DASHBOARD_PORT", 8501))
HORIZONTE_DIAS = int(_env.get("DASHBOARD_HORIZONTE_DIAS", 14))
