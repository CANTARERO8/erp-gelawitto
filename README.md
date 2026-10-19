# Gelawitto ERP

Sistema de gestión y administración para la heladería artesanal Gelawitto, diseñado para controlar operaciones de inventario, recetas, ventas y pedidos. Desarrollado con Next.js 16, React 19, TypeScript y Tailwind CSS v4 sobre Supabase.

---

## Arquitectura y Stack Tecnológico

- **Framework:** Next.js 16 (App Router con Turbopack)
- **Librería de interfaz:** React 19 y TypeScript
- **Estilos:** Tailwind CSS v4 y Base UI
- **Tipografías:** Fredoka (encabezados) e Inter (texto general)
- **Base de datos y backend:** Supabase
- **Suite de pruebas:** Vitest

---

## Identidad Visual y Paleta de Colores

El sistema adopta una paleta cromática propia inspirada en helados artesanales:

| Tono | Valor Hex | Función en el sistema |
| :--- | :--- | :--- |
| **Fresa** | `#e8436b` | Color primario y acciones principales |
| **Frambuesa** | `#b0244e` | Estados interactivos y enlaces destacados |
| **Mora** | `#4a1430` | Estructura de navegación y barra lateral |
| **Durazno** | `#ff9e80` | Acentos secundarios y avisos cálidos |
| **Rubor** | `#fde7ec` | Fondos de selección y elementos secundarios |
| **Nata** | `#fff9f7` | Fondo principal de la aplicación |
| **Tinta** | `#2a2126` | Tipografía principal y alto contraste |

---

## Puesta en Marcha e Instalación

### Requisitos previos

- Node.js 22 LTS o superior
- npm 10 o superior
- Git configurado localmente
- Acceso con credenciales al repositorio privado en GitHub

### Pasos de instalación

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/CANTARERO8/erp-gelawitto.git
   cd erp-gelawitto
   ```

2. Instalar dependencias del proyecto:
   ```bash
   npm install
   ```

3. Configurar variables de entorno:
   Copiar la plantilla de entorno hacia el archivo local:
   ```bash
   cp .env.example .env.local
   ```
   Completar los valores de conexión a Supabase dentro de `.env.local`:
   ```env
   NEXT_PUBLIC_SUPABASE_URL=https://tu-proyecto.supabase.co/rest/v1/
   NEXT_PUBLIC_SUPABASE_ANON_KEY=tu_clave_anonima_de_supabase
   ```

4. Iniciar el entorno de desarrollo:
   ```bash
   npm run dev
   ```
   Abrir en el navegador la dirección `http://localhost:3000`.

5. Ejecutar la suite de pruebas unitarias:
   ```bash
   npm test
   ```

6. Validar la compilación para producción:
   ```bash
   npm run build
   ```

---

## Guía de Colaboración y Flujo de Trabajo

El proyecto implementa un flujo de trabajo estructurado basado en ramas de características y solicitudes de extracción (Pull Requests) para garantizar la calidad del código y la trazabilidad de cada aporte.

### 1. Convención de ramas

Toda modificación debe realizarse en una rama específica derivada de `main`:

- `tarea/XX-nombre`: Tareas específicas o asignaciones de desarrollo (ejemplo: `tarea/01-modulo-inventario`).
- `feat/nombre-modulo`: Nuevas características o módulos del ERP (ejemplo: `feat/catalogo-sabores`).
- `fix/descripcion`: Correcciones puntuales de errores (ejemplo: `fix/calculo-merma`).
- `docs/descripcion`: Actualizaciones de documentación o bitácoras.

La rama `main` está reservada para versiones estables y nunca debe recibir commits directos.

### 2. Flujo de trabajo paso a paso

1. **Sincronizar el repositorio principal:**
   ```bash
   git checkout main
   git pull origin main
   ```

2. **Crear una rama de trabajo:**
   ```bash
   git checkout -b feat/nombre-funcionalidad
   ```

3. **Desarrollar la funcionalidad:**
   Implementar los cambios respetando la paleta institucional, las pautas de tipografía y el tipado estricto en TypeScript.

4. **Ejecutar validaciones locales:**
   Antes de confirmar cualquier cambio, asegurar que el proyecto compile y que todas las pruebas pasen:
   ```bash
   npm run lint
   npm test
   ```

5. **Registrar el aporte en la bitácora:**
   Abrir el archivo `docs/entregable/bitacora-aportes.md` y agregar una fila con los detalles del aporte: fecha, nombre del colaborador, tarea realizada, número de Pull Request, descripción del trabajo y aprendizaje obtenido.

6. **Confirmar los cambios con mensajes semánticos:**
   Seguir el estándar de Conventional Commits:
   - `feat:` para nuevas funcionalidades.
   - `fix:` para resolución de defectos.
   - `docs:` para cambios exclusivamente documentales.
   - `test:` para creación o modificación de pruebas.
   - `refactor:` para mejoras estructurales de código sin cambio de comportamiento.

   ```bash
   git add .
   git commit -m "feat: agrega formulario para registro de recetas"
   ```

7. **Publicar la rama en GitHub:**
   ```bash
   git push -u origin feat/nombre-funcionalidad
   ```

8. **Abrir una Solicitud de Extracción (Pull Request):**
   - Dirigirse al repositorio en GitHub y crear el PR hacia la rama `main`.
   - Incluir una descripción concisa de los cambios realizados, el impacto en el sistema y confirmación de que las pruebas pasaron localmente.

9. **Revisión de Código (Code Review):**
   - Al menos un colaborador debe revisar y aprobar el Pull Request antes del merge.
   - Tras la aprobación, se realiza la integración a `main`.

---

## Historial de Aportaciones

Para consultar las aportaciones realizadas a lo largo del ciclo de vida del proyecto, revisar la bitácora oficial en:

`docs/entregable/bitacora-aportes.md`

Todo miembro activo debe mantener actualizada dicha bitácora con cada contribución aceptada.

---

## Estructura del Repositorio

- `src/app/`: Enrutamiento, layouts y vistas de la aplicación Next.js.
- `src/components/ui/`: Componentes base del sistema de diseño.
- `src/lib/`: Utilidades generales, clientes y funciones auxiliares.
- `tests/`: Pruebas automatizadas de tema, componentes y lógica empresarial.
- `docs/entregable/`: Bitácora de aportes y documentación técnica del equipo.

---

## Comandos del Proyecto

- `npm run dev`: Inicia el servidor de desarrollo con Turbopack.
- `npm run build`: Compila y optimiza la aplicación para producción.
- `npm run start`: Inicia el servidor en modo producción local.
- `npm run test`: Ejecuta las pruebas unitarias con Vitest.
- `npm run lint`: Ejecuta el análisis de código con ESLint.
