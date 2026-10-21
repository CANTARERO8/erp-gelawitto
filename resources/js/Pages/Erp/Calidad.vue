<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ErpLayout from '@/Layouts/ErpLayout.vue';

const props = defineProps<{
    trazabilidad: Array<{
        id: number;
        codigo_lote: string;
        producto_nombre: string;
        estado: string;
        estado_calidad: string;
        cantidad_programada: string;
        cantidad_obtenida: string | null;
        insumos: Array<{ nombre: string; cantidad: string }>;
        controles: Array<{
            codigo_control: string;
            fecha: string;
            inspector: string;
            unidades_inspeccionadas: string;
            unidades_no_conformes: string;
            defecto: string;
            disposicion: string;
            costo_perdido: string;
        }>;
    }>;
    pendientes: Array<{
        id: number;
        codigo_lote: string;
        producto_nombre: string;
        cantidad_obtenida: string;
    }>;
    lecturas: Array<{
        fecha: string;
        dispositivo: string;
        origen: string;
        temperatura: string;
        fuera_de_rango: boolean;
    }>;
    rango: { minimo: number; maximo: number; dispositivo: string };
}>();

const form = useForm({
    lote_id: props.pendientes[0]?.id ?? 0,
    unidades_inspeccionadas: props.pendientes[0]?.cantidad_obtenida ?? '',
    unidades_no_conformes: '0',
    defecto: '',
    disposicion: 'Liberación',
    notas: '',
});

const loteSeleccionado = computed(() => props.pendientes.find((p) => p.id === Number(form.lote_id)));
const sinNoConformes = computed(() => Number(form.unidades_no_conformes || 0) === 0);

function seleccionarLote() {
    const lote = loteSeleccionado.value;
    if (lote) {
        form.unidades_inspeccionadas = lote.cantidad_obtenida;
    }
}

function registrarInspeccion() {
    form.post(`/calidad/lotes/${form.lote_id}/inspeccion`, {
        preserveScroll: true,
        onSuccess: () => form.reset('defecto', 'notas', 'unidades_no_conformes'),
    });
}

function claseEstadoCalidad(estado: string): string {
    if (estado === 'Liberado') return 'bg-estado-verde text-estado-verde-texto';
    if (estado === 'Retenido' || estado === 'Retrabajo') return 'bg-amber-100 text-amber-800';
    if (estado === 'Scrap') return 'bg-estado-rojo text-estado-rojo-texto';
    return 'bg-muted text-foreground';
}

function claseDisposicion(disposicion: string): string {
    if (disposicion === 'Liberación') return 'bg-estado-verde text-estado-verde-texto';
    if (disposicion === 'Retrabajo') return 'bg-amber-100 text-amber-800';
    return 'bg-estado-rojo text-estado-rojo-texto';
}
</script>

