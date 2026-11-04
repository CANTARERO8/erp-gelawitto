<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useFormulario, ejecutarAccionApi } from '@/composables/useFormulario';
import ErpLayout from '@/Layouts/ErpLayout.vue';

const props = defineProps<{
    recetas: Array<{
        id: number;
        nombre: string;
        producto_nombre: string;
        producto_sku: string;
        producto_tipo?: string;
        sabor: string;
        rendimiento_unidades: string;
        tiempo_preparacion_min: number;
        instrucciones: string;
        ingredientes: Array<{
            insumo_nombre: string;
            es_subensamble?: boolean;
            subproducto_sku?: string | null;
            nivel?: number;
            cantidad_requerida: string;
            merma_estimada_porcentaje: string;
            stock_actual: string;
            costo_promedio: string;
            unidad: string;
            componentes_nivel_2?: Array<{
                insumo_nombre: string;
                cantidad_requerida: string;
                unidad: string;
                costo_promedio: string;
                nivel: number;
            }>;
        }>;
        rutas?: Array<{
            secuencia: number;
            centro_codigo: string;
            centro_nombre: string;
            operacion: string;
            tiempo_preparacion_min: string;
            tiempo_ciclo_min_por_unidad: string;
        }>;
    }>;
    centros_trabajo?: Array<{
        id: number;
        codigo: string;
        nombre: string;
        estacion: string;
        capacidad_unidades_hora: string;
    }>;
    oee?: {
        disponibilidad: number;
        rendimiento: number;
        calidad: number;
        oee_global: number;
        tiempo_ciclo_real_promedio_min: number;
        tiempo_ciclo_estandar_promedio_min: number;
    };
    lotes: Array<{
        id: number;
        codigo_lote: string;
        producto_nombre: string;
        producto_sku: string;
        operador_nombre: string;
        fecha_produccion: string;
        cantidad_programada: string;
        cantidad_obtenida: string | null;
        merma: string;
        costo_total_lote: string | null;
        costo_unitario_resultante: string | null;
        estado: string;
        notas: string;
    }>;
    productos?: Array<{
        id: number;
        sku: string;
        nombre: string;
        tipo?: string;
    }>;
    insumos?: Array<{
        id: number;
        codigo: string;
        nombre: string;
        unidad: string;
    }>;
}>();

const mostrarNuevaReceta = ref(false);

const subensambles = computed(() =>
    (props.productos ?? []).filter((p) => p.tipo === 'semielaborado')
);

const form = useFormulario({
    receta_id: props.recetas[0]?.id ?? 1,
    cantidad_programada: '10',
    notas: '',
});

const formReceta = useFormulario({
    producto_id: props.productos?.[0]?.id ?? 1,
    nombre: '',
    rendimiento_unidades: '5',
    tiempo_preparacion_min: 45,
    instrucciones: '',
    ingredientes: [
        {
            tipo_componente: 'insumo' as 'insumo' | 'subensamble',
            insumo_id: (props.insumos?.[0]?.id ?? 1) as number | null,
            subproducto_id: null as number | null,
            nivel: 1,
            cantidad_requerida: '1.000',
            merma_estimada_porcentaje: '0',
        },
    ],
    rutas: [
        {
            centro_trabajo_id: props.centros_trabajo?.[0]?.id ?? 1,
            secuencia: 1,
            operacion: '',
            tiempo_preparacion_min: '5.0',
            tiempo_ciclo_min_por_unidad: '1.500',
        },
    ],
});

const recetaSeleccionada = computed(() =>
    props.recetas.find((r) => r.id === Number(form.receta_id))
);

// Simulador MRP: Explosión de materiales según la cantidad programada
const explosionMrp = computed(() => {
    const rec = recetaSeleccionada.value;
    if (!rec) return [];
    const factor = Number(form.cantidad_programada || 0) / Math.max(0.001, Number(rec.rendimiento_unidades));
    return rec.ingredientes.map((ing) => {
        const requerido = Number(ing.cantidad_requerida) * factor;
        const disponible = Number(ing.stock_actual);
        const faltante = Math.max(0, requerido - disponible);
        return {
            nombre: ing.insumo_nombre,
            unidad: ing.unidad,
            requerido: requerido.toFixed(3),
            disponible: disponible.toFixed(3),
            faltante: faltante.toFixed(3),
            alcanza: disponible >= requerido,
            costo: (requerido * Number(ing.costo_promedio)).toFixed(2),
        };
    });
});

