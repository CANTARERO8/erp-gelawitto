<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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
        cantidad_obtenida: string;
        merma: string;
        costo_total_lote: string;
        costo_unitario_resultante: string;
        estado: string;
        notas: string;
    }>;
}>();

const form = useForm({
    receta_id: props.recetas[0]?.id ?? 1,
    cantidad_programada: '10',
    cantidad_obtenida: '9.8',
    notas: '',
});

function ejecutarLote() {
    form.post('/produccion/lotes', {
        onSuccess: () => form.reset('notas'),
    });
}
</script>

<template>
    <Head title="Recetas y Producción" />

    <ErpLayout
        title="Formulación y Producción por Lotes"
        subtitle="Fichas técnicas de heladería, descuento transaccional de insumos y costeo real por lote"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Formulario de ejecución de lote -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Procesar Nuevo Lote</h2>
                <p class="text-xs text-muted-foreground mb-4">
                    Descuenta automáticamente los insumos de la receta e ingresa el helado terminado al Kardex
                </p>

                <form @submit.prevent="ejecutarLote" class="space-y-3.5">
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

                    <div class="grid grid-cols-2 gap-3">
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
                            <label class="block text-xs font-semibold mb-1">Obtenido Real (uds)</label>
                            <input
                                v-model="form.cantidad_obtenida"
                                type="number"
                                step="0.1"
                                min="0.5"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Notas de Planta y Mantecación</label>
                        <input
                            v-model="form.notas"
                            type="text"
                            placeholder="Temperatura de salida, overrun y textura"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Ejecutar Lote y Actualizar Inventario
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

        <!-- Historial de lotes producidos -->
        <section class="rounded-lg bg-surface border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-titulo font-semibold text-foreground">Historial de Lotes de Producción</h2>
                <p class="text-xs text-muted-foreground">Registro de órdenes de planta, mermas de proceso y costo unitario resultante</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                            <th class="py-3 px-4 font-semibold">Lote</th>
                            <th class="py-3 px-4 font-semibold">Fecha</th>
                            <th class="py-3 px-4 font-semibold">Producto</th>
                            <th class="py-3 px-4 font-semibold text-right">Programado</th>
                            <th class="py-3 px-4 font-semibold text-right">Obtenido</th>
                            <th class="py-3 px-4 font-semibold text-right">Merma</th>
                            <th class="py-3 px-4 font-semibold text-right">Costo Lote</th>
                            <th class="py-3 px-4 font-semibold text-right">Costo Unitario</th>
                            <th class="py-3 px-4 font-semibold">Maestro Heladero</th>
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
                            <td class="py-3 px-4 text-right font-semibold">{{ Number(l.cantidad_obtenida).toFixed(1) }}</td>
                            <td class="py-3 px-4 text-right text-estado-rojo-texto">{{ Number(l.merma).toFixed(2) }}</td>
                            <td class="py-3 px-4 text-right">${{ Number(l.costo_total_lote).toFixed(2) }}</td>
                            <td class="py-3 px-4 text-right font-semibold">${{ Number(l.costo_unitario_resultante).toFixed(2) }}</td>
                            <td class="py-3 px-4 text-xs text-muted-foreground">{{ l.operador_nombre }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </ErpLayout>
</template>
