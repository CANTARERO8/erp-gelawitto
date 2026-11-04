# Flujo transaccional de extremo a extremo y trazabilidad FEFO (requisito 13)

Evidencia del flujo **orden de compra con lote de proveedor → recepción de material → producción multinivel (BOM de 2 niveles) → rutas y centros de trabajo (OEE y Tiempo de Ciclo) → control de calidad e integración IIoT → venta con asignación FEFO → consolidación analítica en Dashboard** para la línea de postres funcionales de gelatina a base de arroz **Gelawitto**, ejecutado sobre la arquitectura desacoplada de Gelawitto ERP (backend REST API en Laravel 13 y frontend SPA en Vue 3 + Vite + PostgreSQL 16).

El diseño operativo y la jerarquía de datos siguen el estándar de integración entre sistemas empresariales y de control de planta ANSI/ISA-95 (International Society of Automation, 2010), acoplando la planificación de requerimientos de materiales y listas de materiales multinivel (Vollmann et al., 2005; Heizer & Render, 2020) con la trazabilidad alimentaria ascendente y descendente (Comisión del Codex Alimentarius, 2020) y la medición de Eficiencia Global de Equipos (Nakajima, 1988).

Todas las transacciones asignan folios atómicos inmutables gobernados por secuencias nativas de PostgreSQL (`folio_compras_seq`, `folio_lotes_seq`, `folio_ventas_seq`, `folio_turnos_seq`, `folio_calidad_seq`, `folio_finanzas_seq`, `folio_asientos_seq`), eliminando condiciones de carrera bajo concurrencia.

---

## 1. Depuración de Catálogo y Estructura BOM de 2 Niveles con Balance de Masa

Se depuraron en su totalidad los registros de prueba de heladería (`SELECT count(*) FROM productos WHERE sku LIKE 'GEL-%'` = `0`) y se estructuró el catálogo industrial de **Gelawitto** con **3 productos terminados** (`tipo = 'terminado'`), **1 subensamble intermedio** (`tipo = 'semielaborado'`) y **10 insumos** de materia prima y empaque, garantizando coherencia matemática estricta en el balance de masa y volumen (Heizer & Render, 2020; Vollmann et al., 2005):

### Nivel 1 → Nivel 2: Subensamble Intermedio (`SUB-GEL-BASE`)
- **SKU:** `SUB-GEL-BASE` — *Base Gelificada de Arroz (Subensamble)* (`semielaborado`, unidad: Litro `L`).
- **Rendimiento por batch:** `1.000 L` (500 mL de agua en cocción coloidal + 500 mL de agua fría en choque térmico = `1.000 L`).
- **Componentes de Nivel 2 por cada `1.000 L` de base:**

| Código | Insumo (Nivel 2) | Cantidad por `1.000 L` | Costo Unitario | Subtotal |
|---|---|---:|---:|---:|
| `INS-001` | Harina de arroz micropulverizada grado alimenticio | `0.100 kg` | C$ 45.00 / kg | C$ 4.50 |
| `INS-002` | Gelatina sin sabor de alto bloom (Great Value) | `0.080 kg` | C$ 1,125.00 / kg | C$ 90.00 |
| `INS-003` | Agua purificada por ósmosis inversa | `1.000 L` | C$ 1.00 / L | C$ 1.00 |
| `INS-004` | Azúcar blanca refinada | `0.050 kg` | C$ 40.00 / kg | C$ 2.00 |
| **Total Nivel 2** | **Costo estándar por Litro de `SUB-GEL-BASE`** | **`1.000 L`** | — | **C$ 97.50 / L** |

### Nivel 0 → Nivel 1: Tres Productos Terminados (`GLW-*`)
Cada receta de producto terminado produce un lote base de **5 vasos de 100 mL** (`0.500 L` de volumen neto por lote):

