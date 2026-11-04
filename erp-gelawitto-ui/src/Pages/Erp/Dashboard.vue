<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import ErpLayout from '@/Layouts/ErpLayout.vue';
import * as echarts from 'echarts';

const props = defineProps<{
    kpis?: {
        ventas_hoy: number;
        ventas_acumuladas: number;
        valor_inventario_insumos: number;
        valor_inventario_productos: number;
        alertas_stock: number;
        cuentas_por_pagar: number;
    };
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
    oee?: {
        disponibilidad: number;
        rendimiento: number;
        calidad: number;
        oee_global: number;
        tiempo_ciclo_real_promedio_min: number;
        tiempo_ciclo_estandar_promedio_min: number;
        lotes: Array<{
            codigo_lote: string;
            producto: string;
            cantidad_obtenida: number;
            unidades_conformes: number;
            tiempo_planificado_min: number;
            tiempo_real_min: number;
            tiempo_paro_min: number;
            tiempo_operativo_min: number;
            ciclo_estandar_min: number;
            ciclo_real_min: number;
            disponibilidad: number;
            rendimiento: number;
            calidad: number;
            oee: number;
        }>;
    };
}>();

function money(val: number | string | undefined): string {
    if (val === undefined || val === null) return 'C$ 0.00';
    return 'C$ ' + new Intl.NumberFormat('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(val));
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

function nombreCorto(nombre: string): string {
    return nombre
        .replace(/ blanco seleccionado/i, '')
        .replace(/ en polvo Great Value 40 g/i, '')
        .replace(/ tratada \(ósmosis inversa\)/i, '')
        .replace(/ cristalina refinada/i, '')
        .replace(/ naturales en cubos \(fresa, durazno y kiwi\)/i, '')
        .replace(/ azucarada pasteurizada/i, '')
        .replace(/Extracto natural de /i, 'Extracto ')
        .replace(/ reciclable biorientado 100 mL con tapa domo/i, ' 100 mL')
        .replace(/ autoadhesiva impermeable Gelawitto/i, '')
        .replace(/ termoformada empaque secundario x5 vasos/i, ' x5')
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
    () => props.analisisRentabilidad,
    () => {
        nextTick(() => renderizarGraficos());
    },
    { deep: true }
);
</script>

<template>
    <ErpLayout
        title="Dashboard"
        subtitle="Análisis financiero de rentabilidad, margen por receta y abastecimiento MRP"
    >
        <!-- SECCIÓN 1: RENTABILIDAD Y CONTROL COMERCIAL -->
        <section class="space-y-4">
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
                        {{ money(analisisRentabilidad?.ingreso_total) }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-1.5">Ventas completadas en caja</p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs transition-transform hover:-translate-y-0.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Utilidad Bruta Neta</p>
                    <p class="text-2xl font-titulo font-bold text-frambuesa dark:text-durazno mt-2">
                        {{ money(analisisRentabilidad?.utilidad_total) }}
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
                                        <td class="py-3 text-right text-muted-foreground">{{ money(rec.costo) }}</td>
                                        <td class="py-3 text-right font-medium text-foreground">{{ money(rec.precio) }}</td>
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

        <!-- SECCIÓN 2: ABASTECIMIENTO Y PLANIFICACIÓN DE COMPRAS (MRP) -->
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
                        {{ money(analisisCompras?.valor_compra_sugerida) }}
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
                                <td class="py-3 text-right font-semibold text-frambuesa dark:text-durazno">{{ money(ins.valor) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 3: EFICIENCIA DE PLANTA (OEE Y TIEMPO DE CICLO) -->
        <section v-if="oee" class="space-y-4 pt-4">
            <div class="flex flex-wrap items-end justify-between gap-3 pb-3 border-b border-border">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-fresa">
                        Ingeniería de Planta y Manufactura
                    </span>
                    <h2 class="text-xl font-titulo font-semibold text-foreground">
                        Eficiencia Global de Equipos (OEE) y Tiempo de Ciclo
                    </h2>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Disponibilidad, Rendimiento y Calidad calculados sobre tiempos reales de ejecución y rutas de operación
                    </p>
                </div>
                <span class="px-3 py-1.5 rounded-lg border border-border bg-surface text-xs font-medium text-muted-foreground">
                    Ciclo Estándar: {{ oee.tiempo_ciclo_estandar_promedio_min }} min/ud
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">OEE Global Planta</p>
                    <p class="text-2xl font-titulo font-bold text-fresa mt-2">{{ oee.oee_global }} %</p>
                    <p class="text-xs text-muted-foreground mt-1.5">Disp. x Rend. x Calidad</p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Disponibilidad</p>
                    <p class="text-2xl font-titulo font-bold text-foreground mt-2">{{ oee.disponibilidad }} %</p>
                    <p class="text-xs text-muted-foreground mt-1.5">Tiempo operativo vs real</p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Rendimiento</p>
                    <p class="text-2xl font-titulo font-bold text-foreground mt-2">{{ oee.rendimiento }} %</p>
                    <p class="text-xs text-muted-foreground mt-1.5">Velocidad real vs estándar</p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Calidad (FTQ)</p>
                    <p class="text-2xl font-titulo font-bold text-foreground mt-2">{{ oee.calidad }} %</p>
                    <p class="text-xs text-muted-foreground mt-1.5">Unidades conformes liberadas</p>
                </article>

                <article class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Tiempo de Ciclo Real</p>
                    <p class="text-2xl font-titulo font-bold text-frambuesa dark:text-durazno mt-2">
                        {{ oee.tiempo_ciclo_real_promedio_min }} min
                    </p>
                    <p class="text-xs text-muted-foreground mt-1.5">
                        Estándar: {{ oee.tiempo_ciclo_estandar_promedio_min }} min/ud
                    </p>
                </article>
            </div>

            <div class="rounded-xl bg-surface border border-border p-5 shadow-xs">
                <h3 class="text-sm font-titulo font-semibold text-foreground mb-3">
                    Desglose de OEE y Tiempo de Ciclo por Lote Ejecutado
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-border text-muted-foreground uppercase tracking-wider">
                                <th class="py-2.5 font-semibold">Lote</th>
                                <th class="py-2.5 font-semibold">Producto / Subensamble</th>
                                <th class="py-2.5 font-semibold text-right">Conformes / Obtenido</th>
                                <th class="py-2.5 font-semibold text-right">Ciclo Estándar</th>
                                <th class="py-2.5 font-semibold text-right">Ciclo Real</th>
                                <th class="py-2.5 font-semibold text-right">Disponibilidad</th>
                                <th class="py-2.5 font-semibold text-right">Rendimiento</th>
                                <th class="py-2.5 font-semibold text-right">Calidad</th>
                                <th class="py-2.5 font-semibold text-right">OEE Lote</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="l in oee.lotes" :key="l.codigo_lote" class="hover:bg-muted/40 transition-colors">
                                <td class="py-3 font-mono font-semibold text-frambuesa">{{ l.codigo_lote }}</td>
                                <td class="py-3 font-medium text-foreground">{{ l.producto }}</td>
                                <td class="py-3 text-right">{{ l.unidades_conformes }} / {{ l.cantidad_obtenida }}</td>
                                <td class="py-3 text-right text-muted-foreground">{{ l.ciclo_estandar_min }} min</td>
                                <td class="py-3 text-right font-medium text-foreground">{{ l.ciclo_real_min }} min</td>
                                <td class="py-3 text-right">{{ l.disponibilidad }} %</td>
                                <td class="py-3 text-right">{{ l.rendimiento }} %</td>
                                <td class="py-3 text-right">{{ l.calidad }} %</td>
                                <td class="py-3 text-right">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full font-semibold text-xs"
                                        :class="
                                            l.oee >= 85
                                                ? 'bg-estado-verde text-estado-verde-texto'
                                                : l.oee >= 70
                                                  ? 'bg-estado-azul text-estado-azul-texto'
                                                  : 'bg-estado-rojo text-estado-rojo-texto'
                                        "
                                    >
                                        {{ l.oee }} %
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </ErpLayout>
</template>
