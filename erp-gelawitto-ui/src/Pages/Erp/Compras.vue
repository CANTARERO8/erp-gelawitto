<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useFormulario, ejecutarAccionApi } from '@/composables/useFormulario';
import { useAuth } from '@/composables/useAuth';
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
    iva_porcentaje?: number;
}>();

const { ivaPorcentaje } = useAuth();
const porcentajeIva = computed(() => Number(props.iva_porcentaje ?? ivaPorcentaje.value));
const tasaIva = computed(() => porcentajeIva.value / 100);

const form = useFormulario({
    proveedor_id: props.proveedores[0]?.id ?? 1,
    aplicar_iva: false,
    notas: '',
    lineas: [
        {
            insumo_id: props.insumos[0]?.id ?? 1,
            cantidad: '20',
            costo_unitario: props.insumos[0]?.costo_promedio ?? '120.00',
        },
    ],
});

const modalidadRecepcion = reactive<Record<number, string>>({});
const recibiendoId = ref<number | null>(null);

function agregarLinea() {
    const insumoDefault = props.insumos[0];
    form.lineas.push({
        insumo_id: insumoDefault?.id ?? 1,
        cantidad: '10',
        costo_unitario: insumoDefault?.costo_promedio ?? '100.00',
    });
}

function quitarLinea(idx: number) {
    if (form.lineas.length > 1) {
        form.lineas.splice(idx, 1);
    }
}

function actualizarCostoSugerido(idx: number) {
    const linea = form.lineas[idx];
    const seleccionado = props.insumos.find((i) => i.id === Number(linea.insumo_id));
    if (seleccionado) {
        linea.costo_unitario = String(Number(seleccionado.costo_promedio).toFixed(2));
    }
}

const subtotalEstimado = computed(() => {
    return form.lineas.reduce((acc: number, l: any) => acc + Number(l.cantidad || 0) * Number(l.costo_unitario || 0), 0);
});

const ivaEstimado = computed(() => {
    return form.aplicar_iva ? subtotalEstimado.value * tasaIva.value : 0;
});

const totalEstimado = computed(() => {
    return (subtotalEstimado.value + ivaEstimado.value).toFixed(2);
});

function crearOrden() {
    form.post('/compras', {
        onSuccess: () => {
            form.notas = '';
            form.aplicar_iva = false;
            form.lineas = [
                {
                    insumo_id: props.insumos[0]?.id ?? 1,
                    cantidad: '20',
                    costo_unitario: props.insumos[0]?.costo_promedio ?? '120.00',
                },
            ];
        },
    });
}

async function recibirOrden(oc: { id: number }) {
    recibiendoId.value = oc.id;
    await ejecutarAccionApi(
        `/compras/${oc.id}/recibir`,
        { modalidad_pago: modalidadRecepcion[oc.id] ?? 'Pagado' },
        {
            onFinish: () => {
                recibiendoId.value = null;
            },
        }
    );
}
</script>