| SKU (Nivel 0) | Producto Terminado | Composición Nivel 1 (por lote de 5 vasos) | Costo Lote | Costo Unitario | Precio Venta | Margen Bruto |
|---|---|---|---:|---:|---:|---:|
| `GLW-TRA-100` | **Gelawitto Tradicional de Arroz** | `0.500 L` `SUB-GEL-BASE` (C$ 48.75) + `0.005 L` Vainilla y canela `INS-007` (C$ 1.60) + `5 ud` Vaso 100 mL `INS-008` (C$ 40.00) + `5 ud` Etiqueta `INS-009` (C$ 7.50) + `1 ud` Bandeja x5 `INS-010` (C$ 6.00) | C$ 103.85 | **C$ 20.77** | C$ 100.00 | **79.2%** |
| `GLW-FRU-100` | **Gelawitto con Frutas Variadas** | `0.400 L` `SUB-GEL-BASE` (80 mL/vaso = C$ 39.00) + `0.100 kg` Mix de frutas `INS-005` (20 g/vaso = C$ 15.00) + `5 ud` Vaso `INS-008` (C$ 40.00) + `5 ud` Etiqueta `INS-009` (C$ 7.50) + `1 ud` Bandeja `INS-010` (C$ 6.00) | C$ 107.50 | **C$ 21.50** | C$ 115.00 | **81.3%** |
| `GLW-LCO-100` | **Gelawitto Tres Leches y Arroz** | `0.400 L` `SUB-GEL-BASE` (80 mL/vaso = C$ 39.00) + `0.100 kg` Leche condensada `INS-006` (20 g/vaso = C$ 13.50) + `0.005 L` Vainilla `INS-007` (C$ 1.60) + `5 ud` Vaso `INS-008` (C$ 40.00) + `5 ud` Etiqueta `INS-009` (C$ 7.50) + `1 ud` Bandeja `INS-010` (C$ 6.00) | C$ 107.60 | **C$ 21.52** | C$ 120.00 | **82.1%** |

---

## 2. Orden de compra, recepción y trazabilidad de lote de proveedor (`lotes_insumo`)

La compra opera en dos pasos desacoplados y registra para cada línea el **lote de origen del proveedor** (`lote_proveedor`) y su **fecha de vencimiento** (`fecha_vencimiento`), creando registros trazables en `lotes_insumo` al recibir la mercancía en almacén (Chopra & Meindl, 2019; Comisión del Codex Alimentarius, 2020):

1. **Crear orden de compra** (`POST /api/compras`): registra el pedido con estado `Pendiente`, folio atómico `OC-XXXXXX`, `lote_proveedor` y `fecha_vencimiento` por línea. No mueve inventario ni genera asientos contables.
2. **Recibir en almacén** (`POST /api/compras/{orden}/recibir`): incrementa el stock, recalcula el costo promedio ponderado del insumo, crea el registro en `lotes_insumo` con su `cantidad_disponible` para consumo FEFO, registra `Entrada por Compra` en el Kardex y genera la transacción financiera y el asiento contable de partida doble.

Órdenes de compra registradas en el sistema (las fechas de vencimiento son relativas a la fecha de carga del semillero):

| Folio | Proveedor | Insumos y lotes de proveedor (`lote_proveedor`) | Vencimiento | Total | Condición |
|---|---|---|---|---:|---|
| `OC-2026-0001` | Agroindustria Arrocera de Sébaco S.A. | `INS-001` Harina de arroz (`30`, lote `LPR-ARR-2601`); `INS-004` Azúcar (`34.875`, lote `LPR-AZU-2601`) | 2027-03-04, 2027-05-03 | C$ 2,745.00 | Contado |
| `OC-2026-0002` | Distribuidora Alimentaria del Valle S.A. | `INS-002` Gelatina (`6`, lote `LPR-GEL-2601`); `INS-003` Agua (`150`, lote `LPR-AGU-2601`); `INS-006` Leche condensada (`10`, lote `LPR-LCO-2601`); `INS-007` Vainilla (`4.688`, lote `LPR-VAI-2601`) | 2027-01-03, 2027-02-02, 2027-04-03, 2027-05-03 | C$ 9,750.00 | Contado |
| `OC-2026-0003` | Frutas Selectas de la Montaña | `INS-005` Mix de frutas (`8`, lote `LPR-FRU-2601`) | 2026-11-16 | C$ 1,200.00 | Contado |
| `OC-2026-0004` | Empaques Térmicos BioPack | `INS-008` Vaso 100 mL (`250`, lote `LPR-VAS-2601`); `INS-009` Etiqueta (`300`, lote `LPR-ETI-2601`); `INS-010` Bandeja x5 (`70.833`, lote `LPR-BAN-2601`) | 2027-11-04 | C$ 2,875.00 | Crédito |

