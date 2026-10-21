# Bitácora de Aportes

Registro cronológico de aportaciones, aprendizaje y trazabilidad técnica del equipo en el desarrollo de Gelawitto ERP.

## Historial de Aportaciones

| Fecha | Nombre | Guía / Tarea | PR | Qué hice | Qué aprendí |
|---|---|---|---|---|---|
| 2026-09-29 | Moises Zelaya | Proyecto inicial | — | Creé el proyecto inicial de la interfaz con Next.js y Tailwind CSS (commit `a7885a6`). | Arrancar un proyecto web con Next.js y Tailwind CSS como base de la interfaz. |
| 2026-10-13 | Moises Zelaya | Incorporación a la bitácora | PR 1 (rama `tarea/00-MoisesZelaya`) | Agregué mi nombre a la bitácora (commit `1ad653a`) y se fusionó mi rama de tarea en la rama principal (commit `6b59b9e`). | Integrar cambios al repositorio por ramas de tarea y Pull Request. |
| 2026-10-14 | Moises Zelaya | Paleta Gelawitto y tipografías | — | Llevé la paleta Gelawitto a Tailwind (`645cb4f`) y agregué las tipografías Fredoka e Inter (`8902c33`). Creé la muestra de la paleta (`ec1b619`), verifiqué los colores y agregué los colores fresa y tinta (`b474c71`, `0462ca2`, `a0c410f`). Instalé las dependencias base: shadcn, sonner y vitest (`49c87ed`). | Definir tokens de color y tipografía en Tailwind para mantener una identidad visual consistente, y verificar los colores antes de aplicarlos a los componentes. |
| 2026-10-16 | Richard Malespin | Datos maestros del ERP | — | Diseñé los datos maestros del ERP para el caso del equipo. | Los datos maestros se definen antes de los módulos transaccionales, porque compras, inventario y producción dependen de ellos. |
| 2026-10-19 | Eduardo Cantarero | Inicialización ERP Gelawitto | — | Estructuración del proyecto desde cero, directrices de colaboración, guía de instalación y configuración de repositorio | Organización de repositorios para trabajo colaborativo y flujos de integración continua |
| 2026-10-19 | Eduardo Cantarero | Sistema Integral Gelawitto ERP | — | Implementé el modelo relacional en PostgreSQL 16, arquitectura modular por dominios (Modelos, Repositorios, Servicios, FormRequests, Resources y Controladores), interfaces operativas y suite de pruebas automatizadas | Arquitectura empresarial en capas con Laravel, Inertia.js, Vue 3 y transacciones atómicas en PostgreSQL |
| 2026-10-19 | Eduardo Cantarero | Pantalla de inicio de sesión | — | Ajusté la pantalla de login: quité el epígrafe y el recuadro flotante sobre la fotografía, optimicé la composición y actualicé el pie de página a Gelawitto Heladería (commits `234028d`, `b55aea2`, `b6f5b8d`). | Simplificar la composición visual de la pantalla de acceso, con menos elementos sobre la fotografía. |
| 2026-10-19 | Eduardo Cantarero | Logotipos institucionales y credenciales | — | Integré los logotipos institucionales de Gelawitto en formato SVG (`6e35b02`) y corregí la sintaxis XML para que se vean en navegadores (`9fa7632`). Quité las contraseñas expuestas de la interfaz de login y trasladé el catálogo de credenciales de prueba al manual técnico (`4d6a3c3`, `e21d541`). | Las credenciales de prueba no deben mostrarse en la interfaz. Se documentan en el manual técnico, fuera de la pantalla de acceso. |
| 2026-10-19 | Eduardo Cantarero | Pruebas automatizadas del ERP | — | Actualicé las pruebas del sistema (`6af6768`) y creé una suite de integración automatizada para transacciones y módulos del ERP (`3973ce5`). | Probar las transacciones de cada módulo de punta a punta, no solo las pantallas. |
| 2026-10-19 | Eduardo Cantarero | Arquitectura modular del ERP | — | Documenté las capas arquitectónicas y los componentes técnicos en el README (`9b4aea4`). Integré las rutas con controladores dedicados por módulo (`9a57dab`), creé controladores modulares desacoplados por dominio (`e22df9c`), API Resources para respuestas estructuradas (`d93244e`), validaciones tipadas de formularios (`4103c45`) y la capa de servicios con transacciones atómicas por dominio. | Separar cada módulo en rutas, controladores, recursos, validaciones y servicios para poder probarlo y mantenerlo por separado. |
| Por confirmar | Marlyng | Diseño de paleta en Figma e informe del segundo entregable | — | Trabajé en un diseño en Figma con la paleta de colores para el sistema y me encargué del informe del segundo entregable. | Por completar por la persona autora. |

## Guía de Registro para Colaboradores

Cada miembro del equipo debe registrar su aportación en esta bitácora dentro de la misma rama de trabajo de su Pull Request:

1. Añadir una nueva fila al final de la tabla antes de fusionar los cambios.
2. Formato de fecha: `AAAA-MM-DD`.
3. Especificar la tarea o módulo asignado con claridad.
4. Incluir el número o enlace del Pull Request correspondiente.
5. Describir de forma concisa qué funcionalidad o refactorización se realizó.
6. Compartir el aprendizaje o concepto técnico afianzado durante la tarea.
