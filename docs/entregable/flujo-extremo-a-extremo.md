# Flujo transaccional de extremo a extremo (requisito 13)

Evidencia del flujo **orden de compra → recepción de material → orden de producción → movimiento de inventario → venta** para el producto piloto Gelawitto, ejecutado sobre el ERP con los datos de la BOM del Informe básico del proyecto.

Las operaciones se ejecutaron por las rutas HTTP de la aplicación (`/compras`, `/produccion/lotes`, `/ventas`), con el usuario Administrador, para que cada paso pasara por sus validaciones y servicios reales.

## 1. Orden de compra y recepción de material

La compra tiene dos pasos separados, cada uno con su propia acción en la app:

1. **Crear orden de compra** (`POST /compras`): registra el pedido al proveedor con estado `Pendiente`. No mueve stock, no genera movimientos en el Kardex ni en Finanzas.
2. **Recibir en almacén** (`POST /compras/{orden}/recibir`): elige la condición de pago (contado o crédito a 15 días) y, en una sola transacción, incrementa el stock, recalcula el costo promedio ponderado del insumo, registra el movimiento `Entrada por Compra` y genera el egreso o la cuenta por pagar en Finanzas. La orden pasa a `Recibida`. Una orden no puede recibirse dos veces.

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

1. **Programar orden de producción** (`POST /produccion/lotes`): registra la orden con estado `Programada`, la receta y la meta. No descuenta insumos ni suma producto, y su cantidad obtenida y costos quedan pendientes.
2. **Ejecutar lote** (`POST /produccion/lotes/{lote}/ejecutar`): con la cantidad obtenida real, descuenta los insumos de la receta escalados a la meta (0.040 kg de gelatina, 0.500 L de agua y 5 vasos por lote de 5), calcula el costo y la merma, e ingresa el producto terminado al Kardex. La orden pasa a `Finalizado`. Una orden no puede ejecutarse dos veces, y si faltan insumos al ejecutarla, la app lo rechaza sin descontar nada.

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

Cada venta requiere un turno de caja abierto (TUR-2026-001), descuenta el stock del producto, actualiza los acumulados del turno y registra el ingreso en Finanzas.

## 5. Cambios respecto al diseño original

Con la separación de la orden de compra (sección 1) y de la orden de producción (sección 2), el flujo sigue la secuencia de cinco pasos. El único cambio que se declara es:

1. **Costo unitario del producto por promedio ponderado.** Al producir, el costo vigente del producto se recalcula como promedio ponderado entre el inventario existente y el lote nuevo. Antes se tomaba el costo del último lote. Con los dos lotes anteriores, el costo vigente pasó de C$ 21.38 a **C$ 19.00** ((5 × 17.10 + 85.50) / 9). Los costos ya registrados en el Kardex y en las ventas no se modifican.

## 6. Calidad y trazabilidad por lote (requisito 14)

Cada lote ejecutado pasa por una inspección de calidad antes de considerarse liberado. La pantalla **Calidad y Trazabilidad** registra la inspección y aplica una de tres disposiciones:

| Disposición | Cuándo aplica | Efecto en inventario |
|---|---|---|
| **Liberación** | Sin unidades no conformes | Ninguno. Las unidades quedan disponibles. |
| **Retrabajo** | Hay unidades no conformes que se corregirán | Retira esas unidades del stock vendible y registra `Retrabajo por Calidad` en el Kardex. |
| **Scrap** | Hay unidades no conformes que se desechan | Da de baja esas unidades, registra `Scrap por Calidad` en el Kardex y calcula el costo perdido. |

Reglas de negocio: solo se inspeccionan lotes ya ejecutados, cada lote admite una única inspección, no se puede liberar un lote con unidades no conformes, la no conformidad debe describirse, y no se puede retirar más stock del disponible.

Evidencia de la demostración:

| Lote | Inspección | Unidades no conformes | Disposición | Costo perdido | Estado de calidad |
|---|---|---|---|---|---|
| LOT-2026-005 | CC-2026-001 | 0 de 5 | Liberación | C$ 0.00 | Liberado |
| LOT-2026-006 | CC-2026-002 | 1 de 5 (gelificación incompleta en 1 vaso) | Scrap | C$ 17.64 | Scrap |

Al dar de baja una unidad de `LOT-2026-006`, el stock de producto pasó de 14 a 13 y quedó en el Kardex con referencia del lote. Un segundo intento de inspección de `LOT-2026-005` fue rechazado.

