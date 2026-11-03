<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';
import ErpLayout from '@/Layouts/ErpLayout.vue';
import * as echarts from 'echarts';

const props = defineProps<{
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
    analisisRentabilidad?: {
        ingreso_total: number;
        utilidad_total: number;
        margen_promedio: number;
        margen_productos: Array<{
            nombre: string;
            ingreso: number;
            costo: number;
            utilidad: number;
            margen: number;
            acumulado: number;
        }>;
        recetas: Array<{
            receta: string;
            producto: string;
            precio: number;
            costo: number;
            margen: number;
        }>;
    };
    analisisCompras?: {
        plan: Array<{
            id: number;
            codigo: string;
            nombre: string;
            stock: number;
            minimo: number;
            costo: number;
            consumo: number;
            proveedor: string;
            dias: number | null;
            estado: 'Reordenar' | 'Vigilar' | 'Suficiente';
            sugerida: number;
            valor: number;
        }>;
        historial_precios: Array<{
            nombre: string;
            fecha: string;
            costo: number;
        }>;
        insumos_reordenar: number;
        insumos_vigilar: number;
        valor_compra_sugerida: number;
    };
}>();

function money(val: number | string): string {
    return 'C$ ' + new Intl.NumberFormat('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(val));
}

function cordobas(val: number | string | undefined | null): string {
    if (val === undefined || val === null) return 'C$ 0.00';
    return 'C$ ' + new Intl.NumberFormat('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(val));
}

function nombreCorto(nombre: string): string {
    return nombre
        .replace(/ y dextrosa/i, '')
        .replace(/ batible \d+% grasa/i, '')
        .replace(/ isot[eé]rmico biodegradable/i, '')
        .replace(/ silvestre congelada/i, '')
        .replace(/ seleccionada/i, '')
        .replace(/ fresca pasteurizada/i, '')
        .replace(/natural de /i, '')
        .replace(/ criollo/i, '')
        .trim();
}

// Referencias a los contenedores DOM de los gráficos
const chartMargenRef = ref<HTMLDivElement | null>(null);
const chartParetoRef = ref<HTMLDivElement | null>(null);
const chartRecetaRef = ref<HTMLDivElement | null>(null);
const chartStockRef = ref<HTMLDivElement | null>(null);
const chartPreciosRef = ref<HTMLDivElement | null>(null);

let chartMargen: echarts.ECharts | null = null;
let chartPareto: echarts.ECharts | null = null;
let chartReceta: echarts.ECharts | null = null;
let chartStock: echarts.ECharts | null = null;
let chartPrecios: echarts.ECharts | null = null;

const isDark = ref(false);

function detectarTema() {
    isDark.value =
        document.documentElement.classList.contains('dark') ||
        document.body.classList.contains('dark') ||
        document.body.classList.contains('body--dark');
}

const mejorProducto = computed(() => {
    const prods = props.analisisRentabilidad?.margen_productos ?? [];
    if (!prods.length) return null;
    return [...prods].sort((a, b) => b.margen - a.margen)[0];
});

function renderizarGraficos() {
    const dark = isDark.value;
    const textColor = dark ? '#f5edf0' : '#2a2126';
    const splitColor = dark ? '#3a2d36' : '#ead5dc';
    const fresa = '#e8436b';
    const frambuesa = '#b0244e';
    const mora = '#4a1430';
    const durazno = '#ff9e80';

    // 1. Margen por producto
    if (chartMargenRef.value) {
        chartMargen?.dispose();
        chartMargen = echarts.init(chartMargenRef.value);
        const prods = [...(props.analisisRentabilidad?.margen_productos ?? [])].reverse();
        chartMargen.setOption({
            textStyle: { fontFamily: 'Inter, sans-serif', color: textColor },
            animationDuration: 900,
            grid: { left: 180, right: 40, top: 20, bottom: 30 },
            tooltip: { trigger: 'axis', valueFormatter: (v: any) => `${v} %` },
            xAxis: {
                type: 'value',
                max: 100,
                axisLabel: { formatter: '{value} %', color: textColor },
                splitLine: { lineStyle: { color: splitColor } },
            },
            yAxis: {
                type: 'category',
                data: prods.map((p) => p.nombre),
                axisLabel: { color: textColor },
            },
            series: [
                {
                    type: 'bar',
                    data: prods.map((p) => p.margen),
                    barWidth: 16,
                    itemStyle: {
                        borderRadius: [0, 8, 8, 0],
                        color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                            { offset: 0, color: frambuesa },
                            { offset: 1, color: durazno },
                        ]),
                    },
                    label: { show: true, position: 'right', formatter: '{c} %', color: textColor },
                },
            ],
        });
    }

    // 2. Curva de Pareto
    if (chartParetoRef.value) {
        chartPareto?.dispose();
        chartPareto = echarts.init(chartParetoRef.value);
        const paretoData = props.analisisRentabilidad?.margen_productos ?? [];
        chartPareto.setOption({
            textStyle: { fontFamily: 'Inter, sans-serif', color: textColor },
            animationDuration: 900,
            grid: { left: 60, right: 50, top: 40, bottom: 60 },
            legend: { data: ['Ingreso', 'Acumulado'], textStyle: { color: textColor }, top: 0 },
            tooltip: { trigger: 'axis' },
            xAxis: {
                type: 'category',
                data: paretoData.map((p) => p.nombre.replace('Gelato ', '')),
                axisLabel: { color: textColor, rotate: 12, interval: 0 },
            },
            yAxis: [
                { type: 'value', axisLabel: { color: textColor }, splitLine: { lineStyle: { color: splitColor } } },
                {
                    type: 'value',
                    max: 100,
                    axisLabel: { formatter: '{value} %', color: textColor },
                    splitLine: { show: false },
                },
            ],
            series: [
                {
                    name: 'Ingreso',
                    type: 'bar',
                    data: paretoData.map((p) => p.ingreso),
                    itemStyle: { color: fresa, borderRadius: [6, 6, 0, 0] },
                },
                {
                    name: 'Acumulado',
                    type: 'line',
                    yAxisIndex: 1,
                    smooth: true,
                    data: paretoData.map((p) => p.acumulado),
                    color: dark ? durazno : mora,
                    lineStyle: { width: 3 },
                },
            ],
        });
    }

    // 3. Costo de fórmula vs precio de venta
    if (chartRecetaRef.value) {
        chartReceta?.dispose();
        chartReceta = echarts.init(chartRecetaRef.value);
        const recetas = props.analisisRentabilidad?.recetas ?? [];
        chartReceta.setOption({
            textStyle: { fontFamily: 'Inter, sans-serif', color: textColor },
            animationDuration: 900,
            grid: { left: 55, right: 30, top: 40, bottom: 50 },
            legend: { data: ['Costo unitario', 'Precio de venta'], textStyle: { color: textColor }, top: 0 },
            tooltip: { trigger: 'axis' },
            xAxis: {
                type: 'category',
                data: recetas.map((r) => r.producto.replace('Gelato ', '')),
                axisLabel: { color: textColor, rotate: 10, interval: 0 },
            },
            yAxis: { type: 'value', axisLabel: { color: textColor }, splitLine: { lineStyle: { color: splitColor } } },
            series: [
                {
                    name: 'Costo unitario',
                    type: 'bar',
                    data: recetas.map((r) => r.costo),
                    itemStyle: { color: durazno, borderRadius: [6, 6, 0, 0] },
                },
                {
                    name: 'Precio de venta',
                    type: 'bar',
                    data: recetas.map((r) => r.precio),
                    itemStyle: { color: frambuesa, borderRadius: [6, 6, 0, 0] },
                },
            ],
        });
    }

    // 4. Stock vs mínimo en kardex
    if (chartStockRef.value) {
        chartStock?.dispose();
        chartStock = echarts.init(chartStockRef.value);
        const plan = props.analisisCompras?.plan ?? [];
        chartStock.setOption({
            textStyle: { fontFamily: 'Inter, sans-serif', color: textColor },
            animationDuration: 900,
            grid: { left: 55, right: 30, top: 40, bottom: 75 },
            legend: { data: ['Stock actual', 'Stock mínimo'], textStyle: { color: textColor }, top: 0 },
            tooltip: { trigger: 'axis' },
            xAxis: {
                type: 'category',
                data: plan.map((p) => nombreCorto(p.nombre)),
                axisLabel: { color: textColor, rotate: 25, interval: 0, fontSize: 11 },
            },
            yAxis: { type: 'value', axisLabel: { color: textColor }, splitLine: { lineStyle: { color: splitColor } } },
            series: [
                {
                    name: 'Stock actual',
                    type: 'bar',
                    data: plan.map((p) => p.stock),
                    itemStyle: { color: fresa, borderRadius: [6, 6, 0, 0] },
                },
                {
                    name: 'Stock mínimo',
                    type: 'line',
                    data: plan.map((p) => p.minimo),
                    color: dark ? durazno : mora,
                    lineStyle: { type: 'dashed', width: 2 },
                    symbol: 'diamond',
                    symbolSize: 8,
                },
            ],
        });
    }

    // 5. Histórico de precios
    if (chartPreciosRef.value) {
        chartPrecios?.dispose();
        chartPrecios = echarts.init(chartPreciosRef.value);
        const hist = props.analisisCompras?.historial_precios ?? [];

        // Extraer fechas únicas y ordenadas
        const fechasSet = new Set<string>();
        hist.forEach((h) => fechasSet.add(h.fecha));
        const fechas = Array.from(fechasSet).sort();

        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const fechaLabels = fechas.map((f) => {
            const parts = f.split('-');
            const mesIdx = parseInt(parts[1], 10) - 1;
            return `${parts[2]} ${meses[mesIdx] ?? parts[1]}`;
        });

        // Agrupar por insumo
        const porInsumo: Record<string, Record<string, number>> = {};
        for (const h of hist) {
            const n = nombreCorto(h.nombre);
            if (!porInsumo[n]) porInsumo[n] = {};
            porInsumo[n][h.fecha] = h.costo;
        }

        const seriesList = Object.entries(porInsumo).map(([nombre, fechasMap]) => ({
            name: nombre,
            type: 'line',
            smooth: true,
            symbolSize: 8,
            data: fechas.map((f) => fechasMap[f] ?? null),
            connectNulls: true,
            lineStyle: { width: 3 },
        }));

        chartPrecios.setOption({
            textStyle: { fontFamily: 'Inter, sans-serif', color: textColor },
            animationDuration: 900,
            grid: { left: 65, right: 30, top: 50, bottom: 45 },
            legend: { data: Object.keys(porInsumo), textStyle: { color: textColor }, type: 'scroll', top: 0 },
            tooltip: {
                trigger: 'axis',
                valueFormatter: (v: any) => (v !== null && v !== undefined ? `C$ ${Number(v).toFixed(2)}` : '-'),
            },
            xAxis: {
                type: 'category',
                data: fechaLabels,
                axisLabel: { color: textColor },
            },
            yAxis: {
                type: 'value',
                axisLabel: { color: textColor, formatter: 'C$ {value}' },
                splitLine: { lineStyle: { color: splitColor } },
            },
            color: [fresa, durazno, frambuesa, '#8a4a68', '#40916c', '#52b788'],
            series: seriesList,
        });
    }
}

