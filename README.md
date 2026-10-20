# Gelawitto ERP

Sistema integral de planificación de recursos empresariales para la heladería artesanal **Gelawitto**, diseñado para administrar catálogos maestros, compras a proveedores, inventario con libro de Kardex inmutable, formulación y lotes de producción, punto de venta con corte de caja, finanzas operativas y auditoría de seguridad.

Construido con **Laravel**, **Vue 3 (Inertia.js + TypeScript)**, **Tailwind CSS v4** y **PostgreSQL 16**, con una temática visual inspirada en el diseño de doble columna y panel administrativo de Carnes San Martín fusionada con la paleta cromática de Gelawitto.

---

## Arquitectura y Stack Tecnológico

- **Backend y Reglas de Negocio:** Laravel con PHP 8.3+, transacciones atómicas (`DB::transaction`) y bloqueo pesimista (`lockForUpdate`) en Kardex y Caja
- **Interfaz Reactiva:** Vue 3 (Composition API, TypeScript) conectado mediante Inertia.js
- **Sistema de Diseño:** Tailwind CSS v4 con soporte nativo para modo claro y modo oscuro
- **Tipografías:** Fredoka (titulares) e Inter (cuerpo de texto, formularios y tablas de datos)
- **Base de Datos:** PostgreSQL 16 (`postgres:16`) en contenedor Docker
- **Pruebas Automatizadas:** PHPUnit (integración y transacciones sobre PostgreSQL 16) y Vitest (validación de paleta y componentes)

---

## Identidad Visual y Paleta de Colores

| Tono | Valor Hex | Función en el sistema |
| :--- | :--- | :--- |
| **Fresa** | `#e8436b` | Color primario y acciones principales |
| **Frambuesa** | `#b0244e` | Estados interactivos, folios y enlaces destacados |
| **Mora** | `#4a1430` | Estructura de navegación y menú lateral semi-dark |
| **Durazno** | `#ff9e80` | Acentos secundarios y avisos cálidos |
| **Rubor** | `#fde7ec` | Fondos de selección y etiquetas de rol |
| **Nata** | `#fff9f7` | Fondo principal de la aplicación en modo claro |
| **Tinta** | `#2a2126` | Tipografía principal de alto contraste |

---

## Módulos Integrados del ERP

1. **Autenticación y Seguridad (`Auth/Login` y `/seguridad`):**
   - Pantalla de inicio de sesión estilo *Split-Screen (8/4)* con fotografía heroica de heladería artesanal, insignia flotante, alternador de visibilidad de contraseña, bloqueo temporal tras 5 intentos fallidos (60 segundos), selector de modo claro/oscuro, gestión de roles (`Administrador`, `Producción`, `Cajero`, `Contador`) y bitácora de auditoría.
2. **Tablero Ejecutivo (`/dashboard`):**
   - Indicadores en tiempo real de ventas del día, ventas acumuladas, valorización de inventario de insumos y helados terminados, alertas de stock mínimo, cuentas por pagar y rentabilidad por sabor.
3. **Catálogos Maestros (`/catalogos`):**
   - Gestión de productos terminados (SKU, sabor, presentación, costo y precio), materia prima/insumos, unidades de medida, proveedores y clientes.
4. **Compras a Proveedores (`/compras`):**
   - Recepción de órdenes de compra que incrementa el stock del insumo, recalcula su costo promedio ponderado, genera el asiento en el Kardex y registra el egreso o cuenta por pagar en Finanzas.
5. **Inventario y Kardex Inmutable (`/inventario`):**
   - Existencias valorizadas en tiempo real, registro justificado de mermas o ajustes físicos y consulta del Libro Mayor de Kardex inmutable.
6. **Recetas y Producción por Lotes (`/produccion`):**
   - Fichas técnicas con desglose de ingredientes por base y procesamiento transaccional de lotes que descuenta insumos, calcula el costo unitario real del lote, registra la merma e ingresa el helado terminado al almacén.
