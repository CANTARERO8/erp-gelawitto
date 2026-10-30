<script setup lang="ts">
import { RouterLink } from 'vue-router';
import ErpLayout from '@/Layouts/ErpLayout.vue';

defineProps<{
    kpis: {
        ventas_hoy: number;
        ventas_acumuladas: number;
        valor_inventario_insumos: number;
        valor_inventario_productos: number;
        alertas_stock: number;
        cuentas_por_pagar: number;
    };
    insumosCriticos: Array<{
        id: number;
        codigo: string;
        nombre: string;
        stock_actual: string;
        stock_minimo: string;
        unidad: string;
    }>;
    ultimosMovimientos: Array<{
        id: number;
        fecha: string;
        tipo_item: string;
        nombre_item: string;
        tipo_movimiento: string;
        referencia: string;
        cantidad: string;
        stock_resultante: string;
        responsable: string;
    }>;
    productosRentabilidad: Array<{
        id: number;
        sku: string;
        nombre: string;
        sabor: string;
        presentacion: string;
        precio_venta: string;
        costo_unitario: string;
        stock_actual: string;
        margen_porcentaje: string;
    }>;
}>();

function money(val: number | string): string {
    return 'C$ ' + new Intl.NumberFormat('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(val));
}
</script>

<template>
    <ErpLayout
        title="Tablero Ejecutivo"
        subtitle=" Estado operativo en tiempo real de producción, inventario, ventas y flujo financiero"
    >
        <!-- Tarjetas de indicadores clave -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <article class="rounded-lg bg-surface border border-border p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Ventas Registradas Hoy</p>
                <p class="text-3xl font-titulo font-bold text-foreground mt-2">{{ money(kpis.ventas_hoy) }}</p>
                <p class="text-xs text-muted-foreground mt-2">
                    Acumulado general: <strong class="text-foreground">{{ money(kpis.ventas_acumuladas) }}</strong>
                </p>
            </article>

            <article class="rounded-lg bg-surface border border-border p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Valor Inventario Helados</p>
                <p class="text-3xl font-titulo font-bold text-foreground mt-2">{{ money(kpis.valor_inventario_productos) }}</p>
                <p class="text-xs text-muted-foreground mt-2">
                    Materia prima en almacén: <strong class="text-foreground">{{ money(kpis.valor_inventario_insumos) }}</strong>
                </p>
            </article>

            <article class="rounded-lg bg-surface border border-border p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Compromisos y Alertas</p>
                <div class="flex items-baseline justify-between mt-2">
                    <p class="text-3xl font-titulo font-bold text-fresa">{{ money(kpis.cuentas_por_pagar) }}</p>
                    <span
                        class="px-2.5 py-1 rounded-md text-xs font-semibold border"
                        :class="
                            kpis.alertas_stock > 0
                                ? 'bg-estado-rojo text-estado-rojo-texto border-estado-rojo-texto/25'
                                : 'bg-estado-verde text-estado-verde-texto border-estado-verde-texto/25'
                        "
                    >
                        {{ kpis.alertas_stock }} insumos bajo mínimo
                    </span>
                </div>
                <p class="text-xs text-muted-foreground mt-2">Cuentas por pagar pendientes a proveedores</p>
            </article>
        </section>

        <!-- Acciones directas del flujo ERP -->
        <section class="rounded-lg bg-surface border border-border p-5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-titulo font-semibold text-foreground">Operaciones Rápidas de Planta y Mostrador</h2>
                <p class="text-xs text-muted-foreground">Ejecuta transacciones integradas con afectación inmediata en Kardex y Caja</p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <RouterLink
                    to="/compras"
                    class="px-3.5 py-2 rounded-lg border border-border bg-background hover:bg-muted text-xs font-semibold text-foreground transition-colors"
                >
                    Recibir Compra
                </RouterLink>
                <RouterLink
                    to="/produccion"
                    class="px-3.5 py-2 rounded-lg border border-border bg-background hover:bg-muted text-xs font-semibold text-foreground transition-colors"
                >
                    Producir Lote de Gelato
                </RouterLink>
                <RouterLink
                    to="/ventas"
                    class="px-4 py-2 rounded-lg bg-fresa hover:bg-frambuesa text-xs font-semibold text-white transition-colors"
                >
                    Abrir Punto de Venta
                </RouterLink>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Rentabilidad y existencias por producto -->
            <section class="lg:col-span-7 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-titulo font-semibold text-foreground">Existencias y Margen por Sabor</h2>
                        <p class="text-xs text-muted-foreground">Costo unitario calculado por lotes de producción</p>
                    </div>
                    <RouterLink to="/catalogos" class="text-xs font-semibold text-frambuesa hover:underline">
                        Ver catálogo completo
                    </RouterLink>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">SKU</th>
                                <th class="py-3 px-4 font-semibold">Producto</th>
                                <th class="py-3 px-4 font-semibold text-right">Stock</th>
                                <th class="py-3 px-4 font-semibold text-right">Costo</th>
                                <th class="py-3 px-4 font-semibold text-right">Precio</th>
                                <th class="py-3 px-4 font-semibold text-right">Margen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="prod in productosRentabilidad" :key="prod.id" class="hover:bg-muted/25">
                                <td class="py-3 px-4 font-mono text-xs text-muted-foreground">{{ prod.sku }}</td>
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-foreground">{{ prod.nombre }}</p>
                                    <p class="text-xs text-muted-foreground">{{ prod.presentacion }}</p>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold">{{ Number(prod.stock_actual).toFixed(1) }} uds</td>
                                <td class="py-3 px-4 text-right text-muted-foreground">{{ money(prod.costo_unitario) }}</td>
                                <td class="py-3 px-4 text-right font-semibold">{{ money(prod.precio_venta) }}</td>
                                <td class="py-3 px-4 text-right">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-estado-verde text-estado-verde-texto">
                                        {{ prod.margen_porcentaje }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Alertas de insumos críticos -->
            <section class="lg:col-span-5 rounded-lg bg-surface border border-border overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-titulo font-semibold text-foreground">Alertas de Reabastecimiento</h2>
                        <p class="text-xs text-muted-foreground">Insumos con stock igual o inferior al mínimo operativo</p>
                    </div>
                    <RouterLink to="/compras" class="text-xs font-semibold text-frambuesa hover:underline">
                        Generar orden
                    </RouterLink>
                </div>

                <div class="p-5 flex-1 flex flex-col justify-center">
                    <div v-if="insumosCriticos.length === 0" class="text-center py-6 text-sm text-muted-foreground">
                        Todos los insumos cuentan con stock por encima del mínimo requerido.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="ins in insumosCriticos"
                            :key="ins.id"
                            class="p-3.5 rounded-lg border border-estado-rojo-texto/25 bg-estado-rojo/50 flex items-center justify-between gap-4"
                        >
                            <div>
                                <p class="text-sm font-semibold text-foreground">{{ ins.nombre }}</p>
                                <p class="text-xs text-muted-foreground">Código {{ ins.codigo }}, Mínimo: {{ Number(ins.stock_minimo).toFixed(1) }} {{ ins.unidad }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 rounded text-xs font-bold bg-estado-rojo text-estado-rojo-texto border border-estado-rojo-texto/20">
                                    {{ Number(ins.stock_actual).toFixed(2) }} {{ ins.unidad }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Últimos movimientos asentados en Kardex -->
        <section class="rounded-lg bg-surface border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="text-base font-titulo font-semibold text-foreground">Bitácora Reciente de Kardex</h2>
                    <p class="text-xs text-muted-foreground">Trazabilidad inmutable de entradas, consumos de receta y salidas por venta</p>
                </div>
                <RouterLink to="/inventario" class="text-xs font-semibold text-frambuesa hover:underline">
                    Abrir Kardex completo
                </RouterLink>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                            <th class="py-3 px-4 font-semibold">Referencia</th>
                            <th class="py-3 px-4 font-semibold">Operación</th>
                            <th class="py-3 px-4 font-semibold">Artículo</th>
                            <th class="py-3 px-4 font-semibold text-right">Variación</th>
                            <th class="py-3 px-4 font-semibold text-right">Saldo Final</th>
                            <th class="py-3 px-4 font-semibold">Responsable</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="mov in ultimosMovimientos" :key="mov.id" class="hover:bg-muted/25">
                            <td class="py-3 px-4 font-mono text-xs font-semibold text-frambuesa">{{ mov.referencia }}</td>
                            <td class="py-3 px-4 text-xs font-semibold">{{ mov.tipo_movimiento }}</td>
                            <td class="py-3 px-4 font-medium text-foreground">{{ mov.nombre_item }}</td>
                            <td
                                class="py-3 px-4 text-right font-semibold"
                                :class="Number(mov.cantidad) >= 0 ? 'text-estado-verde-texto' : 'text-estado-rojo-texto'"
                            >
                                {{ Number(mov.cantidad) >= 0 ? '+' : '' }}{{ Number(mov.cantidad).toFixed(2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold">{{ Number(mov.stock_resultante).toFixed(2) }}</td>
                            <td class="py-3 px-4 text-xs text-muted-foreground">{{ mov.responsable }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </ErpLayout>
</template>
