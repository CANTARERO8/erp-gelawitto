<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ErpLayout from '@/Layouts/ErpLayout.vue';

const props = defineProps<{
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
    cuentas_por_cobrar?: number;
    contabilidad?: {
        asientos: Array<{
            id: number;
            numero_asiento: string;
            fecha: string;
            modulo_origen: string;
            referencia: string;
            concepto: string;
            total_debe: string;
            total_haber: string;
            responsable: string;
            partidas: Array<{
                codigo: string;
                cuenta: string;
                descripcion: string;
                debe: string;
                haber: string;
            }>;
        }>;
        balanza: Array<{
            codigo: string;
            nombre: string;
            tipo: string;
            naturaleza: string;
            debe: number;
            haber: number;
            saldo: number;
        }>;
        estado_resultados: {
            ingresos_ventas: number;
            costo_ventas: number;
            utilidad_bruta: number;
            gastos_operativos: number;
            mermas_scrap: number;
            utilidad_neta: number;
        };
        balance_general: {
            total_activos: number;
            total_pasivos: number;
            capital_patrimonio: number;
            utilidad_ejercicio: number;
            total_pasivo_mas_patrimonio: number;
        };
    };
}>();

const tabActiva = ref<'flujo' | 'diario' | 'estados'>('flujo');

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
    <Head title="Finanzas y Contabilidad" />

    <ErpLayout
        title="Finanzas Operativas y Contabilidad General"
        subtitle="Flujo de caja, cuentas por cobrar y pagar, Libro Diario de Partida Doble, Estado de Resultados y Balance General"
    >
        <!-- Tarjetas de resumen financiero -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <article class="rounded-lg bg-surface border border-border p-4">
                <p class="text-xs uppercase font-semibold text-muted-foreground">Ingresos Cobrados</p>
                <p class="text-xl font-titulo font-bold text-estado-verde-texto mt-1">C$ {{ resumen.ingresos.toFixed(2) }}</p>
            </article>
            <article class="rounded-lg bg-surface border border-border p-4">
                <p class="text-xs uppercase font-semibold text-muted-foreground">Egresos Pagados</p>
                <p class="text-xl font-titulo font-bold text-foreground mt-1">C$ {{ resumen.egresos.toFixed(2) }}</p>
            </article>
            <article class="rounded-lg bg-surface border border-border p-4">
                <p class="text-xs uppercase font-semibold text-muted-foreground">Flujo Neto de Caja</p>
                <p
                    class="text-xl font-titulo font-bold mt-1"
                    :class="resumen.flujo_neto >= 0 ? 'text-foreground' : 'text-estado-rojo-texto'"
                >
                    C$ {{ resumen.flujo_neto.toFixed(2) }}
                </p>
            </article>
            <article class="rounded-lg bg-surface border border-border p-4">
                <p class="text-xs uppercase font-semibold text-muted-foreground">Cuentas por Cobrar</p>
                <p class="text-xl font-titulo font-bold text-frambuesa mt-1">
                    C$ {{ Number(props.cuentas_por_cobrar ?? 0).toFixed(2) }}
                </p>
            </article>
            <article class="rounded-lg bg-surface border border-border p-4">
                <p class="text-xs uppercase font-semibold text-muted-foreground">Cuentas por Pagar</p>
                <p class="text-xl font-titulo font-bold text-fresa mt-1">C$ {{ resumen.cuentas_por_pagar.toFixed(2) }}</p>
            </article>
        </section>

        <!-- Selector de vistas contables -->
        <div class="flex flex-wrap gap-2 border-b border-border pb-3">
            <button
                type="button"
                @click="tabActiva = 'flujo'"
                class="px-4 py-2 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
                :class="
                    tabActiva === 'flujo'
                        ? 'bg-fresa text-white'
                        : 'bg-surface border border-border text-foreground hover:bg-muted/50'
                "
            >
                Flujo de Caja y Cuentas Pendientes
            </button>
            <button
                type="button"
                @click="tabActiva = 'diario'"
                class="px-4 py-2 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
                :class="
                    tabActiva === 'diario'
                        ? 'bg-fresa text-white'
                        : 'bg-surface border border-border text-foreground hover:bg-muted/50'
                "
            >
                Libro Diario (Partida Doble)
            </button>
            <button
                type="button"
                @click="tabActiva = 'estados'"
                class="px-4 py-2 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
                :class="
                    tabActiva === 'estados'
                        ? 'bg-fresa text-white'
                        : 'bg-surface border border-border text-foreground hover:bg-muted/50'
                "
            >
                Estado de Resultados y Balance General
            </button>
        </div>

        <!-- TAB 1: Flujo de Caja y Cuentas -->
        <div v-if="tabActiva === 'flujo'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Registro de gasto o cuenta por pagar -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Registrar Egreso Operativo</h2>
                <p class="text-xs text-muted-foreground mb-4">
                    Asienta gastos de energía, mantenimiento, logística o compromisos y genera su póliza contable
                </p>

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
                            <label class="block text-xs font-semibold mb-1">Monto (C$)</label>
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
                    <p class="text-xs text-muted-foreground">
                        Ingresos por ventas, cuentas por cobrar a clientes mayoristas y cuentas por pagar a proveedores
                    </p>
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
                                    :class="
                                        m.tipo === 'Ingreso' || m.tipo === 'Cuenta por Cobrar'
                                            ? 'text-estado-verde-texto'
                                            : 'text-foreground'
                                    "
                                >
                                    C$ {{ Number(m.monto).toFixed(2) }}
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
                                        {{ m.tipo === 'Cuenta por Cobrar' ? 'Cobrar' : 'Liquidar' }}
                                    </button>
                                    <span v-else class="text-xs text-muted-foreground">Cerrado</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- TAB 2: Libro Diario (Partida Doble) -->
        <section v-else-if="tabActiva === 'diario'" class="space-y-4">
            <div class="rounded-lg bg-surface border border-border p-5">
                <h2 class="text-base font-titulo font-semibold text-foreground">Libro Diario General (Partida Doble)</h2>
                <p class="text-xs text-muted-foreground">
                    Asientos contables generados automáticamente por Compras, Producción, Calidad, Inventario, Ventas y Finanzas (Debe = Haber)
                </p>
            </div>

            <div
                v-for="asiento in props.contabilidad?.asientos ?? []"
                :key="asiento.id"
                class="rounded-lg bg-surface border border-border overflow-hidden"
            >
                <div class="px-5 py-3 bg-muted/40 border-b border-border flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="font-mono text-xs font-bold text-frambuesa">{{ asiento.numero_asiento }}</span>
                        <span class="text-xs font-semibold text-foreground">{{ asiento.concepto }}</span>
                        <span class="px-2 py-0.5 rounded bg-background border border-border text-[11px] font-semibold">
                            {{ asiento.modulo_origen }} | Ref: {{ asiento.referencia }}
                        </span>
                    </div>
                    <span class="text-xs text-muted-foreground">
                        {{ asiento.fecha }} por {{ asiento.responsable }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-border text-muted-foreground uppercase">
                                <th class="py-2 px-4 font-semibold w-24">Código</th>
                                <th class="py-2 px-4 font-semibold">Cuenta Contable</th>
                                <th class="py-2 px-4 font-semibold">Detalle de Partida</th>
                                <th class="py-2 px-4 font-semibold text-right w-36">Debe (C$)</th>
                                <th class="py-2 px-4 font-semibold text-right w-36">Haber (C$)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="(p, idx) in asiento.partidas" :key="idx">
                                <td class="py-2 px-4 font-mono font-semibold">{{ p.codigo }}</td>
                                <td class="py-2 px-4 font-medium text-foreground">{{ p.cuenta }}</td>
                                <td class="py-2 px-4 text-muted-foreground">{{ p.descripcion }}</td>
                                <td class="py-2 px-4 text-right font-semibold">
                                    {{ Number(p.debe) > 0 ? `C$ ${Number(p.debe).toFixed(2)}` : '—' }}
                                </td>
                                <td class="py-2 px-4 text-right font-semibold">
                                    {{ Number(p.haber) > 0 ? `C$ ${Number(p.haber).toFixed(2)}` : '—' }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-muted/30 border-t border-border font-bold">
                                <td colspan="3" class="py-2 px-4 text-right uppercase text-[11px] text-muted-foreground">
                                    Sumas Iguales de Póliza
                                </td>
                                <td class="py-2 px-4 text-right text-foreground">C$ {{ Number(asiento.total_debe).toFixed(2) }}</td>
                                <td class="py-2 px-4 text-right text-foreground">C$ {{ Number(asiento.total_haber).toFixed(2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>

        <!-- TAB 3: Estados Financieros y Balanza -->
        <div v-else class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Estado de Resultados -->
                <section class="rounded-lg bg-surface border border-border p-5 space-y-3">
                    <div>
                        <h2 class="text-base font-titulo font-semibold text-foreground">Estado de Resultados</h2>
                        <p class="text-xs text-muted-foreground">Rentabilidad acumulada del ejercicio en Córdobas (C$)</p>
                    </div>

                    <div class="space-y-2 text-sm pt-2">
                        <div class="flex justify-between py-1.5 border-b border-border">
                            <span>(+) Ingresos por Ventas de Helados (4101)</span>
                            <span class="font-semibold">
                                C$ {{ Number(props.contabilidad?.estado_resultados.ingresos_ventas ?? 0).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-border text-muted-foreground">
                            <span>(-) Costo de Ventas de Producción (5101)</span>
                            <span class="font-semibold">
                                C$ {{ Number(props.contabilidad?.estado_resultados.costo_ventas ?? 0).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-border font-semibold bg-muted/30 px-2 rounded">
                            <span>(=) Utilidad Bruta Industrial</span>
                            <span>C$ {{ Number(props.contabilidad?.estado_resultados.utilidad_bruta ?? 0).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-border text-muted-foreground">
                            <span>(-) Gastos Operativos de Planta (5201)</span>
                            <span class="font-semibold">
                                C$ {{ Number(props.contabilidad?.estado_resultados.gastos_operativos ?? 0).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-border text-muted-foreground">
                            <span>(-) Pérdidas por Mermas y Scrap de Calidad (5202)</span>
                            <span class="font-semibold">
                                C$ {{ Number(props.contabilidad?.estado_resultados.mermas_scrap ?? 0).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-2.5 px-3 rounded-lg bg-secondary/60 border border-border font-titulo font-bold text-base">
                            <span>(=) Utilidad Neta del Ejercicio</span>
                            <span class="text-frambuesa">
                                C$ {{ Number(props.contabilidad?.estado_resultados.utilidad_neta ?? 0).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Balance General -->
                <section class="rounded-lg bg-surface border border-border p-5 space-y-3">
                    <div>
                        <h2 class="text-base font-titulo font-semibold text-foreground">Balance General</h2>
                        <p class="text-xs text-muted-foreground">Situación patrimonial y ecuación contable (Activo = Pasivo + Patrimonio)</p>
                    </div>

                    <div class="space-y-2 text-sm pt-2">
                        <div class="flex justify-between py-2 border-b border-border font-semibold">
                            <span>Total Activos Circulantes e Inventarios (1101–1105)</span>
                            <span class="text-estado-verde-texto">
                                C$ {{ Number(props.contabilidad?.balance_general.total_activos ?? 0).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-border text-muted-foreground">
                            <span>Total Pasivos a Corto Plazo (2101–2102)</span>
                            <span class="font-semibold">
                                C$ {{ Number(props.contabilidad?.balance_general.total_pasivos ?? 0).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-border text-muted-foreground">
                            <span>Capital Social y Patrimonio Inicial (3101)</span>
                            <span class="font-semibold">
                                C$ {{ Number(props.contabilidad?.balance_general.capital_patrimonio ?? 0).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-border text-muted-foreground">
                            <span>Resultado Acumulado del Ejercicio</span>
                            <span class="font-semibold">
                                C$ {{ Number(props.contabilidad?.balance_general.utilidad_ejercicio ?? 0).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-2.5 px-3 rounded-lg bg-secondary/60 border border-border font-titulo font-bold text-base">
                            <span>Total Pasivo + Patrimonio</span>
                            <span class="text-foreground">
                                C$ {{ Number(props.contabilidad?.balance_general.total_pasivo_mas_patrimonio ?? 0).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Balanza de Comprobación -->
            <section class="rounded-lg bg-surface border border-border overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h2 class="text-base font-titulo font-semibold text-foreground">Balanza de Comprobación por Cuenta Contable</h2>
                    <p class="text-xs text-muted-foreground">Catálogo maestro de cuentas con movimientos acumulados y saldo actual</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">Código</th>
                                <th class="py-3 px-4 font-semibold">Nombre de la Cuenta</th>
                                <th class="py-3 px-4 font-semibold">Tipo</th>
                                <th class="py-3 px-4 font-semibold">Naturaleza</th>
                                <th class="py-3 px-4 font-semibold text-right">Total Debe</th>
                                <th class="py-3 px-4 font-semibold text-right">Total Haber</th>
                                <th class="py-3 px-4 font-semibold text-right">Saldo Actual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="c in props.contabilidad?.balanza ?? []" :key="c.codigo" class="hover:bg-muted/25">
                                <td class="py-2.5 px-4 font-mono text-xs font-bold text-frambuesa">{{ c.codigo }}</td>
                                <td class="py-2.5 px-4 font-medium text-foreground">{{ c.nombre }}</td>
                                <td class="py-2.5 px-4 text-xs">{{ c.tipo }}</td>
                                <td class="py-2.5 px-4 text-xs text-muted-foreground">{{ c.naturaleza }}</td>
                                <td class="py-2.5 px-4 text-right">C$ {{ Number(c.debe).toFixed(2) }}</td>
                                <td class="py-2.5 px-4 text-right">C$ {{ Number(c.haber).toFixed(2) }}</td>
                                <td class="py-2.5 px-4 text-right font-semibold">C$ {{ Number(c.saldo).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </ErpLayout>
</template>