const mrpCompleto = computed(() => explosionMrp.value.every((i) => i.alcanza));

// Cantidad obtenida, paro y vencimiento que el operador captura al ejecutar cada orden
const obtenidoEjecucion = reactive<Record<number, string>>({});
const paroEjecucion = reactive<Record<number, string>>({});
const vencimientoEjecucion = reactive<Record<number, string>>({});
const ejecutandoId = ref<number | null>(null);

function cambiarTipoComponente(idx: number) {
    const ing = formReceta.ingredientes[idx];
    if (ing.tipo_componente === 'subensamble') {
        ing.insumo_id = null;
        ing.subproducto_id = subensambles.value[0]?.id ?? null;
        ing.nivel = 1;
    } else {
        ing.subproducto_id = null;
        ing.insumo_id = props.insumos?.[0]?.id ?? 1;
        ing.nivel = 1;
    }
}

function agregarIngrediente() {
    formReceta.ingredientes.push({
        tipo_componente: 'insumo',
        insumo_id: props.insumos?.[0]?.id ?? 1,
        subproducto_id: null,
        nivel: 1,
        cantidad_requerida: '1.000',
        merma_estimada_porcentaje: '0',
    });
}

function quitarIngrediente(idx: number) {
    if (formReceta.ingredientes.length > 1) {
        formReceta.ingredientes.splice(idx, 1);
    }
}

function agregarRuta() {
    formReceta.rutas.push({
        centro_trabajo_id: props.centros_trabajo?.[0]?.id ?? 1,
        secuencia: formReceta.rutas.length + 1,
        operacion: '',
        tiempo_preparacion_min: '5.0',
        tiempo_ciclo_min_por_unidad: '1.500',
    });
}

function quitarRuta(idx: number) {
    formReceta.rutas.splice(idx, 1);
    formReceta.rutas.forEach((r: any, i: number) => {
        r.secuencia = i + 1;
    });
}

function guardarReceta() {
    formReceta.rutas = formReceta.rutas.filter((r: any) => String(r.operacion || '').trim().length > 0);
    formReceta.post('/produccion/recetas', {
        onSuccess: () => {
            formReceta.reset('nombre', 'instrucciones');
            mostrarNuevaReceta.value = false;
        },
    });
}

function programarLote() {
    form.post('/produccion/lotes', {
        onSuccess: () => form.reset('notas'),
    });
}

async function ejecutarLote(lote: { id: number; cantidad_programada: string }) {
    ejecutandoId.value = lote.id;
    await ejecutarAccionApi(
        `/produccion/lotes/${lote.id}/ejecutar`,
        {
            cantidad_obtenida: obtenidoEjecucion[lote.id] ?? lote.cantidad_programada,
            tiempo_paro_min: paroEjecucion[lote.id] || 0,
            fecha_vencimiento: vencimientoEjecucion[lote.id] || null,
        },
        {
            onFinish: () => {
                ejecutandoId.value = null;
            },
        }
    );
}
</script>

