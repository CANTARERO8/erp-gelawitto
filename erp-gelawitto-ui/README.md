# ERP Gelawitto UI (Frontend SPA Desacoplado)

Aplicacion Frontend SPA independiente construida con Vue 3, Vite, Vue Router 4, Axios y Tailwind CSS v4 para el sistema ERP Gelawitto, estructurada bajo el estandar arquitectonico desacoplado de Carnes San Martin (`SM_LOG_UI`).

## Estructura del Proyecto (`src/`)

- `src/plugins/axios.ts`: Instancia centralizada de Axios (`@axios`) con credenciales, cookies de sesion (`withCredentials`, `withXSRFToken`) e interceptores de respuesta para redireccion automatica en caso de 401.
- `src/services/apiService.ts`: Factoria de servicios REST por recurso (`get`, `post`, `put`, `delete`) conectada directamente a la API de Laravel.
- `src/composables/useAuth.ts`: Composable reactivo de estado global para usuario autenticado, roles, permisos por modulo cargados desde PostgreSQL (`/api/me`) y parametros globales del sistema (`iva_porcentaje`).
- `src/composables/useFormulario.ts`: Composable reactivo para gestion de formularios, reseteo, errores de validacion del API y recarga automatica de datos tras operaciones exitosas.
- `src/router/index.ts`: Enrutador SPA con Vue Router 4, guardias de autenticacion, control de acceso por modulo basado en base de datos y renderizado reactivo de vistas con sincronizacion de endpoints.
- `src/Layouts/ErpLayout.vue`: Layout corporativo del ERP con barra lateral dinamica (construida a partir de los modulos autorizados en PostgreSQL), soporte para modo claro/oscuro y notificaciones tipo toast.
- `src/Pages/`: Vistas de usuario por modulo de negocio (`Auth/Login.vue`, `Erp/Dashboard.vue`, `Erp/Catalogos.vue`, `Erp/Compras.vue`, `Erp/Inventario.vue`, `Erp/Produccion.vue`, `Erp/Calidad.vue`, `Erp/Ventas.vue`, `Erp/Finanzas.vue`, `Erp/Seguridad.vue`).

## Variables de Entorno

- `.env.development`: Configuracion de entorno local con proxy `/api` hacia `http://127.0.0.1:8000`.
- `.env.example`: Plantilla de variables de entorno para frontend.

## Ejecucion y Pruebas

1. Iniciar servidor de desarrollo (puerto 5174):
   ```bash
   npm install
   npm run dev
   ```
2. Compilar paquete para produccion (`dist/`):
   ```bash
   npm run build
   ```
3. Ejecutar pruebas unitarias automatizadas con Vitest:
   ```bash
   npm test
   ```