7. **Ventas, Punto de Venta y Caja (`/ventas`):**
   - Apertura de turno con fondo inicial, cobro en mostrador/mayorista (Efectivo, Tarjeta, Transferencia) con descuento automático de stock y corte/arqueo de caja con cálculo de diferencia.
8. **Finanzas Operativas (`/finanzas`):**
   - Estado de flujo de caja, registro de gastos operativos y liquidación de cuentas por pagar a proveedores.

---

## Puesta en Marcha e Instalación

### Requisitos previos

- PHP 8.3 o superior con extensiones `pdo_pgsql`, `pgsql`, `mbstring`, `xml`, `bcmath`, `curl`, `zip`
- Composer 2.x
- Node.js 22 LTS o superior y npm 10+
- Docker y Docker Compose (para PostgreSQL 16 y pgAdmin 4)

### Pasos de instalación

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/CANTARERO8/erp-gelawitto.git
   cd erp-gelawitto
   ```

2. **Configurar variables de entorno:**
   ```bash
   cp .env.example .env
   ```
   Ajustar en `.env` las contraseñas `DB_PASSWORD` y `PGADMIN_PASSWORD`.

3. **Levantar PostgreSQL 16:**
   ```bash
   docker compose up -d
   ```
   PostgreSQL 16 queda disponible en `127.0.0.1:5433` (base de datos `gelawitto_erp`, usuario `gelawitto`).

4. **Instalar dependencias de PHP y Node.js:**
   ```bash
   composer install
   npm install
   php artisan key:generate
   ```

5. **Ejecutar migraciones y datos iniciales (Seeders):**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Compilar activos e iniciar el servidor:**
   ```bash
   npm run build
   php artisan serve
   ```
   Para desarrollo con recarga en caliente:
   ```bash
   composer run dev
   ```
   Acceder a `http://localhost:8000` en el navegador.

### Credenciales operativas iniciales

- **Administrador:** `admin@gelawitto.com` / `password123`
- **Producción:** `produccion@gelawitto.com` / `password123`
- **Cajero:** `caja@gelawitto.com` / `password123`
- **Contador:** `finanzas@gelawitto.com` / `password123`

---

## Guía de Colaboración y Flujo de Trabajo

### 1. Convención de ramas

Toda modificación debe realizarse en una rama específica derivada de `main`:

- `tarea/XX-nombre`: Tareas específicas o asignaciones de desarrollo.
- `feat/nombre-modulo`: Nuevas características o módulos del ERP.
- `fix/descripcion`: Correcciones puntuales de errores.
- `docs/descripcion`: Actualizaciones de documentación o bitácoras.

### 2. Flujo de trabajo paso a paso

1. **Sincronizar la rama principal:**
   ```bash
   git checkout main
   git pull origin main
   ```

2. **Crear una rama de trabajo:**
   ```bash
   git checkout -b feat/nombre-funcionalidad
   ```

3. **Desarrollar y validar pruebas locales:**
   ```bash
   npm test
   npm run build
   php artisan test
   ```

4. **Registrar el aporte en la bitácora:**
   Agregar una fila en `docs/entregable/bitacora-aportes.md` indicando fecha, nombre, tarea, Pull Request, trabajo realizado y aprendizaje.

5. **Confirmar con Commits Semánticos y publicar:**
   ```bash
   git add .
   git commit -m "feat: descripcion concisa del cambio"
   git push -u origin feat/nombre-funcionalidad
   ```

6. **Abrir Pull Request y Revisión de Código:**
   Crear el PR en GitHub hacia `main` para su revisión y aprobación.

---

## Estructura Detallada de Archivos y Componentes del Proyecto

A continuación se detalla la función técnica de cada directorio y archivo clave que conforma el ERP Gelawitto:

### 1. Núcleo, Configuración y Entorno de Ejecución
- `docker-compose.yml`: Define y orquesta el contenedor de PostgreSQL 16 (`postgres:16`) y la interfaz administrativa pgAdmin 4, aislando el almacenamiento en un volumen persistente.
- `.env.example`: Plantilla de variables de entorno (conexión a base de datos en puerto `5433`, configuraciones de sesión, app key y caché).
- `artisan`: Interfaz de línea de comandos de Laravel para ejecutar migraciones, seeders, pruebas y tareas de mantenimiento.
- `bootstrap/app.php`: Punto de inicialización de la aplicación Laravel 11, configuración de middlewares globales y ruteo.
- `bootstrap/providers.php`: Registro de proveedores de servicio esenciales del framework.
- `config/database.php`: Configuración del conector PostgreSQL (`pgsql`) con soporte para tipos de datos avanzados y transacciones ACID.
- `config/auth.php`: Definición del proveedor de autenticación de usuarios sobre el modelo `User` con hashing seguro de contraseñas.
- `config/session.php`: Configuración del almacenamiento de sesiones para persistencia de credenciales y roles.
- `composer.json` / `composer.lock`: Dependencias del backend (Laravel 11, Inertia.js Server Adapter, soporte PostgreSQL y PHPUnit).
- `package.json` / `package-lock.json`: Dependencias del frontend (Vue 3, @inertiajs/vue3, Tailwind CSS v4, Lucide Icons, TypeScript, Vite y Vitest).
- `vite.config.js`: Orquestador de empaquetado de Vite con integración del plugin oficial de Laravel y el compilador de componentes Vue 3.
- `tsconfig.json`: Configuración del compilador de TypeScript con tipado estricto para componentes y vistas reactivas.

### 2. Base de Datos y Persistencia Relacional (`database/`)
- `database/migrations/0001_01_01_000000_create_users_table.php`: Estructura de la tabla de usuarios, roles del sistema (`Administrador`, `Producción`, `Cajero`, `Contador`) y reseteo de contraseñas.
- `database/migrations/0001_01_02_000000_create_erp_tables.php`: Esquema relacional con llaves foráneas y precisión numérica decimal (`NUMERIC(12,2)` para importes y `NUMERIC(12,3)` para existencias en gramos/mililitros). Crea las tablas:
  - `unidades_medida`, `proveedores`, `clientes`, `productos`, `insumos`.
  - `ordenes_compra` y `ordenes_compra_lineas` (gestión de compras y cuentas por pagar).
  - `movimientos_inventario` (Kardex inmutable para trazabilidad de entradas, salidas y mermas).
  - `recetas`, `recetas_insumos` y `lotes_produccion` (fichas técnicas y cálculo de costo real de helado).
  - `cajas_turnos`, `ventas` y `ventas_lineas` (apertura de turno, cobro y corte de caja).
  - `finanzas_movimientos` y `bitacora_auditoria` (flujo financiero y registro de operaciones críticas).
- `database/seeders/DatabaseSeeder.php`: Poblador integral de la base de datos con usuarios para cada rol, catálogo de unidades, proveedores locales, clientes frecuentes, helados estrella (Fresa Silvestre, Frambuesa Cream, Mora Intensa, etc.), recetas formuladas y transacciones demostrativas.

### 3. Modelos de Dominio Eloquent (`app/Models/`)
- `app/Models/User.php`: Modelo de usuario con atributos de rol, autenticación y relación con registros operativos.
- `app/Models/Catalogos/`:
  - `Producto.php`: Modelado de sabores y presentaciones terminadas de helado con control de precio y costo.
  - `Insumo.php`: Materia prima con stock actual, stock mínimo para alertas y costo promedio ponderado.
  - `Proveedor.php`: Datos de contacto, razón social y condiciones comerciales de suministros.
  - `Cliente.php`: Registro de clientes particulares y mayoristas.
  - `UnidadMedida.php`: Definición de unidades estándar (kg, gr, lt, ml, pza).
