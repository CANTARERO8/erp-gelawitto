"""Acceso de solo lectura a PostgreSQL."""
import psycopg2
import psycopg2.extras

from config import DB


def consultar(sql: str, params: tuple = ()) -> list[dict]:
    conn = psycopg2.connect(**DB)
    try:
        conn.set_session(readonly=True, autocommit=True)
        with conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
            cur.execute(sql, params or None)
            return [dict(r) for r in cur.fetchall()]
    finally:
        conn.close()