---

## 3. Centros de Trabajo, Rutas de Operación, OEE y Tiempo de Ciclo

Se modelaron en PostgreSQL las tablas `centros_trabajo` y `rutas_operacion` para gobernar las secuencias productivas y los tiempos estándar de preparación (`tiempo_preparacion_min`) y de ciclo unitario (`tiempo_ciclo_min_por_unidad`), permitiendo calcular el **Tiempo de Ciclo** y la **Eficiencia Global de Equipos (OEE)** conforme a la formulación de Nakajima (1988):

$$\text{OEE} = \text{Disponibilidad} \times \text{Rendimiento} \times \text{Calidad}$$

### Centros de Trabajo (`centros_trabajo`)

| Código | Centro de Trabajo | Capacidad / Hora |
|---|---|---|
| `CT-01` | Estación de Cocción e Hidratación Coloidal | `40.00 unidades/h` |
| `CT-02` | Mesa de Mezclado, Dosificación y Envasado | `120.00 unidades/h` |
| `CT-03` | Cámara de Refrigeración y Gelificación Controlada (REF-01) | `200.00 unidades/h` |
| `CT-04` | Estación de Inspección de Calidad y Etiquetado | `150.00 unidades/h` |

El esquema no guarda costo por hora de centro de trabajo; el costo de los lotes proviene de la BOM (materiales y empaque).

### Ejecución de Lotes, Consumo FEFO de Subensamble y Métricas OEE (`GET /api/produccion/oee`)

Al ejecutar un lote de producto terminado (`POST /api/produccion/lotes/{lote}/ejecutar`), `ProduccionService` descuenta bajo criterio **FEFO** tanto los lotes de materia prima/empaque (`lotes_insumo`) como el lote del subensamble intermedio (`SUB-GEL-BASE` desde `LOT-2026-001`, de 20.000 L), registrando la trazabilidad exacta en `consumos_lote`:

| Lote | Producto | Obtenido | Conformes | T. Plan. (min) | T. Real (min) | Paro (min) | Ciclo Est. (min/ud) | Ciclo Real (min/ud) | Disp. | Rend. | Calidad | OEE |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| `LOT-2026-001` | Base Gelificada de Arroz (Subensamble) | 20 | 20 | 415 | 415 | 5 | 20.75 | 20.50 | 98.8% | 100% | 100% | **98.8%** |
| `LOT-2026-002` | Gelawitto Tradicional de Arroz | 30 | 30 | 193 | 200 | 8 | 6.43 | 6.40 | 96% | 100% | 100% | **96%** |
| `LOT-2026-003` | Gelawitto con Frutas Variadas | 35 | 35 | 240 | 220 | 10 | 6.86 | 6.00 | 95.5% | 100% | 100% | **95.5%** |
| `LOT-2026-004` | Gelawitto Tres Leches y Arroz | 36 | 36 | 237.2 | 220 | 6 | 6.59 | 5.94 | 97.3% | 100% | 100% | **97.3%** |
| `LOT-2026-005` | Gelawitto Tradicional de Arroz | 25 | 25 | 163 | 175 | 5 | 6.52 | 6.80 | 97.1% | 95.9% | 100% | **93.1%** |
| `LOT-2026-006` | Gelawitto con Frutas Variadas | 10 | 9 | 80 | 105 | 5 | 8.00 | 10.00 | 95.2% | 80% | 90% | **68.6%** |
| **Promedio Global** | **Consolidado de planta (6 lotes)** | — | — | — | — | — | **9.19** | **9.27** | **96.7%** | **96%** | **98.3%** | **91.2%** |

