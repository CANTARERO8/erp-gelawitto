# ERP Gelawitto API (Backend REST & Motor Transaccional)

Servicio Backend REST y motor transaccional construido con Laravel 11 y PostgreSQL 16 para el sistema ERP Gelawitto, estructurado bajo el estandar de arquitectura desacoplada de Carnes San Martin (`SM_LOG_API`).

## Caracteristicas Principales

- **Arquitectura REST Pura:** Todos los controladores devuelven respuestas estandarizadas en formato `JsonResponse` (`200 OK`, `201 Created`, `422 Unprocessable Content`, etc.).
- **Secuencias Atomicas PostgreSQL:** Los folios de compras (`folio_compras_seq`), lotes (`folio_lotes_seq`), ventas (`folio_ventas_seq`), turnos de caja (`folio_turnos_seq`), movimientos financieros (`folio_finanzas_seq`), controles de calidad (`folio_calidad_seq`) y asientos contables (`folio_asientos_seq`) se generan mediante secuencias nativas de base de datos (`nextval(...)`), eliminando condiciones de carrera y folios duplicados por concurrencia.
- **Reglas de Negocio en Base de Datos:**
  - Tabla `parametros`: Tasa de IVA configurable (`iva_porcentaje = 15`), codigo del sensor de refrigeracion (`refrigerador_codigo = REF-01`) y limites de temperatura (`refrigerador_temp_min = 2.0`, `refrigerador_temp_max = 6.0`).
  - Tablas `roles`, `modulos` y `rol_modulo`: Permisos y asignacion de navegacion determinados por relaciones relacionales en PostgreSQL.
  - Tabla `cuentas_contables`: Catalogo maestro de cuentas para el Libro Diario y balances contables de partida doble.
- **Respaldo SQL Completo:** Archivo `database/gelawitto_erp.sql` exportado mediante `pg_dump`, listo para restaurar en cualquier instancia de PostgreSQL 16 o pgAdmin 4.

## Puesta en Marcha

1. Copiar archivo de entorno y configurar credenciales de PostgreSQL:
   ```bash
   cp .env.example .env
   ```
2. Instalar dependencias de PHP y generar clave de aplicacion:
   ```bash
   composer install
   php artisan key:generate
   ```
3. Ejecutar migraciones y semillero inicial de datos:
   ```bash
   php artisan migrate:fresh --seed
   ```
4. Iniciar servidor de desarrollo en el puerto 8000:
   ```bash
   php artisan serve --port=8000
   ```

## Restauracion de Base de Datos desde `gelawitto_erp.sql`

Para restaurar el respaldo de la base de datos directamente en PostgreSQL:
```bash
PGPASSWORD=secret psql -h 127.0.0.1 -p 5433 -U gelawitto -d gelawitto_erp < database/gelawitto_erp.sql
```
O bien mediante la herramienta "Restore" de pgAdmin 4 seleccionando `database/gelawitto_erp.sql`.

## Pruebas Automatizadas

Ejecutar la suite completa de 45 pruebas transaccionales con PHPUnit:
```bash
php artisan test
```