- `app/Models/Compras/`:
  - `OrdenCompra.php`: Cabecera de compra con folio, proveedor, estado y fecha de entrega.
  - `OrdenCompraLinea.php`: Detalle de insumos comprados con cantidades y precios unitarios.
- `app/Models/Inventario/`:
  - `MovimientoInventario.php`: Registro inmutable del Kardex con tipo de movimiento, insumo/producto, cantidad, saldo resultante y justificación.
- `app/Models/Produccion/`:
  - `Receta.php`: Ficha técnica de formulación por sabor de helado y rendimiento proyectado.
  - `RecetaInsumo.php`: Proporción exacta de materia prima requerida por receta.
  - `LoteProduccion.php`: Registro de fabricación real, fecha, lote, merma y costo real calculado.
- `app/Models/Ventas/`:
  - `CajaTurno.php`: Control de apertura, fondo inicial, total cobrado y arqueo/cierre de caja por cajero.
  - `Venta.php`: Comprobante de venta con cliente, método de pago, subtotal, impuestos y total.
  - `VentaLinea.php`: Desglose de productos vendidos, cantidad y precio unitario.
- `app/Models/Finanzas/`:
  - `FinanzasMovimiento.php`: Asiento contable de ingresos, egresos operativos y liquidación a proveedores.
- `app/Models/Seguridad/`:
  - `BitacoraAuditoria.php`: Registro histórico de auditoría con usuario, módulo, acción y fecha.

### 4. Capa de Repositorios y Consultas (`app/Repositories/`)
- `app/Repositories/Catalogos/CatalogosRepository.php`: Abstracción de consultas para productos, insumos, clientes y proveedores activos.
- `app/Repositories/Compras/ComprasRepository.php`: Consulta de órdenes de compra pendientes y recepciones históricas.
- `app/Repositories/Inventario/InventarioRepository.php`: Lecturas optimizadas de existencias valorizadas e historial de Kardex.
- `app/Repositories/Produccion/ProduccionRepository.php`: Recuperación de fichas técnicas y trazabilidad de lotes fabricados.
- `app/Repositories/Ventas/VentasRepository.php`: Registro de turnos de caja, totales diarios y métricas por método de pago.
- `app/Repositories/Finanzas/FinanzasRepository.php`: Agrupación de flujo de caja y balance de cuentas pendientes.
- `app/Repositories/Seguridad/SeguridadRepository.php`: Consulta de eventos de auditoría del sistema.

### 5. Controladores y Lógica de Negocio (`app/Http/Controllers/`)
- `app/Http/Controllers/AuthController.php`: Gestión del ciclo de autenticación:
  - Validación de credenciales contra PostgreSQL.
  - Mecanismo anti-fuerza bruta: bloqueo temporal de 60 segundos tras 5 intentos fallidos con feedback al usuario.
  - Regeneración de identificador de sesión y cierre seguro (`logout`).
- `app/Http/Controllers/ErpController.php`: Orquestador principal de las operaciones del ERP:
  - `dashboard()`: Agregación en tiempo real de ventas, valorización de inventario y alertas de stock crítico.
  - `catalogos()`, `storeProducto()`, `storeInsumo()`: Alta y actualización de productos y materia prima.
  - `compras()`, `storeCompra()`: Transacción atómica de recepción de insumos, cálculo de costo promedio ponderado y asiento en Kardex.
  - `inventario()`, `ajusteInventario()`: Ajustes manuales y mermas con registro estricto en el libro de Kardex.
  - `produccion()`, `storeReceta()`, `procesarLote()`: Deducción atómica de insumos de inventario, ingreso del helado terminado y prorrateo de costo.
  - `ventas()`, `abrirCaja()`, `storeVenta()`, `cerrarCaja()`: Validación de turno activo, cobro multiformato y cálculo de diferencia de arqueo.
  - `finanzas()`, `storeGasto()`: Control de gastos de operación y pagos a proveedores.
  - `seguridad()`: Administración de usuarios y monitoreo de la bitácora de auditoría.