---

## 4. Calidad, Integración IIoT y Trazabilidad Bidireccional FEFO

Cada lote ejecutado pasa por una inspección en `POST /api/calidad` (folios `CC-2026-001` a `CC-2026-006`). Cuando el sensor de la cámara fría `REF-01` reportó en `POST /api/iiot/lecturas` una excursión térmica de **`8.90 °C`** (superando el umbral máximo de `6.0 °C`), el lote `LOT-2026-006` fue retenido e inspeccionado bajo el folio `CC-2026-006`, dictaminando la disposición de **Scrap** para `1` vaso con sinéresis superficial (pérdida de **C$ 21.50** en Kardex).

| Lote | Producto | Vencimiento | Inspección | No Conformes | Disposición | Costo Perdido | Estado |
|---|---|---|---|---:|---|---:|---|
| `LOT-2026-001` | Base Gelificada de Arroz (Subensamble) | 2026-11-14 | `CC-2026-001` | 0 de 20 L | Liberación | C$ 0.00 | Liberado |
| `LOT-2026-002` | Gelawitto Tradicional de Arroz | 2026-11-16 | `CC-2026-002` | 0 de 30 | Liberación | C$ 0.00 | Liberado |
| `LOT-2026-003` | Gelawitto con Frutas Variadas | 2026-11-15 | `CC-2026-003` | 0 de 35 | Liberación | C$ 0.00 | Liberado |
| `LOT-2026-004` | Gelawitto Tres Leches y Arroz | 2026-11-18 | `CC-2026-004` | 0 de 36 | Liberación | C$ 0.00 | Liberado |
| `LOT-2026-005` | Gelawitto Tradicional de Arroz | 2026-11-19 | `CC-2026-005` | 0 de 25 | Liberación | C$ 0.00 | Liberado |
| `LOT-2026-006` | Gelawitto con Frutas Variadas | 2026-11-18 | `CC-2026-006` | 1 de 10 | **Scrap** | **C$ 21.50** | Scrap |

---

## 5. Venta y Despacho con Asignación Automática FEFO (`lote_produccion_id`)

Al registrar una venta (`POST /api/ventas`), `VentasService` selecciona automáticamente los lotes de producción con `estado_calidad = 'Liberado'` y `cantidad_disponible > 0` ordenados por **`fecha_vencimiento ASC NULLS LAST, id ASC`** (criterio *First Expired, First Out* — FEFO). Si la cantidad demandada supera el remanente del lote más próximo a vencer, el servicio divide automáticamente la línea de venta entre los lotes necesarios y vincula cada `venta_lineas.lote_produccion_id`. Los lotes en estado `Scrap` (`LOT-2026-006`) o `Retenido` quedan bloqueados para despacho comercial.

| Folio Venta | Cliente | Productos Despachados | Lotes asignados (FEFO) | Método de Pago | Total |
|---|---|---|---|---|---:|
| `VTA-2026-0001` | Cliente de Mostrador | `8 ud` Gelawitto con Frutas Variadas + `10 ud` Gelawitto Tradicional de Arroz | LOT-2026-002 (vence 2026-11-16) / LOT-2026-003 (vence 2026-11-15) | Efectivo | C$ 1,920.00 |
| `VTA-2026-0002` | Cafetería y Pastelería El Mirador | `12 ud` Gelawitto con Frutas Variadas + `10 ud` Gelawitto Tres Leches y Arroz | LOT-2026-003 (vence 2026-11-15) / LOT-2026-004 (vence 2026-11-18) | Tarjeta | C$ 2,580.00 |
| `VTA-2026-0003` | Kiosco Estudiantil y Comedor Saludable UNI | `15 ud` Gelawitto Tradicional de Arroz + `8 ud` Gelawitto Tres Leches y Arroz | LOT-2026-002 (vence 2026-11-16) / LOT-2026-004 (vence 2026-11-18) | Transferencia | C$ 2,460.00 |
| `VTA-2026-0004` | Cafetería y Pastelería El Mirador | `5 ud` Gelawitto con Frutas Variadas + `5 ud` Gelawitto Tradicional de Arroz | LOT-2026-003 (vence 2026-11-15) / LOT-2026-005 (vence 2026-11-19) | Efectivo | C$ 1,075.00 |

