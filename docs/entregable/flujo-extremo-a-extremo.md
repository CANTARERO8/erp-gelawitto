# Flujo transaccional de extremo a extremo (requisito 13)

Evidencia del flujo **orden de compra → recepción de material → orden de producción → movimiento de inventario → venta → control de calidad → consolidación analítica en Dashboard** para el producto piloto Gelawitto, ejecutado sobre la arquitectura desacoplada de Gelawitto ERP (backend REST API en Laravel 11 y frontend SPA en Vue 3 + Vite).

Las operaciones se ejecutan a través de los endpoints REST de la API (`/api/compras`, `/api/produccion/lotes`, `/api/ventas`, `/api/calidad`, `/api/dashboard`) consumidos de forma reactiva por la interfaz de usuario, garantizando validaciones de tipado estricto, atomicidad en PostgreSQL y control de acceso basado en roles (`Administrador`, `Producción`, `Cajero`, `Contador`).

Todas las transacciones asignan folios atómicos inmutables gobernados por secuencias nativas de PostgreSQL (`folio_compras_seq`, `folio_lotes_seq`, `folio_ventas_seq`, `folio_turnos_seq`, `folio_calidad_seq`, `folio_finanzas_seq`, `folio_asientos_seq`), eliminando condiciones de carrera bajo concurrencia.

## 1. Orden de compra y recepción de material

La compra tiene dos pasos separados, cada uno con su propia acción en la API y en la interfaz:

1. **Crear orden de compra** (`POST /api/compras`): registra el pedido al proveedor con estado `Pendiente`. Asigna folio atómico `OC-XXXXXX`. No mueve stock, no genera movimientos en el Kardex ni en Finanzas.
2. **Recibir en almacén** (`POST /api/compras/{orden}/recibir`): elige la condición de pago (contado o crédito a 15 días) y, en una sola transacción atómica, incrementa el stock, recalcula el costo promedio ponderado del insumo, registra el movimiento `Entrada por Compra` en el Kardex y genera el egreso o la cuenta por pagar en Finanzas junto a su asiento contable de partida doble. La orden pasa a `Recibida`. Una orden no puede recibirse dos veces.

Órdenes de la BOM de Gelawitto:

| Folio | Proveedor | Insumo | Cantidad | Costo unitario | Total | Recepción |
|---|---|---|---|---|---|---|
| OC-2026-0003 | Empaques Térmicos BioPack | Vaso reciclable 100 mL con tapa | 50 ud | C$ 8.00 | C$ 400.00 | Lote inicial |
| OC-2026-0004 | Proveedor por definir (gelatina y agua) | Gelatina en polvo Great Value 40 g | 0.400 kg | C$ 1,125.00 | C$ 450.00 | Lote inicial |
| OC-2026-0005 | Proveedor por definir (gelatina y agua) | Agua potable | 10 L | C$ 1.00 | C$ 10.00 | Lote inicial |
| OC-2026-0006 | Empaques Térmicos BioPack | Vaso reciclable 100 mL con tapa | 40 ud | C$ 8.00 | C$ 320.00 | Segunda compra, recibida por API |
| OC-2026-0007 | Empaques Térmicos BioPack | Vaso reciclable 100 mL con tapa | 10 ud | C$ 8.00 | C$ 80.00 | Tercera compra, creada y recibida desde la pantalla |

Las órdenes OC-2026-0003 a 0005 se registraron con el flujo anterior, de un solo paso. Se conservan tal como quedaron en el Kardex.

Verificación del flujo separado con OC-2026-0006: al crearla, el stock de vasos se mantuvo en 40. Al recibirla, subió a 80 con un movimiento `Entrada por Compra` y un egreso de C$ 320 en Finanzas (`FIN-2026-009`). Un segundo intento de recepción fue rechazado y no duplicó nada.

## 2. Orden de producción y ejecución

La producción tiene dos pasos separados:

1. **Programar orden de producción** (`POST /api/produccion/lotes`): registra la orden con estado `Programada`, asigna folio atómico `LOT-XXXXXX`, la receta y la meta. No descuenta insumos ni suma producto, y su cantidad obtenida y costos quedan pendientes.
2. **Ejecutar lote** (`POST /api/produccion/lotes/{lote}/ejecutar`): con la cantidad real obtenida, descuenta los insumos de la receta escalados a la meta (0.040 kg de gelatina, 0.500 L de agua y 5 vasos por lote de 5), calcula el costo y la merma, e ingresa el producto terminado al Kardex con el movimiento `Ingreso de Producción`. La orden pasa a `Finalizado`. Una orden no puede ejecutarse dos veces, y si faltan insumos al ejecutarla, la API lo rechaza sin descontar nada.