function resizeCharts() {
    chartMargen?.resize();
    chartPareto?.resize();
    chartReceta?.resize();
    chartStock?.resize();
    chartPrecios?.resize();
}

let observer: MutationObserver | null = null;

onMounted(() => {
    detectarTema();
    nextTick(() => {
        renderizarGraficos();
    });
    window.addEventListener('resize', resizeCharts);

    observer = new MutationObserver(() => {
        detectarTema();
        nextTick(() => renderizarGraficos());
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onUnmounted(() => {
    window.removeEventListener('resize', resizeCharts);
    observer?.disconnect();
    chartMargen?.dispose();
    chartPareto?.dispose();
    chartReceta?.dispose();
    chartStock?.dispose();
    chartPrecios?.dispose();
});

watch(
    () => [props.analisisRentabilidad, props.analisisCompras],
    () => {
        nextTick(() => renderizarGraficos());
    },
    { deep: true }
);
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

        <!-- SECCIÓN ANÁLISIS 1: RENTABILIDAD Y CONTROL COMERCIAL -->
        <section class="space-y-4 pt-4">
            <div class="flex flex-wrap items-end justify-between gap-3 pb-3 border-b border-border">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-fresa">
                        Control Financiero y Comercial
                    </span>
                    <h2 class="text-xl font-titulo font-semibold text-foreground">
                        Rentabilidad Real por Producto y Receta
                    </h2>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Margen bruto obtenido por ventas y costeo unitario de fórmulas maestras contra precio al público
                    </p>
                </div>
                <span class="px-3 py-1.5 rounded-lg border border-border bg-surface text-xs font-medium text-muted-foreground">
                    Cálculo en vivo sobre libro de ventas
                </span>
            </div>

            <!-- Tarjetas de indicadores de rentabilidad -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs transition-transform hover:-translate-y-0.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Ingreso por Ventas</p>
                    <p class="text-2xl font-titulo font-bold text-foreground mt-2">
                        {{ cordobas(analisisRentabilidad?.ingreso_total) }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-1.5">Ventas completadas en caja</p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs transition-transform hover:-translate-y-0.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Utilidad Bruta Neta</p>
                    <p class="text-2xl font-titulo font-bold text-frambuesa dark:text-durazno mt-2">
                        {{ cordobas(analisisRentabilidad?.utilidad_total) }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-1.5">Ingresos menos costo de materia prima</p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs transition-transform hover:-translate-y-0.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Margen Bruto Promedio</p>
                    <p class="text-2xl font-titulo font-bold text-foreground mt-2">
                        {{ analisisRentabilidad?.margen_promedio ?? 0 }} %
                    </p>
                    <p class="text-xs text-muted-foreground mt-1.5">Rendimiento comercial global</p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs transition-transform hover:-translate-y-0.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Sabor Más Rentable</p>
                    <p class="text-2xl font-titulo font-bold text-fresa mt-2">
                        {{ mejorProducto?.margen ?? 0 }} %
                    </p>
                    <p class="text-xs text-muted-foreground mt-1.5 truncate" :title="mejorProducto?.nombre">
                        {{ mejorProducto?.nombre ?? 'Sin datos' }}
                    </p>
                </article>
            </div>

            <!-- Gráficos de rentabilidad -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <h3 class="text-sm font-titulo font-semibold text-foreground mb-2">Margen Porcentual por Producto</h3>
                    <div ref="chartMargenRef" class="w-full h-80"></div>
                </div>

                <div class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <h3 class="text-sm font-titulo font-semibold text-foreground mb-2">Curva de Pareto de Ingresos</h3>
                    <div ref="chartParetoRef" class="w-full h-80"></div>
                </div>
            </div>

            <!-- Costeo de fórmulas y tabla de recetas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <h3 class="text-sm font-titulo font-semibold text-foreground mb-2">Costo Unitario de Fórmula frente a Precio de Venta</h3>
                    <div ref="chartRecetaRef" class="w-full h-80"></div>
                </div>

                <div class="rounded-xl bg-surface border border-border p-5 shadow-xs flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-titulo font-semibold text-foreground mb-3">Rentabilidad y Costeo de Recetas Maestras</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="border-b border-border text-muted-foreground uppercase tracking-wider">
                                        <th class="py-2.5 font-semibold">Producto</th>
                                        <th class="py-2.5 font-semibold text-right">Costo</th>
                                        <th class="py-2.5 font-semibold text-right">Precio</th>
                                        <th class="py-2.5 font-semibold text-right">Margen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr
                                        v-for="rec in analisisRentabilidad?.recetas ?? []"
                                        :key="rec.receta"
                                        class="hover:bg-muted/40 transition-colors"
                                    >
                                        <td class="py-3 font-medium text-foreground">{{ rec.producto }}</td>
                                        <td class="py-3 text-right text-muted-foreground">{{ cordobas(rec.costo) }}</td>
                                        <td class="py-3 text-right font-medium text-foreground">{{ cordobas(rec.precio) }}</td>
                                        <td class="py-3 text-right">
                                            <span
                                                class="inline-block px-2 py-0.5 rounded-full font-semibold text-xs"
                                                :class="
                                                    rec.margen >= 70
                                                        ? 'bg-estado-verde text-estado-verde-texto'
                                                        : rec.margen >= 50
                                                          ? 'bg-estado-azul text-estado-azul-texto'
                                                          : 'bg-estado-rojo text-estado-rojo-texto'
                                                "
                                            >
                                                {{ rec.margen }} %
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN ANÁLISIS 2: ABASTECIMIENTO Y PLANIFICACIÓN DE COMPRAS (MRP) -->
        <section class="space-y-4 pt-4">
            <div class="flex flex-wrap items-end justify-between gap-3 pb-3 border-b border-border">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-fresa">
                        Abastecimiento y Operaciones
                    </span>
                    <h2 class="text-xl font-titulo font-semibold text-foreground">
                        Planificación de Compras y Reorden (MRP)
                    </h2>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Días de inventario restante, insumos bajo mínimo operativo y presupuesto sugerido por proveedor
                    </p>
                </div>
                <span class="px-3 py-1.5 rounded-lg border border-border bg-surface text-xs font-medium text-muted-foreground">
                    Horizonte proyectado a 14 días
                </span>
            </div>

            <!-- Tarjetas de indicadores de compras -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs transition-transform hover:-translate-y-0.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Insumos Críticos Bajo Mínimo</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <p class="text-2xl font-titulo font-bold text-frambuesa dark:text-durazno">
                            {{ analisisCompras?.insumos_reordenar ?? 0 }}
                        </p>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-estado-rojo text-estado-rojo-texto">
                            Acción requerida
                        </span>
                    </div>
                    <p class="text-xs text-muted-foreground mt-1.5">
                        {{ analisisCompras?.insumos_vigilar ?? 0 }} insumos adicionales en observación
                    </p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs transition-transform hover:-translate-y-0.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Presupuesto Sugerido de Compra</p>
                    <p class="text-2xl font-titulo font-bold text-foreground mt-2">
                        {{ cordobas(analisisCompras?.valor_compra_sugerida) }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-1.5">Para garantizar cobertura a 14 días</p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs transition-transform hover:-translate-y-0.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Catálogo de Insumos en Almacén</p>
                    <p class="text-2xl font-titulo font-bold text-foreground mt-2">
                        {{ analisisCompras?.plan?.length ?? 0 }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-1.5">Materias primas y empaques activos</p>
                </article>
            </div>

            <!-- Gráficos de compras -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <h3 class="text-sm font-titulo font-semibold text-foreground mb-2">Existencia en Kardex frente a Stock Mínimo</h3>
                    <div ref="chartStockRef" class="w-full h-80"></div>
                </div>

                <div class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <h3 class="text-sm font-titulo font-semibold text-foreground mb-2">Histórico de Precios de Compra Unitario</h3>
                    <div ref="chartPreciosRef" class="w-full h-80"></div>
                </div>
            </div>

            <!-- Tabla de sugerencia de reorden por proveedor -->
            <div class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                <h3 class="text-sm font-titulo font-semibold text-foreground mb-3">Sugerencia de Reabastecimiento por Proveedor</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-border text-muted-foreground uppercase tracking-wider">
                                <th class="py-2.5 font-semibold">Insumo</th>
                                <th class="py-2.5 font-semibold">Proveedor Asignado</th>
                                <th class="py-2.5 font-semibold text-right">Stock</th>
                                <th class="py-2.5 font-semibold text-right">Mínimo</th>
                                <th class="py-2.5 font-semibold text-right">Días Cobertura</th>
                                <th class="py-2.5 font-semibold text-center">Estado</th>
                                <th class="py-2.5 font-semibold text-right">Sugerido</th>
                                <th class="py-2.5 font-semibold text-right">Presupuesto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="ins in analisisCompras?.plan ?? []"
                                :key="ins.id"
                                class="hover:bg-muted/40 transition-colors"
                            >
                                <td class="py-3 font-medium text-foreground">{{ ins.nombre }}</td>
                                <td class="py-3 text-muted-foreground">{{ ins.proveedor }}</td>
                                <td class="py-3 text-right font-medium text-foreground">{{ ins.stock }}</td>
                                <td class="py-3 text-right text-muted-foreground">{{ ins.minimo }}</td>
                                <td class="py-3 text-right text-muted-foreground">
                                    {{ ins.dias !== null ? `${ins.dias} días` : 'Sin consumo' }}
                                </td>
                                <td class="py-3 text-center">
                                    <span
                                        class="inline-block px-2.5 py-0.5 rounded-full font-semibold text-xs"
                                        :class="
                                            ins.estado === 'Reordenar'
                                                ? 'bg-estado-rojo text-estado-rojo-texto'
                                                : ins.estado === 'Vigilar'
                                                  ? 'bg-estado-azul text-estado-azul-texto'
                                                  : 'bg-estado-verde text-estado-verde-texto'
                                        "
                                    >
                                        {{ ins.estado }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-semibold text-foreground">{{ ins.sugerida }}</td>
                                <td class="py-3 text-right font-semibold text-frambuesa dark:text-durazno">{{ cordobas(ins.valor) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </ErpLayout>
</template>
