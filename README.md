# Gelawitto ERP

Sistema de gestión interna para la heladería artesanal Gelawitto. Desarrollado con Next.js 16, React 19 y Tailwind CSS v4 sobre Supabase.

---

## Arquitectura y Stack Tecnológico

- **Framework:** Next.js 16 (App Router)
- **Librería de interfaz:** React 19, TypeScript
- **Estilos:** Tailwind CSS v4, Base UI
- **Tipografías:** Fredoka (titulares) e Inter (cuerpo de texto)
- **Base de datos y autenticación:** Supabase
- **Pruebas:** Vitest

---

## Identidad Visual y Paleta de Colores

El sistema utiliza una paleta cromática propia inspirada en helados artesanales:

| Tono | Valor Hex | Función en el sistema |
| :--- | :--- | :--- |
| **Fresa** | `#e8436b` | Color primario y acciones principales |
| **Frambuesa** | `#b0244e` | Estados hover y enlaces destacados |
| **Mora** | `#4a1430` | Estructura de navegación y menú lateral |
| **Durazno** | `#ff9e80` | Acentos secundarios y alertas cálidas |
| **Rubor** | `#fde7ec` | Fondos de selección y elementos secundarios |
| **Nata** | `#fff9f7` | Fondo principal de la aplicación |
| **Tinta** | `#2a2126` | Tipografía principal y contraste alto |

---

## Puesta en Marcha Local

### Requisitos previos

- Node.js 22 LTS o superior
- npm 10 o superior

### Instalación

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/zelayamoises725-star/Gelawitto.git
   cd Gelawitto
   ```

2. Instalar dependencias del proyecto:
   ```bash
   npm install
   ```

3. Configurar variables de entorno:
   Crear un archivo `.env.local` en la raíz con las credenciales de Supabase:
   ```env
   NEXT_PUBLIC_SUPABASE_URL=https://lydxzihvgawjscfsrwgo.supabase.co/rest/v1/
   NEXT_PUBLIC_SUPABASE_ANON_KEY=tu_clave_anonima_de_supabase
   ```

4. Iniciar el servidor de desarrollo:
   ```bash
   npm run dev
   ```
   Acceder a `http://localhost:3000` en el navegador.

---

## Comandos del Proyecto

- `npm run dev`: Inicia el servidor de desarrollo local.
- `npm run build`: Genera la compilación para producción.
- `npm run start`: Inicia el servidor en modo producción.
- `npm run test`: Ejecuta la suite de pruebas unitarias con Vitest.
- `npm run lint`: Analiza el código con ESLint.

---

## Estructura del Repositorio

- `src/app/`: Rutas, layouts y páginas de la aplicación Next.js.
- `src/components/ui/`: Componentes base del sistema de diseño.
- `src/lib/`: Utilidades compartidas y clientes de servicios.
- `tests/`: Pruebas automatizadas de diseño, componentes y lógica.
- `docs/entregable/`: Bitácora de aportes y registros del equipo de desarrollo.