---

## 6. Consolidación Analítica en Tiempo Real en Dashboard (Rentabilidad, MRP y OEE)

El endpoint `GET /api/dashboard` y la vista nativa `Dashboard.vue` consolidan en tiempo real tres bloques analíticos sobre PostgreSQL:

1. **Control Financiero y Rentabilidad Comercial Real:**
   - **Ingreso Total Acumulado:** `C$ 8,035.00` sobre las 4 ventas completadas.
   - **Costo Real de Ventas (BOM 2 Niveles):** `C$ 1,547.96`.
   - **Utilidad Bruta Neta:** `C$ 6,487.04`, con un **margen bruto promedio global de `80.7%`**. El margen considera materiales y empaque; no incluye mano de obra ni energía.
2. **Abastecimiento y Planificación de Compras (MRP a 14 días):**
   - **Insumos en Estado Crítico (`Reordenar`):** `2` insumos por debajo del stock mínimo: `INS-006` Leche condensada azucarada pasteurizada (`9.280` vs `10.000` mínimo); `INS-005` Mix de frutas naturales en cubos (`7.100` vs `10.000` mínimo).
   - **Presupuesto Sugerido de Compra (14 días):** `C$ 3,379.50`.
   - **Valor del inventario:** insumos `C$ 12,903.64` y productos `C$ 2,097.06`.
3. **Eficiencia Global de Equipos (OEE) y Tiempo de Ciclo:**
   - **OEE Global de Planta:** `91.2%` (`Disponibilidad: 96.7%`, `Rendimiento: 96%`, `Calidad: 98.3%`).
   - **Tiempo de Ciclo Promedio:** `9.27 min/ud` real frente a `9.19 min/ud` estándar.

> **Nota sobre el origen de los datos.** Los lotes, las ventas y las órdenes de compra los carga el semillero `DatabaseSeeder`. Los consumos por lote, los saldos por lote, el Kardex y el stock se reconstruyen con `ConsistenciaInventarioSeeder`, que aplica la BOM completa y el orden FEFO. Los tiempos reales de cada lote son valores del semillero, no mediciones en planta.

---

## 7. Cómo reproducir la evidencia

### 1. Pruebas Automatizadas de Backend (Laravel 13 — 57 pruebas, 653 aserciones)
```bash
cd erp-gelawitto-api
php artisan migrate:fresh --seed
php artisan test
php artisan test --filter=BomOeeTrazabilidadFefoTest
```

### 2. Pruebas Unitarias y Compilación del Frontend (Vue 3 + Vite)
```bash
cd ../erp-gelawitto-ui
npm run test
npm run build
```

### 3. Verificación Automatizada en Navegador Real (Playwright + Firefox)
```bash
cd ../erp-gelawitto-ui
node tests/e2e.mjs
```

---

## 8. Referencias (Formato APA 7)

- Chopra, S., & Meindl, P. (2019). *Administración de la cadena de suministro: Estrategia, planeación y operación* (6.ª ed.). Pearson Educación.
- Comisión del Codex Alimentarius. (2020). *Principios generales de higiene de los alimentos (CXC 1-1969, Rev. 2020)*. Organización de las Naciones Unidas para la Alimentación y la Agricultura (FAO) / Organización Mundial de la Salud (OMS).
- Heizer, J., & Render, B. (2020). *Principios de administración de operaciones* (11.ª ed.). Pearson Educación.
- International Society of Automation. (2010). *ANSI/ISA-95.00.01-2010 (IEC 62264-1 Mod): Enterprise-control system integration — Part 1: Models and terminology*. ISA.
- Nakajima, S. (1988). *Introduction to TPM: Total productive maintenance*. Productivity Press.
- Vollmann, T. E., Berry, W. L., Whybark, D. C., & Jacobs, F. R. (2005). *Manufacturing planning and control for supply chain management* (5.ª ed.). McGraw-Hill.