**Trazabilidad por lote.** La pantalla muestra, para cada lote, los insumos que consumió (leídos del Kardex por referencia de lote), las unidades obtenidas, el estado de calidad y cada inspección con su disposición. Así se va del lote hacia atrás, a los insumos, y hacia adelante, al resultado de la inspección.

**Límites de la trazabilidad.** Hoy no se puede seguir un lote hasta el cliente que lo compró: las ventas descuentan del stock general del producto y no registran de qué lote salieron. Tampoco se registra el número de lote del proveedor en las compras. Ambos son cambios de diseño que habría que decidir con el equipo.

## 7. Pendientes

- Cantidad de fruta por vaso para la versión frutal. La receta no tiene línea de fruta, así que la versión frutal no puede producirse todavía.
- Precio de venta: provisional en C$ 100 por vaso.
- Costos de insumos: estimaciones en córdobas, por validar con proveedores.
- Proveedor real de gelatina y agua: hoy se usa el registro "Proveedor por definir (gelatina y agua)".
- Cierre del retrabajo: las unidades en retrabajo salen del stock y no regresan solas. Su reingreso se hace con un ajuste sobrante en Inventario.

## 8. Cómo reproducir la evidencia

```bash
php artisan test --filter=ComprasRecepcionTest
php artisan test --filter=ProduccionOrdenTest
php artisan test --filter=ProduccionCostoPromedioTest
php artisan test --filter=CalidadTest
php artisan test --filter=IiotTest
php artisan test
```

## 9. Integración IIoT del refrigerador (requisito 15)

El refrigerador de los lotes tiene un sensor de temperatura. Hoy se trabaja con un **simulador**; el sensor físico (DS18B20 con ESP32) todavía no está instalado. El simulador usa la misma ruta de ingesta que tendrá el sensor físico, así que la integración no cambia cuando se instale el hardware.

**Flujo:**

1. El sensor (o el simulador) envía la temperatura a `POST /api/iiot/lecturas` con el encabezado `X-IIoT-Token`.
2. La app guarda la lectura en `lecturas_temperatura` y la marca como en rango o fuera de rango.
3. Si la lectura está fuera de rango, los lotes que esperan inspección pasan a estado de calidad `Retenido` y se registra una alerta en la bitácora.
4. Calidad inspecciona los lotes retenidos y decide su disposición, igual que cualquier otro lote (sección 6).

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
php artisan iiot:simular --lecturas=10 --intervalo=60
php artisan iiot:simular --lecturas=5 --excursion
```

El simulador genera lecturas alrededor de 4 °C. Con `--excursion`, la última lectura sale del rango para probar la retención.

**Configuración:** rango aceptable de **2 a 6 °C**, valor provisional que hay que confirmar con el equipo. El token se define en `IIOT_TOKEN` del archivo `.env`, que no se versiona.

**Demostración:** cuatro lecturas normales entre 3.37 y 4.71 °C quedaron en rango, sin retener lotes. Una lectura de 8.90 °C por la API quedó fuera de rango y retuvo los cuatro lotes que esperaban inspección. Un intento sin token fue rechazado con 401.

**Lotes retenidos.** Los lotes `LOT-2026-003` y `004` (Gelawitto tradicional) se revisaron en planta, sin unidades no conformes, y se liberaron (`CC-2026-003` y `CC-2026-004`). Los lotes `LOT-2026-001` y `002` son datos de demostración de la heladería y también se liberaron (`CC-2026-005` y `CC-2026-006`) para que no bloqueen la demostración.

**Límites:**

- La retención alcanza a **todos** los lotes ejecutados que esperan inspección. Eso incluye los datos de demostración de la heladería (`LOT-2026-001` y `002`), que no están en el refrigerador de Gelawitto. Falta acotar la retención a los lotes que entraron al refrigerador, por ejemplo con una ventana de 24 horas que tome la hora de ingreso del lote.
- No hay sensor físico. Las lecturas son simuladas hasta instalar el DS18B20 y el ESP32.
- El diagrama ISA-95 del equipo describe Odoo como ERP y MES. Esta implementación es una aplicación Laravel propia, así que el diagrama debe corregirse.

Las consultas de trazabilidad se pueden repetir sobre la tabla `movimientos_inventario` filtrando por la columna `referencia` (`OC-2026-%`, `LOT-2026-%`, `VTA-2026-%`).
