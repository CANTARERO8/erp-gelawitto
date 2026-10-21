<script setup lang="ts">
import { reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ErpLayout from '@/Layouts/ErpLayout.vue';

const props = defineProps<{
    recetas: Array<{
        id: number;
        nombre: string;
        producto_nombre: string;
        producto_sku: string;
        sabor: string;
        rendimiento_unidades: string;
        tiempo_preparacion_min: number;
        instrucciones: string;
        ingredientes: Array<{
            insumo_nombre: string;
            cantidad_requerida: string;
            merma_estimada_porcentaje: string;
            stock_actual: string;
            costo_promedio: string;
            unidad: string;
        }>;
    }>;
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
}>();

const form = useForm({
    receta_id: props.recetas[0]?.id ?? 1,
    cantidad_programada: '10',
    notas: '',
});

// Cantidad obtenida que el operador captura al ejecutar cada orden, indexada por id de lote.
const obtenidoEjecucion = reactive<Record<number, string>>({});
const ejecutandoId = ref<number | null>(null);

function programarLote() {
    form.post('/produccion/lotes', {
        onSuccess: () => form.reset('notas'),
    });
}

function ejecutarLote(lote: { id: number; cantidad_programada: string }) {
    router.post(
        `/produccion/lotes/${lote.id}/ejecutar`,
        { cantidad_obtenida: obtenidoEjecucion[lote.id] ?? lote.cantidad_programada },
        {
            preserveScroll: true,
            onStart: () => (ejecutandoId.value = lote.id),
            onFinish: () => (ejecutandoId.value = null),
        }
    );
}
</script>

<template>
    <Head title="Recetas y Producción" />

    <ErpLayout
        title="Formulación y Producción por Lotes"
        subtitle="Órdenes de producción programadas, ejecución con descuento transaccional de insumos y costeo real por lote"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Paso 1: programar orden de producción -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Programar Orden de Producción</h2>
                <p class="text-xs text-muted-foreground mb-4">
                    Planifica el lote. Los insumos se descuentan y el producto entra al Kardex al ejecutarlo
                </p>

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

            <!-- Fichas técnicas de recetas -->
            <section class="lg:col-span-8 space-y-4">
                <article
                    v-for="rec in recetas"
                    :key="rec.id"
                    class="rounded-lg bg-surface border border-border overflow-hidden"
                >
                    <div class="px-5 py-4 border-b border-border flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-base font-titulo font-semibold text-foreground">{{ rec.nombre }}</h3>
                            <p class="text-xs text-muted-foreground">{{ rec.instrucciones }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-md text-xs font-semibold bg-secondary text-secondary-foreground">
                            Base: {{ Number(rec.rendimiento_unidades).toFixed(0) }} tinas ({{ rec.tiempo_preparacion_min }} min)
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                    <th class="py-2.5 px-4 font-semibold">Ingrediente / Empaque</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Requerido por Base</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Stock en Almacén</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Costo Estimado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="(ing, idx) in rec.ingredientes" :key="idx">
                                    <td class="py-2.5 px-4 font-medium text-foreground">{{ ing.insumo_nombre }}</td>
                                    <td class="py-2.5 px-4 text-right font-semibold">
                                        {{ Number(ing.cantidad_requerida).toFixed(2) }} {{ ing.unidad }}
                                    </td>
                                    <td class="py-2.5 px-4 text-right text-xs">
                                        {{ Number(ing.stock_actual).toFixed(2) }} {{ ing.unidad }}
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-medium">
                                        ${{ (Number(ing.cantidad_requerida) * Number(ing.costo_promedio)).toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                                <td class="py-3 px-4 text-right">${{ Number(l.costo_total_lote).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold">${{ Number(l.costo_unitario_resultante).toFixed(2) }}</td>
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
                                <div v-if="l.estado === 'Programada'" class="flex flex-col gap-1.5">
                                    <input
                                        v-model="obtenidoEjecucion[l.id]"
                                        type="number"
                                        step="0.1"
                                        min="0.5"
                                        :placeholder="`Obtenido (meta ${Number(l.cantidad_programada).toFixed(1)})`"
                                        class="h-8 px-2 rounded-md border border-input bg-background text-xs"
                                    />
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
