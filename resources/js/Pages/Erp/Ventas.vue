<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ErpLayout from '@/Layouts/ErpLayout.vue';

const props = defineProps<{
    turnoActivo: {
        id: number;
        codigo_turno: string;
        cajero_nombre: string;
        fecha_apertura: string;
        monto_inicial: string;
        ventas_efectivo: string;
        ventas_tarjeta: string;
        ventas_transferencia: string;
        monto_esperado: string;
        estado: string;
    } | null;
    ventas: Array<{
        id: number;
        folio: string;
        cliente_nombre: string;
        vendedor_nombre: string;
        fecha_venta: string;
        metodo_pago: string;
        total: string;
        estado: string;
    }>;
    productos: Array<{
        id: number;
        sku: string;
        nombre: string;
        presentacion: string;
        precio_venta: string;
        stock_actual: string;
    }>;
    clientes: Array<{ id: number; nombre: string; tipo: string }>;
    historialTurnos: Array<{
        id: number;
        codigo_turno: string;
        cajero_nombre: string;
        fecha_apertura: string;
        fecha_cierre: string | null;
        monto_esperado: string;
        monto_declarado: string | null;
        diferencia: string;
        estado: string;
    }>;
}>();

const formVenta = useForm({
    producto_id: props.productos[0]?.id ?? 1,
    cliente_id: props.clientes[0]?.id ?? 1,
    cantidad: '1',
    metodo_pago: 'Efectivo',
});

const formAbrirCaja = useForm({
    accion: 'abrir',
    monto_inicial: '150.00',
    observaciones: 'Apertura de vitrina principal',
});

const formCerrarCaja = useForm({
    accion: 'cerrar',
    monto_declarado: props.turnoActivo?.monto_esperado ?? '150.00',
    observaciones: 'Cierre y arqueo conforme',
});

const productoSeleccionado = computed(() =>
    props.productos.find((p) => p.id === Number(formVenta.producto_id))
);

const totalVenta = computed(() => {
    const precio = Number(productoSeleccionado.value?.precio_venta ?? 0);
    return (precio * Number(formVenta.cantidad || 0)).toFixed(2);
});

function cobrarVenta() {
    formVenta.post('/ventas', {
        onSuccess: () => {
            formVenta.cantidad = '1';
        },
    });
}

function abrirTurno() {
    formAbrirCaja.post('/ventas/caja');
}

function cerrarTurno() {
    formCerrarCaja.post('/ventas/caja');
}
</script>