- `app/Http/Middleware/HandleInertiaRequests.php`: Puente de datos compartidos entre Laravel e Inertia (usuario autenticado, rol, notificaciones flash).

### 6. Rutas del Sistema (`routes/`)
- `routes/web.php`: Mapa centralizado de navegación:
  - Rutas de acceso público: `/` (redirección a login o dashboard), `/login` (formulario y procesamiento).
  - Rutas protegidas por middleware `auth`: `/dashboard`, `/catalogos`, `/compras`, `/inventario`, `/produccion`, `/ventas`, `/finanzas`, `/seguridad` y `/logout`.

### 7. Interfaz de Usuario y Vistas Reactivas (`resources/`)
- `resources/views/app.blade.php`: Plantilla raíz Blade que carga las fuentes tipográficas institucionales (Fredoka e Inter), meta tags responsivos y el bundle compilado de Vite.
- `resources/css/app.css`: Configuración visual con Tailwind CSS v4, definición de tokens de color corporativo Gelawitto (`fresa`, `frambuesa`, `mora`, `durazno`, `rubor`, `nata`, `tinta`) y estilos para transiciones y desenfoques.
- `resources/js/app.ts`: Punto de entrada del cliente TypeScript que inicializa la aplicación Inertia con Vue 3.
- `resources/js/Layouts/ErpLayout.vue`: Marco maestro del panel ERP:
  - Barra lateral retráctil en tono mora con logotipo temático.
  - Barra superior con desenfoque de fondo (*backdrop blur*), indicador de rol y menú de usuario.
  - Selector nativo de modo claro / modo oscuro.
  - Alertas flotantes (Flash messages) para confirmaciones y errores.
- `resources/js/Pages/Auth/Login.vue`: Pantalla de autenticación diseñada en disposición split-screen (8/4):
  - Lateral izquierdo con fotografía artística de helados artesanales Gelawitto y lema corporativo.
  - Lateral derecho con formulario de credenciales, alternador de visualización de contraseña, contador regresivo de bloqueo tras fallos reiterados y credenciales de acceso rápido para pruebas.
- `resources/js/Pages/Erp/`:
  - `Dashboard.vue`: Tarjetas de KPI ejecutivos, alertas de abastecimiento urgente y accesos rápidos a módulos.
  - `Catalogos.vue`: Tablas de productos, materias primas, proveedores y clientes con filtros y modales de registro.
  - `Compras.vue`: Formulario de recepción de mercancía y tabla histórica de órdenes a proveedores.
  - `Inventario.vue`: Visualización del inventario valorizado y visor cronológico del libro de Kardex inmutable.
  - `Produccion.vue`: Diseñador de recetas técnicas y procesador de lotes de producción con cálculo de merma.
  - `Ventas.vue`: Interfaz de punto de venta con selector de productos, cálculo automático de total, apertura y arqueo de caja.
  - `Finanzas.vue`: Monitor de balance financiero, cuentas por pagar a proveedores y registro de egresos operativos.
  - `Seguridad.vue`: Directorio de operadores, asignación de roles y visor de bitácora de auditoría.

### 8. Pruebas Automatizadas y Aseguramiento de Calidad (`tests/`)
- `tests/Feature/ExampleTest.php`: Pruebas de integración en PHPUnit que validan la respuesta del login, el bloqueo de rutas a usuarios no autenticados, el acceso al dashboard con credenciales válidas y la consistencia transaccional.
- `tests/tema.test.ts`: Pruebas automatizadas en Vitest que aseguran la fidelidad de la paleta de colores institucional (`#e8436b`, `#b0244e`, `#4a1430`, etc.) y los elementos de seguridad de la pantalla de inicio de sesión.

### 9. Documentación y Entregables (`docs/`)
- `docs/entregable/bitacora-aportes.md`: Bitácora histórica con la trazabilidad de cada aporte, responsabilidades y aprendizajes técnicos de los integrantes del equipo.