<template>
    <ErpLayout
        title="Compras y Abastecimiento"
        :subtitle="`Órdenes de compra multi-ítem con IVA opcional (${porcentajeIva}%), recepción en almacén, costo promedio ponderado y asiento contable automático`"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Paso 1: orden de compra -->
            <section class="lg:col-span-5 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Nueva Orden de Compra</h2>
                <p class="text-xs text-muted-foreground mb-4">
                    Registra uno o varios insumos en la misma orden. El stock e impacto contable se aplican al recibirla en almacén
                </p>

                <form @submit.prevent="crearOrden" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Proveedor Autorizado</label>
                        <select
                            v-model="form.proveedor_id"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold">Partidas de Insumos ({{ form.lineas.length }})</label>
                            <button
                                type="button"
                                @click="agregarLinea"
                                class="text-xs font-semibold text-fresa hover:text-frambuesa cursor-pointer"
                            >
                                + Agregar Insumo
                            </button>
                        </div>

                        <div
                            v-for="(linea, idx) in form.lineas"
                            :key="idx"
                            class="p-3 rounded-lg bg-background border border-border space-y-2"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] uppercase font-semibold text-muted-foreground">Partida #{{ idx + 1 }}</span>
                                <button
                                    v-if="form.lineas.length > 1"
                                    type="button"
                                    @click="quitarLinea(idx)"
                                    class="text-xs text-estado-rojo-texto hover:underline cursor-pointer"
                                >
                                    Quitar
                                </button>
                            </div>

                            <select
                                v-model="linea.insumo_id"
                                @change="actualizarCostoSugerido(idx)"
                                class="w-full h-9 px-2.5 rounded-md border border-input bg-surface text-xs"
                            >
                                <option v-for="i in insumos" :key="i.id" :value="i.id">
                                    {{ i.codigo }} - {{ i.nombre }} ({{ i.unidad }})
                                </option>
                            </select>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-semibold text-muted-foreground mb-0.5">Cantidad</label>
                                    <input
                                        v-model="linea.cantidad"
                                        type="number"
                                        step="0.001"
                                        min="0.001"
                                        required
                                        class="w-full h-9 px-2.5 rounded-md border border-input bg-surface text-xs"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-muted-foreground mb-0.5">Costo Unitario (C$)</label>
                                    <input
                                        v-model="linea.costo_unitario"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        required
                                        class="w-full h-9 px-2.5 rounded-md border border-input bg-surface text-xs"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-xs font-semibold text-foreground cursor-pointer select-none">
                        <input v-model="form.aplicar_iva" type="checkbox" class="rounded border-input" />
                        Aplicar IVA Acreditable ({{ porcentajeIva }}%)
                    </label>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Notas de la Orden</label>
                        <input
                            v-model="form.notas"
                            type="text"
                            placeholder="Condiciones pactadas con el proveedor"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <div class="p-3 rounded-lg bg-muted/60 border border-border space-y-1">
                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span>Subtotal</span>
                            <span>C$ {{ subtotalEstimado.toFixed(2) }}</span>
                        </div>
                        <div v-if="form.aplicar_iva" class="flex items-center justify-between text-xs text-muted-foreground">
                            <span>IVA Acreditable ({{ porcentajeIva }}%)</span>
                            <span>C$ {{ ivaEstimado.toFixed(2) }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-border">
                            <span class="text-xs font-semibold text-foreground">Total de la Orden</span>
                            <span class="text-lg font-titulo font-bold text-foreground">C$ {{ totalEstimado }}</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Crear Orden de Compra
                    </button>
                </form>
            </section>

            <!-- Paso 2: recepción y historial -->
            <section class="lg:col-span-7 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h2 class="text-base font-titulo font-semibold text-foreground">Órdenes de Compra</h2>
                    <p class="text-xs text-muted-foreground">
                        Las órdenes pendientes se reciben en almacén para incrementar el stock y registrar el compromiso en Finanzas y Contabilidad
                    </p>
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
                                <th class="py-3 px-4 font-semibold">Recepción</th>
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
                                    <span
                                        v-if="oc.estado === 'Pendiente'"
                                        class="px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800"
                                    >
                                        {{ oc.estado }}
                                    </span>
                                    <span
                                        v-else
                                        class="px-2.5 py-0.5 rounded text-xs font-semibold bg-estado-verde text-estado-verde-texto"
                                    >
                                        {{ oc.estado }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold">C$ {{ Number(oc.total).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-xs text-muted-foreground">{{ oc.comprador_nombre }}</td>
                                <td class="py-3 px-4">
                                    <div v-if="oc.estado === 'Pendiente'" class="flex flex-col gap-1.5">
                                        <select
                                            v-model="modalidadRecepcion[oc.id]"
                                            class="h-8 px-2 rounded-md border border-input bg-background text-xs"
                                        >
                                            <option value="Pagado">Contado (Egreso)</option>
                                            <option value="Pendiente">Crédito 15 días</option>
                                        </select>
                                        <button
                                            type="button"
                                            :disabled="recibiendoId === oc.id"
                                            @click="recibirOrden(oc)"
                                            class="h-8 px-2 rounded-md bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer disabled:opacity-60"
                                        >
                                            Recibir en almacén
                                        </button>
                                    </div>
                                    <span v-else class="text-xs text-muted-foreground">Recibida</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </ErpLayout>
</template>