<template>
    <Head title="Calidad y Trazabilidad" />

    <ErpLayout
        title="Control de Calidad y Trazabilidad"
        subtitle="Inspección de lotes ejecutados, no conformidades con su disposición y seguimiento de insumos por lote"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Inspección de lote -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Inspección de Lote</h2>
                <p class="text-xs text-muted-foreground mb-4">
                    Registra las unidades no conformes y la disposición que se aplica al inventario
                </p>

                <p v-if="pendientes.length === 0" class="text-sm text-muted-foreground">
                    No hay lotes ejecutados pendientes de inspección.
                </p>

                <form v-else @submit.prevent="registrarInspeccion" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Lote a inspeccionar</label>
                        <select
                            v-model="form.lote_id"
                            @change="seleccionarLote"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option v-for="p in pendientes" :key="p.id" :value="p.id">
                                {{ p.codigo_lote }} - {{ p.producto_nombre }} ({{ p.cantidad_obtenida }} uds)
                            </option>
                        </select>
                        <p v-if="form.errors.lote" class="text-xs text-estado-rojo-texto mt-1">{{ form.errors.lote }}</p>
                        <p v-if="form.errors.orden" class="text-xs text-estado-rojo-texto mt-1">{{ form.errors.orden }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Unidades inspeccionadas</label>
                            <input
                                v-model="form.unidades_inspeccionadas"
                                type="number"
                                step="0.5"
                                min="0.5"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                            <p v-if="form.errors.unidades_inspeccionadas" class="text-xs text-estado-rojo-texto mt-1">
                                {{ form.errors.unidades_inspeccionadas }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Unidades no conformes</label>
                            <input
                                v-model="form.unidades_no_conformes"
                                type="number"
                                step="0.5"
                                min="0"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                            <p v-if="form.errors.unidades_no_conformes" class="text-xs text-estado-rojo-texto mt-1">
                                {{ form.errors.unidades_no_conformes }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Descripción de la no conformidad</label>
                        <input
                            v-model="form.defecto"
                            type="text"
                            placeholder="Ej. gelificación incompleta, fuga de tapa, sabor fuera de criterio"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                        <p v-if="form.errors.defecto" class="text-xs text-estado-rojo-texto mt-1">{{ form.errors.defecto }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Disposición</label>
                        <select
                            v-model="form.disposicion"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        >
                            <option value="Liberación">Liberación (sin no conformes)</option>
                            <option value="Retrabajo">Retrabajo (retira unidades del stock vendible)</option>
                            <option value="Scrap">Scrap (da de baja las unidades no conformes)</option>
                        </select>
                        <p v-if="sinNoConformes" class="text-xs text-muted-foreground mt-1">
                            Sin no conformes, la disposición debe ser Liberación.
                        </p>
                        <p v-if="form.errors.disposicion" class="text-xs text-estado-rojo-texto mt-1">{{ form.errors.disposicion }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Notas de inspección</label>
                        <input
                            v-model="form.notas"
                            type="text"
                            placeholder="Criterios revisados, temperatura y textura"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer disabled:opacity-60"
                    >
                        Registrar Inspección y Disposición
                    </button>
                </form>
            </section>

            <!-- Trazabilidad por lote -->
            <section class="lg:col-span-8 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h2 class="text-base font-titulo font-semibold text-foreground">Trazabilidad por Lote</h2>
                    <p class="text-xs text-muted-foreground">
                        Insumos consumidos en cada lote, resultado de producción, inspecciones y disposición aplicada
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">Lote</th>
                                <th class="py-3 px-4 font-semibold">Insumos consumidos</th>
                                <th class="py-3 px-4 font-semibold text-right">Obtenido</th>
                                <th class="py-3 px-4 font-semibold">Calidad</th>
                                <th class="py-3 px-4 font-semibold">Inspecciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="l in trazabilidad" :key="l.id" class="hover:bg-muted/25 align-top">
                                <td class="py-3 px-4">
                                    <p class="font-mono text-xs font-semibold text-frambuesa">{{ l.codigo_lote }}</p>
                                    <p class="text-xs text-muted-foreground">{{ l.producto_nombre }}</p>
                                    <p class="text-xs text-muted-foreground">{{ l.estado }}</p>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <p v-if="l.insumos.length === 0" class="text-muted-foreground">Sin consumos registrados</p>
                                    <p v-for="(i, idx) in l.insumos" :key="idx">
                                        {{ i.nombre }}: <span class="font-semibold">{{ Number(i.cantidad).toFixed(3) }}</span>
                                    </p>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold">
                                    {{ l.cantidad_obtenida !== null ? Number(l.cantidad_obtenida).toFixed(1) : '—' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span :class="['px-2.5 py-0.5 rounded text-xs font-semibold', claseEstadoCalidad(l.estado_calidad)]">
                                        {{ l.estado_calidad }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <p v-if="l.controles.length === 0" class="text-muted-foreground">Sin inspección</p>
                                    <div v-for="c in l.controles" :key="c.codigo_control" class="mb-2 last:mb-0">
                                        <p class="font-mono font-semibold">{{ c.codigo_control }} · {{ c.fecha }}</p>
                                        <p>
                                            {{ c.unidades_no_conformes }} no conformes de {{ c.unidades_inspeccionadas }}
                                            <span :class="['ml-1 px-2 py-0.5 rounded font-semibold', claseDisposicion(c.disposicion)]">
                                                {{ c.disposicion }}
                                            </span>
                                        </p>
                                        <p v-if="c.defecto" class="text-muted-foreground">{{ c.defecto }}</p>
                                        <p v-if="Number(c.costo_perdido) > 0" class="text-estado-rojo-texto">
                                            Costo perdido: ${{ Number(c.costo_perdido).toFixed(2) }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Temperatura del refrigerador (sensor IIoT) -->
        <section class="mt-6 rounded-lg bg-surface border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-titulo font-semibold text-foreground">Temperatura del Refrigerador</h2>
                <p class="text-xs text-muted-foreground">
                    Dispositivo {{ rango.dispositivo }} · rango aceptable de {{ rango.minimo }} a {{ rango.maximo }} °C.
                    Una lectura fuera de rango retiene los lotes que esperan inspección.
                </p>
            </div>

            <p v-if="lecturas.length === 0" class="px-5 py-4 text-sm text-muted-foreground">
                Aún no hay lecturas del refrigerador.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                            <th class="py-3 px-4 font-semibold">Fecha y hora</th>
                            <th class="py-3 px-4 font-semibold">Origen</th>
                            <th class="py-3 px-4 font-semibold text-right">Temperatura</th>
                            <th class="py-3 px-4 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="(r, idx) in lecturas" :key="idx" class="hover:bg-muted/25">
                            <td class="py-2.5 px-4 text-xs">{{ r.fecha }}</td>
                            <td class="py-2.5 px-4 text-xs text-muted-foreground">{{ r.origen }}</td>
                            <td class="py-2.5 px-4 text-right font-semibold">{{ Number(r.temperatura).toFixed(2) }} °C</td>
                            <td class="py-2.5 px-4">
                                <span
                                    v-if="r.fuera_de_rango"
                                    class="px-2.5 py-0.5 rounded text-xs font-semibold bg-estado-rojo text-estado-rojo-texto"
                                >
                                    Fuera de rango
                                </span>
                                <span
                                    v-else
                                    class="px-2.5 py-0.5 rounded text-xs font-semibold bg-estado-verde text-estado-verde-texto"
                                >
                                    En rango
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </ErpLayout>
</template>