| Lote | Receta | Programado | Obtenido | Merma | Costo del lote | Costo por vaso | Cómo se registró |
|---|---|---|---|---|---|---|---|
| LOT-2026-003 | Gelawitto tradicional | 5 | 5 | 0 | C$ 85.50 | C$ 17.10 | Flujo anterior, de un solo paso |
| LOT-2026-004 | Gelawitto tradicional | 5 | 4 | 1 | C$ 85.50 | C$ 21.38 | Flujo anterior, de un solo paso |
| LOT-2026-005 | Gelawitto tradicional | 5 | 5 | 0 | C$ 85.50 | C$ 17.10 | Programada por API y ejecutada por API |
| LOT-2026-006 | Gelawitto tradicional | 5 | 5 | 0 | C$ 85.50 | C$ 17.10 | Programada y ejecutada desde la pantalla |

Verificación del flujo separado con LOT-2026-005: al programarla, los insumos y el stock de producto se mantuvieron igual. Al ejecutarla, descontó 0.040 kg de gelatina, 0.500 L de agua y 5 vasos, y el producto pasó de 4 a 9 unidades. El costo vigente quedó en C$ 17.94, que es el promedio ponderado de 4 unidades a C$ 19.00 y 5 unidades a C$ 17.10. Un segundo intento de ejecución fue rechazado.

## 3. Movimientos de inventario (Kardex)

| Referencia | Tipo de movimiento | Artículo | Cantidad | Saldo resultante |
|---|---|---|---|---|
| OC-2026-0003 | Entrada por Compra | Vaso reciclable 100 mL con tapa | +50.000 | 50.000 |
| OC-2026-0004 | Entrada por Compra | Gelatina en polvo Great Value 40 g | +0.400 | 0.400 |
| OC-2026-0005 | Entrada por Compra | Agua potable | +10.000 | 10.000 |
| LOT-2026-003 | Consumo de Producción | Gelatina / Agua / Vasos | -0.040 / -0.500 / -5.000 | 0.360 / 9.500 / 45.000 |
| LOT-2026-003 | Ingreso de Producción | Gelawitto tradicional | +5.000 | 5.000 |
| LOT-2026-004 | Consumo de Producción | Gelatina / Agua / Vasos | -0.040 / -0.500 / -5.000 | 0.320 / 9.000 / 40.000 |
| LOT-2026-004 | Ingreso de Producción | Gelawitto tradicional | +4.000 | 9.000 |
| VTA-2026-0003 | Salida por Venta | Gelawitto tradicional | -3.000 | 6.000 |
| VTA-2026-0004 | Salida por Venta | Gelawitto tradicional | -2.000 | 4.000 |

## 4. Venta y despacho

| Folio | Cliente | Cantidad | Método de pago | Total |
|---|---|---|---|---|
| VTA-2026-0003 | Cliente de Mostrador | 3 | Efectivo | C$ 300.00 |
| VTA-2026-0004 | Cafetería y Pastelería El Mirador | 2 | Transferencia | C$ 200.00 |

Cada venta (`POST /api/ventas`) requiere un turno de caja abierto (`POST /api/ventas/turnos/abrir`, folio `TRN-XXXXXX`), asigna folio atómico inmutable `VEN-XXXXXX`, descuenta el stock del producto en el Kardex (`Salida por Venta`), actualiza los acumulados del turno, asienta el ingreso en Finanzas y genera el asiento contable de partida doble (`AS-XXXXXX`).

## 5. Cambios respecto al diseño original

Con la separación de la orden de compra (sección 1) y de la orden de producción (sección 2), el flujo sigue la secuencia de cinco pasos con dos mejoras arquitectónicas clave:

1. **Costo unitario del producto por promedio ponderado:** Al producir, el costo vigente del producto se recalcula como promedio ponderado entre el inventario existente y el lote nuevo. Antes se tomaba el costo del último lote. Con los dos lotes anteriores, el costo vigente pasó de C$ 21.38 a **C$ 19.00** ((5 × 17.10 + 85.50) / 9). Los costos ya registrados en el Kardex y en las ventas no se modifican.
2. **Folios atómicos inmutables en PostgreSQL:** Se sustituyeron las consultas `Model::count() + 1` por 7 secuencias nativas de PostgreSQL con función atómica `nextval(...)` (`folio_compras_seq`, `folio_lotes_seq`, `folio_ventas_seq`, `folio_turnos_seq`, `folio_finanzas_seq`, `folio_calidad_seq`, `folio_asientos_seq`), blindando el flujo contra colisiones de folios bajo múltiples operadores simultáneos.

## 6. Calidad y trazabilidad por lote (requisito 14)

Cada lote ejecutado pasa por una inspección de calidad antes de considerarse liberado. La API (`POST /api/calidad`) y la pantalla **Calidad y Trazabilidad** registran la inspección (folio atómico `CAL-XXXXXX`) y aplican una de tres disposiciones:

| Disposición | Cuándo aplica | Efecto en inventario |
|---|---|---|
| **Liberación** | Sin unidades no conformes | Ninguno. Las unidades quedan disponibles en inventario vendible. |
| **Retrabajo** | Hay unidades no conformes que se corregirán | Retira esas unidades del stock vendible y registra `Retrabajo por Calidad` en el Kardex. |
| **Scrap** | Hay unidades no conformes que se desechan | Da de baja esas unidades, registra `Scrap por Calidad` en el Kardex y calcula el costo perdido. |

Reglas de negocio: solo se inspeccionan lotes ya ejecutados, cada lote admite una única inspección, no se puede liberar un lote con unidades no conformes, la no conformidad debe describirse, y no se puede retirar más stock del disponible.

Evidencia de la demostración:

| Lote | Inspección | Unidades no conformes | Disposición | Costo perdido | Estado de calidad |
|---|---|---|---|---|---|
| LOT-2026-005 | CC-2026-001 | 0 de 5 | Liberación | C$ 0.00 | Liberado |
| LOT-2026-006 | CC-2026-002 | 1 de 5 (gelificación incompleta en 1 vaso) | Scrap | C$ 17.64 | Scrap |

Al dar de baja una unidad de `LOT-2026-006`, el stock de producto pasó de 14 a 13 y quedó en el Kardex con referencia del lote. Un segundo intento de inspección de `LOT-2026-005` fue rechazado.

**Trazabilidad por lote:** La pantalla muestra, para cada lote, los insumos que consumió (leídos del Kardex por referencia de lote), las unidades obtenidas, el estado de calidad y cada inspección con su disposición. Así se va del lote hacia atrás, a los insumos, y hacia adelante, al resultado de la inspección.

## 7. Consolidación Analítica en Tiempo Real en Dashboard (Rentabilidad y MRP)

El flujo transaccional anterior no termina en el registro contable: alimenta directamente el cuadro de mando analítico en tiempo real a través del endpoint `GET /api/dashboard` y la vista nativa de alto rendimiento en Vue 3 + Apache ECharts (`Dashboard.vue`).

### Control Financiero y Rentabilidad Comercial Real
1. **Ingreso y Utilidad Neta:** Sobre las transacciones de venta completadas se calcula el ingreso total acumulado (C$ 8,605.00), el costo real de materias primas consumidas (C$ 3,217.75) y la utilidad bruta neta (C$ 5,387.25), arrojando un margen bruto promedio global del 62.6%.
2. **Sabor Más Rentable:** El producto con mayor retorno es identificado automáticamente (Gelato Fior di Nata Clásico con 64.9% de margen).
3. **Curva de Pareto:** Gráfico de Pareto (80/20) que correlaciona los ingresos por producto y el porcentaje acumulado, permitiendo focalizar la producción en los sabores de mayor rotación y margen.
4. **Costeo de Recetas Maestras vs Precio de Venta:** Comparativa visual y tabular del costo unitario calculado por fórmula contra el precio de venta al público en mostrador.

