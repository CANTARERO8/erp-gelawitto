# ERP Gelawitto UI (Frontend SPA Desacoplado)

Aplicación Frontend SPA independiente construida con Vue 3, Vite, Vue Router, Axios y Tailwind CSS v4 para el sistema ERP Gelawitto, estructurada bajo el estándar de arquitectura de Carnes San Martín (`SM_LOG_UI`).

## Estructura del Proyecto (`src/`)

- `src/plugins/axios.ts`: Instancia centralizada de Axios (`@axios`) con interceptores de solicitud (token Bearer, cabeceras CSRF y cliente SPA) e interceptores de respuesta (manejo de sesión expirada 401).
- `src/services/apiService.ts`: Factoría de servicios REST por recurso (`get`, `post`, `put`, `delete`).
- `src/services/api.ts`: Cliente de sincronización de vistas y acciones con el Backend Laravel.
- `src/navigation/vertical/`: Definición modular del menú lateral dividida por dominios (`dashboard.ts`, `operaciones.ts`, `comercial.ts`, `finanzas.ts`, `index.ts`).
- `src/router/index.ts`: Enrutador SPA (`vue-router`) con carga dinámica de vistas (`lazy-loading`).
- `src/Layouts/ErpLayout.vue`: Layout corporativo principal del ERP.
- `src/Pages/`: Módulos de interfaz (`Auth`, `Dashboard`, `Catalogos`, `Inventario`, `Produccion`, `Calidad`, `Compras`, `Ventas`, `Finanzas`).

## Variables de Entorno

- `.env.development`: Apunta al proxy local `/erp-api` hacia `http://127.0.0.1:8000`.
- `.env.production`: Apunta al dominio productivo del API (`VITE_API_URL`).

## Ejecución en Desarrollo

1. Asegúrate de tener el Backend corriendo en el puerto `8000`:
   ```bash
   php artisan serve --port=8000
   ```
2. Instala las dependencias e inicia el servidor de desarrollo de `erp-gelawitto-ui` (puerto `5174`):
   ```bash
   npm install
   npm run dev
   ```
3. Para compilar el bundle estático de producción (`dist/`):
   ```bash
   npm run build
   ```
