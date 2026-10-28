<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ErpLayout from '@/Layouts/ErpLayout.vue';

const props = defineProps<{
    insumos: Array<{
        id: number;
        codigo: string;
        nombre: string;
        categoria: string;
        unidad: string;
        stock_actual: string;
        stock_minimo: string;
        costo_promedio: string;
    }>;
    productos: Array<{
        id: number;
        sku: string;
        nombre: string;
        presentacion: string;
        stock_actual: string;
        stock_minimo: string;
        costo_unitario: string;
    }>;
    kardex: Array<{
        id: number;
        fecha: string;
        tipo_item: string;
        nombre_item: string;
        tipo_movimiento: string;
        referencia: string;
        cantidad: string;
        stock_resultante: string;
        costo_unitario: string;
        responsable: string;
    }>;
}>();

const form = useForm({
    tipo_item: 'insumo' as 'insumo' | 'producto',
    item_id: props.insumos[0]?.id ?? 1,
    tipo_movimiento: 'Merma',
    cantidad: '1.000',
    motivo: '',
});

const articulosDisponibles = computed(() => {
    if (form.tipo_item === 'insumo') {
        return props.insumos.map((i) => ({ id: i.id, etiqueta: `${i.codigo} - ${i.nombre} (Stock: ${Number(i.stock_actual).toFixed(2)} ${i.unidad})` }));
    }
    return props.productos.map((p) => ({ id: p.id, etiqueta: `${p.sku} - ${p.nombre} (Stock: ${Number(p.stock_actual).toFixed(1)} uds)` }));
});

function cambiarTipoItem() {
    form.item_id = articulosDisponibles.value[0]?.id ?? 1;
}

function registrarAjuste() {
    form.post('/inventario/ajustes', {
        onSuccess: () => form.reset('motivo', 'cantidad'),
    });
}
</script>

<template>
    <Head title="Inventario y Kardex" />

    <ErpLayout
        title="Inventario y Libro de Kardex"
        subtitle="Control de existencias por almacén, registro de mermas y auditoría de movimientos inmutables"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Formulario de ajuste o merma -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Registrar Merma o Ajuste</h2>
                <p class="text-xs text-muted-foreground mb-4">
                    Toda modificación genera un asiento trazable en el Kardex inmutable
                </p>

                <form @submit.prevent="registrarAjuste" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Tipo de Existencia</label>
                        <select
                            v-model="form.tipo_item"
                            @change="cambiarTipoItem"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option value="insumo">Materia Prima e Insumos</option>
                            <option value="producto">Producto Terminado (Helados)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Artículo</label>
                        <select
                            v-model="form.item_id"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option v-for="art in articulosDisponibles" :key="art.id" :value="art.id">
                                {{ art.etiqueta }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Naturaleza</label>
                            <select
                                v-model="form.tipo_movimiento"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            >
                                <option value="Merma">Merma Operativa (-)</option>
                                <option value="Ajuste Negativo">Ajuste Faltante (-)</option>
                                <option value="Ajuste Positivo">Ajuste Sobrante (+)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Cantidad</label>
                            <input
                                v-model="form.cantidad"
                                type="number"
                                step="0.001"
                                min="0.001"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>
                    <p v-if="form.errors.cantidad" class="text-xs text-estado-rojo-texto">{{ form.errors.cantidad }}</p>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Justificación Técnica</label>
                        <input
                            v-model="form.motivo"
                            type="text"
                            required
                            placeholder="Ej. Pérdida por maduración o conteo físico"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Asentar Movimiento en Kardex
                    </button>
                </form>
            </section>

            <!-- Existencias de insumos -->
            <section class="lg:col-span-8 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h2 class="text-base font-titulo font-semibold text-foreground">Existencias Valorizadas de Insumos</h2>
                    <p class="text-xs text-muted-foreground">Monitoreo de niveles mínimos y valoración al costo promedio</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">Código</th>
                                <th class="py-3 px-4 font-semibold">Insumo</th>
                                <th class="py-3 px-4 font-semibold text-right">Stock Actual</th>
                                <th class="py-3 px-4 font-semibold text-right">Mínimo</th>
                                <th class="py-3 px-4 font-semibold text-right">Valor Total</th>
                                <th class="py-3 px-4 font-semibold">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="ins in insumos" :key="ins.id" class="hover:bg-muted/25">
                                <td class="py-3 px-4 font-mono text-xs font-semibold text-frambuesa">{{ ins.codigo }}</td>
                                <td class="py-3 px-4 font-semibold text-foreground">{{ ins.nombre }}</td>
                                <td class="py-3 px-4 text-right font-semibold">{{ Number(ins.stock_actual).toFixed(2) }} {{ ins.unidad }}</td>
                                <td class="py-3 px-4 text-right text-xs text-muted-foreground">{{ Number(ins.stock_minimo).toFixed(1) }} {{ ins.unidad }}</td>
                                <td class="py-3 px-4 text-right font-medium">
                                    C$ {{ (Number(ins.stock_actual) * Number(ins.costo_promedio)).toFixed(2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-2.5 py-0.5 rounded text-xs font-semibold"
                                        :class="
                                            Number(ins.stock_actual) <= Number(ins.stock_minimo)
                                                ? 'bg-estado-rojo text-estado-rojo-texto'
                                                : 'bg-estado-verde text-estado-verde-texto'
                                        "
                                    >
                                        {{ Number(ins.stock_actual) <= Number(ins.stock_minimo) ? 'Reabastecer' : 'Óptimo' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Libro de Kardex -->
        <section class="rounded-lg bg-surface border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-titulo font-semibold text-foreground">Libro Mayor de Movimientos (Kardex Inmutable)</h2>
                <p class="text-xs text-muted-foreground">Historial cronológico de todas las entradas, consumos de lote, ventas y mermas</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                            <th class="py-3 px-4 font-semibold">Fecha</th>
                            <th class="py-3 px-4 font-semibold">Referencia</th>
                            <th class="py-3 px-4 font-semibold">Tipo</th>
                            <th class="py-3 px-4 font-semibold">Artículo</th>
                            <th class="py-3 px-4 font-semibold text-right">Cantidad</th>
                            <th class="py-3 px-4 font-semibold text-right">Saldo Resultante</th>
                            <th class="py-3 px-4 font-semibold text-right">Costo Unitario</th>
                            <th class="py-3 px-4 font-semibold">Operador</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="k in kardex" :key="k.id" class="hover:bg-muted/25">
                            <td class="py-3 px-4 text-xs text-muted-foreground">{{ String(k.fecha).slice(0, 16) }}</td>
                            <td class="py-3 px-4 font-mono text-xs font-semibold text-frambuesa">{{ k.referencia }}</td>
                            <td class="py-3 px-4 text-xs font-semibold">{{ k.tipo_movimiento }}</td>
                            <td class="py-3 px-4 font-medium text-foreground">{{ k.nombre_item }}</td>
                            <td
                                class="py-3 px-4 text-right font-semibold"
                                :class="Number(k.cantidad) >= 0 ? 'text-estado-verde-texto' : 'text-estado-rojo-texto'"
                            >
                                {{ Number(k.cantidad) >= 0 ? '+' : '' }}{{ Number(k.cantidad).toFixed(3) }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold">{{ Number(k.stock_resultante).toFixed(3) }}</td>
                            <td class="py-3 px-4 text-right text-xs">C$ {{ Number(k.costo_unitario).toFixed(2) }}</td>
                            <td class="py-3 px-4 text-xs text-muted-foreground">{{ k.responsable }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </ErpLayout>
</template>