<template>
    <ErpLayout
        title="Formulación, MRP y Producción por Lotes"
        subtitle="Fórmulas técnicas, explosión de requerimientos de materiales (MRP) y ejecución transaccional en Córdobas (C$)"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Paso 1: programar orden de producción + Simulador MRP -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit space-y-4">
                <div>
                    <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Programar Orden de Producción</h2>
                    <p class="text-xs text-muted-foreground">
                        Planifica el lote y verifica con el simulador MRP la disponibilidad en almacén
                    </p>
                </div>

                <form @submit.prevent="programarLote" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Fórmula Maestra</label>
                        <select
                            v-model="form.receta_id"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option v-for="r in recetas" :key="r.id" :value="r.id">
                                {{ r.producto_sku }} - {{ r.nombre }}
                            </option>
                        </select>
                        <p v-if="form.errors.receta_id" class="text-xs text-estado-rojo-texto mt-1">{{ form.errors.receta_id }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Meta Programada (uds)</label>
                        <input
                            v-model="form.cantidad_programada"
                            type="number"
                            step="0.5"
                            min="1"
                            required
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <!-- Panel MRP de explosión de insumos -->
                    <div v-if="explosionMrp.length > 0" class="p-3 rounded-lg bg-background border border-border space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Explosión MRP</span>
                            <span
                                class="px-2 py-0.5 rounded text-[11px] font-semibold"
                                :class="mrpCompleto ? 'bg-estado-verde text-estado-verde-texto' : 'bg-estado-rojo text-estado-rojo-texto'"
                            >
                                {{ mrpCompleto ? 'Stock suficiente' : 'Faltan insumos' }}
                            </span>
                        </div>
                        <div class="space-y-1 text-xs">
                            <div v-for="(item, idx) in explosionMrp" :key="idx" class="flex items-center justify-between gap-2">
                                <span class="truncate text-foreground">{{ item.nombre }}</span>
                                <span :class="item.alcanza ? 'text-muted-foreground' : 'text-estado-rojo-texto font-semibold'">
                                    Req: {{ item.requerido }} {{ item.unidad }}
                                    <template v-if="!item.alcanza">(Faltan {{ item.faltante }})</template>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Notas de la Orden</label>
                        <input
                            v-model="form.notas"
                            type="text"
                            placeholder="Turno, equipo o indicaciones de planta"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Programar Orden
                    </button>
                </form>
            </section>

            <!-- Fichas técnicas de recetas + Creador de Receta -->
            <section class="lg:col-span-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-titulo font-semibold text-foreground">Fichas Técnicas Oficiales ({{ recetas.length }})</h2>
                    <button
                        v-if="productos && insumos"
                        type="button"
                        @click="mostrarNuevaReceta = !mostrarNuevaReceta"
                        class="px-3.5 py-2 rounded-lg border border-fresa text-fresa hover:bg-fresa hover:text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        {{ mostrarNuevaReceta ? 'Cerrar Formulario' : 'Nueva Fórmula Técnica' }}
                    </button>
                </div>

                <!-- Formulario para crear nueva receta maestra -->
                <form
                    v-if="mostrarNuevaReceta && productos && insumos"
                    @submit.prevent="guardarReceta"
                    class="rounded-lg bg-surface border border-border p-5 space-y-4"
                >
                    <h3 class="text-sm font-titulo font-semibold text-foreground">Registrar Nueva Fórmula Técnica (BOM)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Producto Terminado</label>
                            <select
                                v-model="formReceta.producto_id"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            >
                                <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.sku }} - {{ p.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Nombre de la Fórmula</label>
                            <input
                                v-model="formReceta.nombre"
                                type="text"
                                placeholder="BOM 2 Niveles: Gelawitto de Maracuyá (Lote 5 vasos)"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Rendimiento Base (uds / L)</label>
                            <input
                                v-model="formReceta.rendimiento_unidades"
                                type="number"
                                step="0.5"
                                min="0.5"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Tiempo Preparación (min)</label>
                            <input
                                v-model="formReceta.tiempo_preparacion_min"
                                type="number"
                                min="1"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Instrucciones de Proceso</label>
                        <input
                            v-model="formReceta.instrucciones"
                            type="text"
                            placeholder="Dosificación de base gelificada de arroz, choque térmico y gelificación en REF-01"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold">Componentes BOM por Lote Base (Insumos o Subensambles)</label>
                            <button
                                type="button"
                                @click="agregarIngrediente"
                                class="text-xs font-semibold text-fresa hover:underline cursor-pointer"
                            >
                                + Agregar Componente
                            </button>
                        </div>
                        <div
                            v-for="(ing, idx) in formReceta.ingredientes"
                            :key="idx"
                            class="grid grid-cols-12 gap-2 items-center"
                        >
                            <div class="col-span-3">
                                <select
                                    v-model="ing.tipo_componente"
                                    @change="cambiarTipoComponente(idx)"
                                    class="w-full h-9 px-2 rounded-md border border-input bg-background text-xs"
                                >
                                    <option value="insumo">Insumo / Empaque</option>
                                    <option v-if="subensambles.length > 0" value="subensamble">Subensamble (N1)</option>
                                </select>
                            </div>
                            <div class="col-span-5">
                                <select
                                    v-if="ing.tipo_componente === 'subensamble'"
                                    v-model="ing.subproducto_id"
                                    class="w-full h-9 px-2 rounded-md border border-input bg-background text-xs"
                                >
                                    <option v-for="sp in subensambles" :key="sp.id" :value="sp.id">
                                        {{ sp.sku }} - {{ sp.nombre }}
                                    </option>
                                </select>
                                <select
                                    v-else
                                    v-model="ing.insumo_id"
                                    class="w-full h-9 px-2 rounded-md border border-input bg-background text-xs"
                                >
                                    <option v-for="i in insumos" :key="i.id" :value="i.id">
                                        {{ i.codigo }} - {{ i.nombre }} ({{ i.unidad }})
                                    </option>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input
                                    v-model="ing.cantidad_requerida"
                                    type="number"
                                    step="0.001"
                                    min="0.001"
                                    required
                                    placeholder="Cantidad"
                                    class="w-full h-9 px-2 rounded-md border border-input bg-background text-xs"
                                />
                            </div>
                            <div class="col-span-1 text-right">
                                <button
                                    type="button"
                                    @click="quitarIngrediente(idx)"
                                    class="text-xs text-estado-rojo-texto font-semibold cursor-pointer"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="centros_trabajo && centros_trabajo.length > 0" class="space-y-2 pt-2 border-t border-border">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold">Rutas de Operación y Centros de Trabajo (Opcional)</label>
                            <button
                                type="button"
                                @click="agregarRuta"
                                class="text-xs font-semibold text-fresa hover:underline cursor-pointer"
                            >
                                + Agregar Etapa
                            </button>
                        </div>
                        <div
                            v-for="(ruta, rIdx) in formReceta.rutas"
                            :key="rIdx"
                            class="grid grid-cols-12 gap-2 items-center"
                        >
                            <div class="col-span-4">
                                <select
                                    v-model="ruta.centro_trabajo_id"
                                    class="w-full h-9 px-2 rounded-md border border-input bg-background text-xs"
                                >
                                    <option v-for="ct in centros_trabajo" :key="ct.id" :value="ct.id">
                                        {{ ct.codigo }} - {{ ct.nombre }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-span-4">
                                <input
                                    v-model="ruta.operacion"
                                    type="text"
                                    placeholder="Descripción de la operación"
                                    class="w-full h-9 px-2 rounded-md border border-input bg-background text-xs"
                                />
                            </div>
                            <div class="col-span-2">
                                <input
                                    v-model="ruta.tiempo_preparacion_min"
                                    type="number"
                                    step="0.5"
                                    min="0"
                                    placeholder="Setup min"
                                    class="w-full h-9 px-2 rounded-md border border-input bg-background text-xs"
                                />
                            </div>
                            <div class="col-span-1">
                                <input
                                    v-model="ruta.tiempo_ciclo_min_por_unidad"
                                    type="number"
                                    step="0.1"
                                    min="0.01"
                                    placeholder="Ciclo"
                                    class="w-full h-9 px-2 rounded-md border border-input bg-background text-xs"
                                />
                            </div>
                            <div class="col-span-1 text-right">
                                <button
                                    type="button"
                                    @click="quitarRuta(rIdx)"
                                    class="text-xs text-estado-rojo-texto font-semibold cursor-pointer"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="formReceta.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Guardar Fórmula Técnica
                    </button>
                </form>

                <article
                    v-for="rec in recetas"
                    :key="rec.id"
                    class="rounded-lg bg-surface border border-border overflow-hidden"
                >
                    <div class="px-5 py-4 border-b border-border flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-2 py-0.5 rounded text-[11px] font-semibold"
                                    :class="rec.producto_tipo === 'semielaborado' ? 'bg-amber-100 text-amber-800' : 'bg-estado-verde text-estado-verde-texto'"
                                >
                                    {{ rec.producto_tipo === 'semielaborado' ? 'Nivel 1 Subensamble' : 'Nivel 0 Producto Terminado' }}
                                </span>
                                <h3 class="text-base font-titulo font-semibold text-foreground">{{ rec.nombre }}</h3>
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">{{ rec.instrucciones }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-md text-xs font-semibold bg-secondary text-secondary-foreground">
                            Base: {{ Number(rec.rendimiento_unidades).toFixed(0) }} uds ({{ rec.tiempo_preparacion_min }} min)
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                    <th class="py-2.5 px-4 font-semibold">Nivel BOM</th>
                                    <th class="py-2.5 px-4 font-semibold">Componente / Insumo / Empaque</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Requerido por Base</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Stock en Almacén</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Costo Estimado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <template v-for="(ing, idx) in rec.ingredientes" :key="idx">
                                    <tr :class="ing.es_subensamble ? 'bg-secondary/20' : ''">
                                        <td class="py-2.5 px-4 text-xs font-mono font-semibold text-frambuesa">
                                            Nivel {{ ing.nivel ?? 1 }}
                                        </td>
                                        <td class="py-2.5 px-4 font-medium text-foreground">
                                            {{ ing.insumo_nombre }}
                                            <span v-if="ing.es_subensamble" class="ml-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">
                                                {{ ing.subproducto_sku }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-semibold">
                                            {{ Number(ing.cantidad_requerida).toFixed(3) }} {{ ing.unidad }}
                                        </td>
                                        <td class="py-2.5 px-4 text-right text-xs">
                                            {{ Number(ing.stock_actual).toFixed(2) }} {{ ing.unidad }}
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-medium">
                                            C$ {{ (Number(ing.cantidad_requerida) * Number(ing.costo_promedio)).toFixed(2) }}
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(sub2, sIdx) in ing.componentes_nivel_2 ?? []"
                                        :key="`${idx}-n2-${sIdx}`"
                                        class="bg-muted/20 text-xs text-muted-foreground"
                                    >
                                        <td class="py-1.5 pl-8 pr-4 font-mono">Nivel 2</td>
                                        <td class="py-1.5 px-4">{{ sub2.insumo_nombre }} (por 1 L de subensamble)</td>
                                        <td class="py-1.5 px-4 text-right">{{ Number(sub2.cantidad_requerida).toFixed(3) }} {{ sub2.unidad }}</td>
                                        <td class="py-1.5 px-4 text-right">—</td>
                                        <td class="py-1.5 px-4 text-right">
                                            C$ {{ (Number(sub2.cantidad_requerida) * Number(sub2.costo_promedio)).toFixed(2) }}
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="rec.rutas && rec.rutas.length > 0" class="px-5 py-3 bg-muted/20 border-t border-border">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">
                            Ruta de Operación y Centros de Trabajo
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-xs">
                            <div
                                v-for="r in rec.rutas"
                                :key="r.secuencia"
                                class="p-2.5 rounded-md bg-surface border border-border"
                            >
                                <p class="font-semibold text-foreground">
                                    Op. {{ r.secuencia }} ({{ r.centro_codigo }}): {{ r.centro_nombre }}
                                </p>
                                <p class="text-muted-foreground mt-0.5">{{ r.operacion }}</p>
                                <p class="font-mono text-[11px] text-frambuesa mt-1">
                                    Setup: {{ r.tiempo_preparacion_min }} min | Ciclo: {{ r.tiempo_ciclo_min_por_unidad }} min/ud
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
        </div>

        <!-- Órdenes de producción: programadas y ejecutadas -->
        <section class="rounded-lg bg-surface border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-titulo font-semibold text-foreground">Órdenes de Producción</h2>
                <p class="text-xs text-muted-foreground">
                    Las órdenes programadas se ejecutan al terminar el lote: ahí se descuentan los insumos y se registra la merma
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                            <th class="py-3 px-4 font-semibold">Orden</th>
                            <th class="py-3 px-4 font-semibold">Fecha</th>
                            <th class="py-3 px-4 font-semibold">Producto</th>
                            <th class="py-3 px-4 font-semibold text-right">Programado</th>
                            <th class="py-3 px-4 font-semibold text-right">Obtenido</th>
                            <th class="py-3 px-4 font-semibold text-right">Merma</th>
                            <th class="py-3 px-4 font-semibold text-right">Costo Lote</th>
                            <th class="py-3 px-4 font-semibold text-right">Costo Unitario</th>
                            <th class="py-3 px-4 font-semibold">Estado</th>
                            <th class="py-3 px-4 font-semibold">Ejecución</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="l in lotes" :key="l.id" class="hover:bg-muted/25">
                            <td class="py-3 px-4 font-mono text-xs font-semibold text-frambuesa">{{ l.codigo_lote }}</td>
                            <td class="py-3 px-4 text-xs">{{ l.fecha_produccion }}</td>
                            <td class="py-3 px-4">
                                <p class="font-semibold text-foreground">{{ l.producto_nombre }}</p>
                                <p class="text-xs text-muted-foreground">{{ l.notas }}</p>
                            </td>
                            <td class="py-3 px-4 text-right">{{ Number(l.cantidad_programada).toFixed(1) }}</td>

                            <template v-if="l.estado === 'Programada'">
                                <td class="py-3 px-4 text-right text-muted-foreground">—</td>
                                <td class="py-3 px-4 text-right text-muted-foreground">—</td>
                                <td class="py-3 px-4 text-right text-muted-foreground">—</td>
                                <td class="py-3 px-4 text-right text-muted-foreground">—</td>
                            </template>
                            <template v-else>
                                <td class="py-3 px-4 text-right font-semibold">{{ Number(l.cantidad_obtenida).toFixed(1) }}</td>
                                <td class="py-3 px-4 text-right text-estado-rojo-texto">{{ Number(l.merma).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-right">C$ {{ Number(l.costo_total_lote).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold">C$ {{ Number(l.costo_unitario_resultante).toFixed(2) }}</td>
                            </template>

                            <td class="py-3 px-4">
                                <span
                                    v-if="l.estado === 'Programada'"
                                    class="px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800"
                                >
                                    {{ l.estado }}
                                </span>
                                <span
                                    v-else
                                    class="px-2.5 py-0.5 rounded text-xs font-semibold bg-estado-verde text-estado-verde-texto"
                                >
                                    {{ l.estado }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <div v-if="l.estado === 'Programada'" class="flex flex-col gap-1.5 min-w-[175px]">
                                    <input
                                        v-model="obtenidoEjecucion[l.id]"
                                        type="number"
                                        step="0.1"
                                        min="0.5"
                                        :placeholder="`Obtenido (meta ${Number(l.cantidad_programada).toFixed(1)})`"
                                        class="h-8 px-2 rounded-md border border-input bg-background text-xs"
                                    />
                                    <div class="grid grid-cols-2 gap-1">
                                        <input
                                            v-model="paroEjecucion[l.id]"
                                            type="number"
                                            step="0.5"
                                            min="0"
                                            placeholder="Paro (min)"
                                            class="h-7 px-2 rounded-md border border-input bg-background text-[11px]"
                                        />
                                        <input
                                            v-model="vencimientoEjecucion[l.id]"
                                            type="date"
                                            title="Fecha de vencimiento FEFO"
                                            class="h-7 px-1.5 rounded-md border border-input bg-background text-[11px]"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        :disabled="ejecutandoId === l.id"
                                        @click="ejecutarLote(l)"
                                        class="h-8 px-2 rounded-md bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer disabled:opacity-60"
                                    >
                                        Ejecutar lote
                                    </button>
                                </div>
                                <span v-else class="text-xs text-muted-foreground">{{ l.operador_nombre }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </ErpLayout>
</template>
