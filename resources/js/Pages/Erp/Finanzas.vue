<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import ErpLayout from '@/Layouts/ErpLayout.vue';

defineProps<{
    movimientos: Array<{
        id: number;
        folio: string;
        fecha: string;
        tipo: string;
        categoria: string;
        concepto: string;
        referencia: string;
        monto: string;
        estado: string;
        fecha_vencimiento: string | null;
        responsable: string;
    }>;
    resumen: {
        ingresos: number;
        egresos: number;
        flujo_neto: number;
        cuentas_por_pagar: number;
    };
}>();

const form = useForm({
    categoria: 'Servicios de Planta',
    concepto: '',
    monto: '',
    estado: 'Pagado',
});

function guardarGasto() {
    form.post('/finanzas/gastos', {
        onSuccess: () => form.reset('concepto', 'monto'),
    });
}

function liquidar(id: number) {
    router.post(`/finanzas/liquidar/${id}`);
}
</script>

<template>
    <Head title="Finanzas y Cuentas" />

    <ErpLayout
        title="Finanzas Operativas y Cuentas por Pagar"
        subtitle="Balance de ingresos, egresos de operación y liquidación de facturas a proveedores"
    >
        <!-- Tarjetas de resumen financiero -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <article class="rounded-lg bg-surface border border-border p-4">
                <p class="text-xs uppercase font-semibold text-muted-foreground">Ingresos Cobrados</p>
                <p class="text-2xl font-titulo font-bold text-estado-verde-texto mt-1">${{ resumen.ingresos.toFixed(2) }}</p>
            </article>
            <article class="rounded-lg bg-surface border border-border p-4">
                <p class="text-xs uppercase font-semibold text-muted-foreground">Egresos Pagados</p>
                <p class="text-2xl font-titulo font-bold text-foreground mt-1">${{ resumen.egresos.toFixed(2) }}</p>
            </article>
            <article class="rounded-lg bg-surface border border-border p-4">
                <p class="text-xs uppercase font-semibold text-muted-foreground">Flujo Neto de Caja</p>
                <p
                    class="text-2xl font-titulo font-bold mt-1"
                    :class="resumen.flujo_neto >= 0 ? 'text-foreground' : 'text-estado-rojo-texto'"
                >
                    ${{ resumen.flujo_neto.toFixed(2) }}
                </p>
            </article>
            <article class="rounded-lg bg-surface border border-border p-4">
                <p class="text-xs uppercase font-semibold text-muted-foreground">Cuentas por Pagar</p>
                <p class="text-2xl font-titulo font-bold text-fresa mt-1">${{ resumen.cuentas_por_pagar.toFixed(2) }}</p>
            </article>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Registro de gasto o cuenta por pagar -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Registrar Egreso Operativo</h2>
                <p class="text-xs text-muted-foreground mb-4">Asienta gastos de energía, mantenimiento, logística o compromisos</p>

                <form @submit.prevent="guardarGasto" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Categoría Contable</label>
                        <select
                            v-model="form.categoria"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option value="Servicios de Planta">Servicios de Planta (Energía / Agua)</option>
                            <option value="Cadena de Frío">Mantenimiento Cadena de Frío</option>
                            <option value="Logística y Reparto">Logística y Reparto</option>
                            <option value="Arrendamiento">Arrendamiento de Local</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Concepto Detallado</label>
                        <input
                            v-model="form.concepto"
                            type="text"
                            required
                            placeholder="Ej. Mantenimiento preventivo de mantecadora"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Monto ($)</label>
                            <input
                                v-model="form.monto"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Estatus</label>
                            <select
                                v-model="form.estado"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            >
                                <option value="Pagado">Pagado</option>
                                <option value="Pendiente">Por Pagar</option>
                            </select>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Asentar Movimiento Financiero
                    </button>
                </form>
            </section>

            <!-- Tabla de movimientos financieros -->
            <section class="lg:col-span-8 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h2 class="text-base font-titulo font-semibold text-foreground">Libro de Movimientos Financieros</h2>
                    <p class="text-xs text-muted-foreground">Ingresos por ventas, pagos a proveedores y obligaciones pendientes</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">Folio</th>
                                <th class="py-3 px-4 font-semibold">Fecha</th>
                                <th class="py-3 px-4 font-semibold">Concepto</th>
                                <th class="py-3 px-4 font-semibold">Tipo</th>
                                <th class="py-3 px-4 font-semibold text-right">Monto</th>
                                <th class="py-3 px-4 font-semibold">Estado</th>
                                <th class="py-3 px-4 font-semibold text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="m in movimientos" :key="m.id" class="hover:bg-muted/25">
                                <td class="py-3 px-4 font-mono text-xs font-semibold text-frambuesa">{{ m.folio }}</td>
                                <td class="py-3 px-4 text-xs">{{ m.fecha }}</td>
                                <td class="py-3 px-4">
                                    <p class="font-medium text-foreground">{{ m.concepto }}</p>
                                    <p class="text-xs text-muted-foreground">{{ m.categoria }}</p>
                                </td>
                                <td class="py-3 px-4 text-xs font-semibold">{{ m.tipo }}</td>
                                <td
                                    class="py-3 px-4 text-right font-semibold"
                                    :class="m.tipo === 'Ingreso' ? 'text-estado-verde-texto' : 'text-foreground'"
                                >
                                    ${{ Number(m.monto).toFixed(2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-2.5 py-0.5 rounded text-xs font-semibold"
                                        :class="
                                            m.estado === 'Pagado'
                                                ? 'bg-estado-verde text-estado-verde-texto'
                                                : 'bg-estado-rojo text-estado-rojo-texto'
                                        "
                                    >
                                        {{ m.estado }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button
                                        v-if="m.estado === 'Pendiente'"
                                        type="button"
                                        @click="liquidar(m.id)"
                                        class="px-3 py-1 rounded bg-fresa hover:bg-frambuesa text-white text-xs font-semibold cursor-pointer"
                                    >
                                        Liquidar
                                    </button>
                                    <span v-else class="text-xs text-muted-foreground">Cerrado</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </ErpLayout>
</template>