### Abastecimiento y Planificación de Compras (MRP)
1. **Monitoreo de Inventario y Consumo Diario:** Cruce de saldos de Kardex de insumos contra el consumo real de los últimos 30 días para determinar los días de inventario disponible por materia prima.
2. **Semáforo Operativo de Reorden:** Clasificación automática de cada insumo en tres estados:
   - `Reordenar`: Stock actual por debajo del stock mínimo de seguridad (detectados 2 insumos críticos: Frambuesa silvestre IQF con 6.5 kg frente a 10 kg mínimo, y Pulpa de durazno con 8 kg frente a 10 kg mínimo).
   - `Vigilar`: Insumos con cobertura menor a 14 días.
   - `Suficiente`: Insumos con stock holgado para la demanda proyectada.
3. **Presupuesto Sugerido de Compra a 14 Días:** Cálculo automatizado de la cantidad y valor monetario sugerido a comprar por insumo y por proveedor habitual (C$ 5,032.50 requeridos para restablecer stock de seguridad y cubrir 14 días de producción).
4. **Histórico Cronológico de Precios de Compra:** Visualización de tendencias de costo unitario por fecha real de emisión de órdenes de compra (27 y 28 de octubre).

## 8. Integración IIoT del refrigerador (requisito 15)

El refrigerador de los lotes cuenta con monitoreo continuo de temperatura. Opera mediante un dispositivo físico (DS18B20 con ESP32) o a través del comando simulador integrado en Laravel, utilizando el mismo contrato de ingesta:

**Flujo:**
1. El sensor (o el simulador) envía la temperatura a `POST /api/iiot/lecturas` con el encabezado `X-IIoT-Token`.
2. La API guarda la lectura en `lecturas_temperatura` y evalúa los umbrales dinámicos gobernados en PostgreSQL (`refrigerador_temp_min` = 2.0 °C y `refrigerador_temp_max` = 6.0 °C en la tabla `parametros`).
3. Si la lectura está fuera de rango, los lotes ejecutados que esperan inspección pasan automáticamente a estado de calidad `Retenido` y se registra una alerta en la bitácora.
4. Calidad inspecciona los lotes retenidos y decide su disposición (Liberación, Retrabajo o Scrap).

**Contrato del dispositivo:**
```json
POST /api/iiot/lecturas
X-IIoT-Token: <token configurado>
Content-Type: application/json

{ "temperatura": 4.2, "dispositivo": "REF-01", "fecha_lectura": "2026-10-21 04:50:00" }
```
Respuesta `201`: `{ "id", "temperatura", "fuera_de_rango", "lotes_retenidos" }`. Sin token o con token incorrecto: `401`. Temperatura no numérica: `422`.

**Simulador:**
```bash
cd erp-gelawitto-api
php artisan iiot:simular --lecturas=10 --intervalo=60
php artisan iiot:simular --lecturas=5 --excursion
```

## 9. Cómo reproducir la evidencia

La verificación integral del flujo se reproduce de forma automatizada en ambas capas desacopladas del ERP:

### 1. Pruebas Automatizadas de Backend (Laravel 11)
```bash
cd erp-gelawitto-api

# Ejecutar la suite completa de 45 pruebas (404 aserciones)
php artisan test

# O ejecutar específicamente las pruebas del flujo transaccional
php artisan test --filter=ComprasRecepcionTest
php artisan test --filter=ProduccionOrdenTest
php artisan test --filter=ProduccionCostoPromedioTest
php artisan test --filter=CalidadTest
php artisan test --filter=IiotTest
php artisan test --filter=DashboardControllerTest
```

### 2. Pruebas Unitarias y Compilación del Frontend (Vue 3 + Vite)
```bash
cd ../erp-gelawitto-ui

# Pruebas unitarias de componentes y temas (Vitest)
npm run test

# Compilación estricta y chequeo de tipos TypeScript
npm run build
```

### 3. Verificación Automatizada en Navegador Real (Playwright + Firefox)
```bash
cd ../erp-gelawitto-ui
node tests/e2e.mjs
```

## 10. Pendientes y Evolución del Diseño

- **Lote de Proveedor en Compras:** Incorporar el registro del número de lote del proveedor en la recepción de materia prima para extender la trazabilidad hacia el origen externo.
- **Trazabilidad Lote-Cliente:** Hoy las ventas descuentan del stock general del producto. Se proyecta asignar el lote de salida en el punto de venta para trazabilidad hacia el consumidor final.
- **Ventana Temporal de Retención IIoT:** Acotar la retención térmica a los lotes ingresados al refrigerador dentro de una ventana móvil de 24 horas, evitando retener lotes de demostración previos.