<template>
    <Head title="Ventas y Turno de Caja" />

    <ErpLayout
        title="Punto de Venta y Control de Caja"
        subtitle="Facturación de mostrador y mayorista, afectación de stock en tiempo real y corte de caja"
    >
        <!-- Estado actual del turno de caja -->
        <section class="rounded-lg bg-surface border border-border p-5">
            <div v-if="turnoActivo" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                <div class="lg:col-span-8 space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-2.5 py-1 rounded text-xs font-semibold bg-estado-verde text-estado-verde-texto">
                            Turno Activo: {{ turnoActivo.codigo_turno }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            Cajero responsable: <strong class="text-foreground">{{ turnoActivo.cajero_nombre }}</strong>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 rounded-lg bg-background border border-border">
                            <p class="text-[11px] uppercase text-muted-foreground font-semibold">Fondo Inicial</p>
                            <p class="text-lg font-titulo font-bold mt-0.5">${{ Number(turnoActivo.monto_inicial).toFixed(2) }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-background border border-border">
                            <p class="text-[11px] uppercase text-muted-foreground font-semibold">Ventas Efectivo</p>
                            <p class="text-lg font-titulo font-bold mt-0.5">${{ Number(turnoActivo.ventas_efectivo).toFixed(2) }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-background border border-border">
                            <p class="text-[11px] uppercase text-muted-foreground font-semibold">Tarjeta / Transf.</p>
                            <p class="text-lg font-titulo font-bold mt-0.5">
                                ${{ (Number(turnoActivo.ventas_tarjeta) + Number(turnoActivo.ventas_transferencia)).toFixed(2) }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-secondary/50 border border-border">
                            <p class="text-[11px] uppercase text-secondary-foreground font-semibold">Efectivo Esperado</p>
                            <p class="text-lg font-titulo font-bold text-frambuesa mt-0.5">
                                ${{ Number(turnoActivo.monto_esperado).toFixed(2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Corte de caja -->
                <form @submit.prevent="cerrarTurno" class="lg:col-span-4 p-4 rounded-lg bg-background border border-border space-y-3">
                    <h3 class="text-sm font-titulo font-semibold text-foreground">Ejecutar Corte y Arqueo de Caja</h3>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Efectivo Contado en Gaveta ($)</label>
                        <input
                            v-model="formCerrarCaja.monto_declarado"
                            type="number"
                            step="0.01"
                            required
                            class="w-full h-9 px-3 rounded-lg border border-input bg-surface text-sm"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="formCerrarCaja.processing"
                        class="w-full h-9 rounded-lg border border-frambuesa text-frambuesa hover:bg-frambuesa hover:text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Cerrar Turno de Caja
                    </button>
                </form>
            </div>

            <!-- Apertura de turno cuando no hay caja abierta -->
            <form v-else @submit.prevent="abrirTurno" class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-base font-titulo font-semibold text-foreground">La caja se encuentra cerrada</h2>
                    <p class="text-xs text-muted-foreground">Abre un nuevo turno declarando el fondo inicial para habilitar las ventas</p>
                </div>
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Fondo Inicial ($)</label>
                        <input
                            v-model="formAbrirCaja.monto_inicial"
                            type="number"
                            step="0.01"
                            required
                            class="w-40 h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="formAbrirCaja.processing"
                        class="h-10 px-5 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Abrir Turno de Caja
                    </button>
                </div>
            </form>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Terminal de Punto de Venta -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Registrar Venta</h2>
                <p class="text-xs text-muted-foreground mb-4">Descuenta producto terminado e impacta la caja activa</p>

                <form @submit.prevent="cobrarVenta" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Producto Terminado</label>
                        <select
                            v-model="formVenta.producto_id"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option v-for="p in productos" :key="p.id" :value="p.id">
                                {{ p.nombre }} (${{ Number(p.precio_venta).toFixed(2) }} | Stock: {{ Number(p.stock_actual).toFixed(1) }})
                            </option>
                        </select>
                        <p v-if="formVenta.errors.producto_id" class="text-xs text-estado-rojo-texto mt-1">{{ formVenta.errors.producto_id }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Cliente</label>
                        <select
                            v-model="formVenta.cliente_id"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option v-for="c in clientes" :key="c.id" :value="c.id">
                                {{ c.nombre }} ({{ c.tipo }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Cantidad (uds)</label>
                            <input
                                v-model="formVenta.cantidad"
                                type="number"
                                step="1"
                                min="1"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Método de Pago</label>
                            <select
                                v-model="formVenta.metodo_pago"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            >
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta POS</option>
                                <option value="Transferencia">Transferencia</option>
                            </select>
                        </div>
                    </div>
                    <p v-if="formVenta.errors.cantidad" class="text-xs text-estado-rojo-texto">{{ formVenta.errors.cantidad }}</p>

                    <div class="p-3.5 rounded-lg bg-muted/60 border border-border flex items-center justify-between">
                        <span class="text-xs font-semibold text-muted-foreground">Total a Cobrar</span>
                        <span class="text-xl font-titulo font-bold text-fresa">${{ totalVenta }}</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="formVenta.processing || !turnoActivo"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa disabled:opacity-50 text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Confirmar Cobro y Emitir Comprobante
                    </button>
                </form>
            </section>

            <!-- Historial de ventas y cortes -->
            <section class="lg:col-span-8 space-y-6">
                <div class="rounded-lg bg-surface border border-border overflow-hidden">
                    <div class="px-5 py-4 border-b border-border">
                        <h2 class="text-base font-titulo font-semibold text-foreground">Ventas Registradas</h2>
                        <p class="text-xs text-muted-foreground">Operaciones de mostrador y pedidos mayoristas</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                    <th class="py-3 px-4 font-semibold">Folio</th>
                                    <th class="py-3 px-4 font-semibold">Fecha</th>
                                    <th class="py-3 px-4 font-semibold">Cliente</th>
                                    <th class="py-3 px-4 font-semibold">Método</th>
                                    <th class="py-3 px-4 font-semibold text-right">Total</th>
                                    <th class="py-3 px-4 font-semibold">Cajero</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="v in ventas" :key="v.id" class="hover:bg-muted/25">
                                    <td class="py-3 px-4 font-mono text-xs font-semibold text-frambuesa">{{ v.folio }}</td>
                                    <td class="py-3 px-4 text-xs">{{ String(v.fecha_venta).slice(0, 16) }}</td>
                                    <td class="py-3 px-4 font-medium text-foreground">{{ v.cliente_nombre }}</td>
                                    <td class="py-3 px-4 text-xs font-semibold">{{ v.metodo_pago }}</td>
                                    <td class="py-3 px-4 text-right font-semibold">${{ Number(v.total).toFixed(2) }}</td>
                                    <td class="py-3 px-4 text-xs text-muted-foreground">{{ v.vendedor_nombre }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historial de turnos y cortes -->
                <div class="rounded-lg bg-surface border border-border overflow-hidden">
                    <div class="px-5 py-4 border-b border-border">
                        <h2 class="text-base font-titulo font-semibold text-foreground">Historial de Cortes de Caja</h2>
                        <p class="text-xs text-muted-foreground">Auditoría de aperturas, cierres y diferencias de efectivo</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                    <th class="py-2.5 px-4 font-semibold">Turno</th>
                                    <th class="py-2.5 px-4 font-semibold">Cajero</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Esperado</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Declarado</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Diferencia</th>
                                    <th class="py-2.5 px-4 font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="t in historialTurnos" :key="t.id">
                                    <td class="py-2.5 px-4 font-mono text-xs font-semibold">{{ t.codigo_turno }}</td>
                                    <td class="py-2.5 px-4 text-xs">{{ t.cajero_nombre }}</td>
                                    <td class="py-2.5 px-4 text-right">${{ Number(t.monto_esperado).toFixed(2) }}</td>
                                    <td class="py-2.5 px-4 text-right">
                                        {{ t.monto_declarado !== null ? `$${Number(t.monto_declarado).toFixed(2)}` : '—' }}
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-semibold">${{ Number(t.diferencia).toFixed(2) }}</td>
                                    <td class="py-2.5 px-4">
                                        <span
                                            class="px-2 py-0.5 rounded text-xs font-semibold"
                                            :class="
                                                t.estado === 'Abierta'
                                                    ? 'bg-estado-verde text-estado-verde-texto'
                                                    : 'bg-estado-gris text-estado-gris-texto'
                                            "
                                        >
                                            {{ t.estado }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </ErpLayout>
</template>
