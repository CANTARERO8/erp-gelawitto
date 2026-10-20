<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ErpLayout from '@/Layouts/ErpLayout.vue';

const props = defineProps<{
    ordenes: Array<{
        id: number;
        folio: string;
        proveedor_nombre: string;
        comprador_nombre: string;
        fecha_emision: string;
        estado: string;
        total: string;
        notas: string;
    }>;
    proveedores: Array<{ id: number; nombre: string }>;
    insumos: Array<{
        id: number;
        codigo: string;
        nombre: string;
        costo_promedio: string;
        unidad: string;
    }>;
}>();

const form = useForm({
    proveedor_id: props.proveedores[0]?.id ?? 1,
    insumo_id: props.insumos[0]?.id ?? 1,
    cantidad: '20',
    costo_unitario: props.insumos[0]?.costo_promedio ?? '1.50',
    modalidad_pago: 'Pagado',
    notas: '',
});

const totalEstimado = computed(() => {
    return (Number(form.cantidad || 0) * Number(form.costo_unitario || 0)).toFixed(2);
});

function actualizarCostoSugerido() {
    const seleccionado = props.insumos.find((i) => i.id === Number(form.insumo_id));
    if (seleccionado) {
        form.costo_unitario = String(Number(seleccionado.costo_promedio).toFixed(2));
    }
}

function registrarCompra() {
    form.post('/compras', {
        onSuccess: () => form.reset('notas'),
    });
}
</script>

<template>
    <Head title="Compras a Proveedores" />

    <ErpLayout
        title="Compras y Abastecimiento"
        subtitle="Recepción de materia prima con actualización de costo promedio ponderado y asiento en Kardex"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Formulario de recepción de orden de compra -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Recibir Orden de Compra</h2>
                <p class="text-xs text-muted-foreground mb-4">
                    Incrementa el stock del insumo y registra el compromiso en Finanzas
                </p>

                <form @submit.prevent="registrarCompra" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Proveedor Autorizado</label>
                        <select
                            v-model="form.proveedor_id"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Insumo Recibido</label>
                        <select
                            v-model="form.insumo_id"
                            @change="actualizarCostoSugerido"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option v-for="i in insumos" :key="i.id" :value="i.id">
                                {{ i.codigo }} - {{ i.nombre }} ({{ i.unidad }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Cantidad Ingresada</label>
                            <input
                                v-model="form.cantidad"
                                type="number"
                                step="0.001"
                                min="0.001"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Costo Unitario ($)</label>
                            <input
                                v-model="form.costo_unitario"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Condición Financiera</label>
                        <select
                            v-model="form.modalidad_pago"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option value="Pagado">Contado (Egreso inmediato)</option>
                            <option value="Pendiente">Crédito 15 días (Cuenta por Pagar)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Observaciones de Recepción</label>
                        <input
                            v-model="form.notas"
                            type="text"
                            placeholder="Control de temperatura y lote del proveedor"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <div class="p-3 rounded-lg bg-muted/60 border border-border flex items-center justify-between">
                        <span class="text-xs font-semibold text-muted-foreground">Total de la Orden</span>
                        <span class="text-lg font-titulo font-bold text-foreground">${{ totalEstimado }}</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Confirmar Recepción e Ingresar a Almacén
                    </button>
                </form>
            </section>

            <!-- Historial de órdenes de compra -->
            <section class="lg:col-span-8 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h2 class="text-base font-titulo font-semibold text-foreground">Órdenes de Compra Registradas</h2>
                    <p class="text-xs text-muted-foreground">Documentos de abastecimiento recibidos en bodega</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">Folio</th>
                                <th class="py-3 px-4 font-semibold">Fecha</th>
                                <th class="py-3 px-4 font-semibold">Proveedor</th>
                                <th class="py-3 px-4 font-semibold">Estado</th>
                                <th class="py-3 px-4 font-semibold text-right">Importe Total</th>
                                <th class="py-3 px-4 font-semibold">Responsable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="oc in ordenes" :key="oc.id" class="hover:bg-muted/25">
                                <td class="py-3 px-4 font-mono text-xs font-semibold text-frambuesa">{{ oc.folio }}</td>
                                <td class="py-3 px-4 text-xs">{{ oc.fecha_emision }}</td>
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-foreground">{{ oc.proveedor_nombre }}</p>
                                    <p class="text-xs text-muted-foreground">{{ oc.notas }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-estado-verde text-estado-verde-texto">
                                        {{ oc.estado }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold">${{ Number(oc.total).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-xs text-muted-foreground">{{ oc.comprador_nombre }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </ErpLayout>
</template>
