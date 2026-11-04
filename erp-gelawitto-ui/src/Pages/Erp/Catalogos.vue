<script setup lang="ts">
import { ref } from 'vue';
import { useFormulario } from '@/composables/useFormulario';
import ErpLayout from '@/Layouts/ErpLayout.vue';

const props = defineProps<{
    productos: Array<{
        id: number;
        sku: string;
        nombre: string;
        tipo?: string;
        sabor: string;
        presentacion: string;
        precio_venta: string;
        costo_unitario: string;
        stock_actual: string;
        stock_minimo: string;
    }>;
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
    proveedores: Array<{
        id: number;
        nombre: string;
        ruc_nit: string;
        contacto: string;
        telefono: string;
        correo: string;
    }>;
    clientes: Array<{
        id: number;
        nombre: string;
        identificacion: string;
        telefono: string;
        correo: string;
        tipo: string;
    }>;
    unidades: Array<{
        id: number;
        nombre: string;
        abreviatura: string;
    }>;
}>();

const activeTab = ref<'productos' | 'insumos' | 'proveedores' | 'clientes'>('productos');

const formProducto = useFormulario({
    sku: '',
    nombre: '',
    tipo: 'terminado',
    sabor: 'Arroz Tradicional',
    presentacion: 'Vaso 100 mL',
    precio_venta: '100.00',
    costo_unitario: '20.77',
    stock_minimo: '10',
});

const formInsumo = useFormulario({
    codigo: '',
    nombre: '',
    categoria: 'Cereales y Harinas',
    unidad_id: props.unidades[0]?.id ?? 1,
    stock_minimo: '10',
    costo_promedio: '45.00',
});

const formProveedor = useFormulario({
    nombre: '',
    ruc_nit: '',
    contacto: '',
    telefono: '+505 ',
    correo: '',
    direccion: '',
});

const formCliente = useFormulario({
    nombre: '',
    identificacion: '',
    telefono: '+505 ',
    correo: '',
    tipo: 'Mayorista',
});

function guardarProducto() {
    formProducto.post('/catalogos/productos', {
        onSuccess: () => formProducto.reset('sku', 'nombre'),
    });
}

function guardarInsumo() {
    formInsumo.post('/catalogos/insumos', {
        onSuccess: () => formInsumo.reset('codigo', 'nombre'),
    });
}

function guardarProveedor() {
    formProveedor.post('/catalogos/proveedores', {
        onSuccess: () => formProveedor.reset('nombre', 'ruc_nit', 'contacto', 'correo', 'direccion'),
    });
}

function guardarCliente() {
    formCliente.post('/catalogos/clientes', {
        onSuccess: () => formCliente.reset('nombre', 'identificacion', 'correo'),
    });
}
</script>

<template>
    <ErpLayout
        title="Catálogos Maestros"
        subtitle="Administración centralizada de productos terminados, materia prima, proveedores y cartera de clientes en Córdobas (C$)"
    >
        <!-- Navegación por pestañas -->
        <div class="flex flex-wrap gap-2 border-b border-border pb-3">
            <button
                type="button"
                @click="activeTab = 'productos'"
                :class="[
                    'px-4 py-2 rounded-lg text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'productos'
                        ? 'bg-fresa text-white'
                        : 'bg-surface border border-border text-foreground hover:bg-muted',
                ]"
            >
                Productos Terminados ({{ productos.length }})
            </button>
            <button
                type="button"
                @click="activeTab = 'insumos'"
                :class="[
                    'px-4 py-2 rounded-lg text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'insumos'
                        ? 'bg-fresa text-white'
                        : 'bg-surface border border-border text-foreground hover:bg-muted',
                ]"
            >
                Insumos y Materia Prima ({{ insumos.length }})
            </button>
            <button
                type="button"
                @click="activeTab = 'proveedores'"
                :class="[
                    'px-4 py-2 rounded-lg text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'proveedores'
                        ? 'bg-fresa text-white'
                        : 'bg-surface border border-border text-foreground hover:bg-muted',
                ]"
            >
                Proveedores ({{ proveedores.length }})
            </button>
            <button
                type="button"
                @click="activeTab = 'clientes'"
                :class="[
                    'px-4 py-2 rounded-lg text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'clientes'
                        ? 'bg-fresa text-white'
                        : 'bg-surface border border-border text-foreground hover:bg-muted',
                ]"
            >
                Clientes ({{ clientes.length }})
            </button>
        </div>

        <!-- Pestaña Productos Terminados -->
        <div v-if="activeTab === 'productos'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Registrar Nuevo Producto o Subensamble</h2>
                <p class="text-xs text-muted-foreground mb-4">Agrega un producto terminado o subensamble intermedio al catálogo maestro</p>

                <form @submit.prevent="guardarProducto" class="space-y-3.5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Código SKU</label>
                            <input
                                v-model="formProducto.sku"
                                type="text"
                                placeholder="GLW-MAR-100"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                            <p v-if="formProducto.errors.sku" class="text-xs text-estado-rojo-texto mt-1">{{ formProducto.errors.sku }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Nivel / Tipo</label>
                            <select
                                v-model="formProducto.tipo"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            >
                                <option value="terminado">Terminado (Nivel 0)</option>
                                <option value="semielaborado">Subensamble (Nivel 1)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Nombre Comercial</label>
                        <input
                            v-model="formProducto.nombre"
                            type="text"
                            placeholder="Gelawitto de Maracuyá y Arroz"
                            required
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Línea de Sabor</label>
                            <input
                                v-model="formProducto.sabor"
                                type="text"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Presentación</label>
                            <input
                                v-model="formProducto.presentacion"
                                type="text"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Precio (C$)</label>
                            <input
                                v-model="formProducto.precio_venta"
                                type="number"
                                step="0.01"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Costo (C$)</label>
                            <input
                                v-model="formProducto.costo_unitario"
                                type="number"
                                step="0.01"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Mínimo</label>
                            <input
                                v-model="formProducto.stock_minimo"
                                type="number"
                                step="1"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>
                    <button
                        type="submit"
                        :disabled="formProducto.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Guardar Producto en Catálogo
                    </button>
                </form>
            </section>

            <section class="lg:col-span-8 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">SKU</th>
                                <th class="py-3 px-4 font-semibold">Producto</th>
                                <th class="py-3 px-4 font-semibold">Sabor</th>
                                <th class="py-3 px-4 font-semibold text-right">Costo</th>
                                <th class="py-3 px-4 font-semibold text-right">Precio Venta</th>
                                <th class="py-3 px-4 font-semibold text-right">Existencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="p in productos" :key="p.id" class="hover:bg-muted/25">
                                <td class="py-3 px-4 font-mono text-xs text-frambuesa font-semibold">{{ p.sku }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-foreground">{{ p.nombre }}</p>
                                        <span
                                            class="px-2 py-0.5 rounded text-[11px] font-semibold"
                                            :class="p.tipo === 'semielaborado' ? 'bg-amber-100 text-amber-800' : 'bg-estado-verde text-estado-verde-texto'"
                                        >
                                            {{ p.tipo === 'semielaborado' ? 'Subensamble' : 'Terminado' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">{{ p.presentacion }}</p>
                                </td>
                                <td class="py-3 px-4 text-xs font-medium">{{ p.sabor }}</td>
                                <td class="py-3 px-4 text-right">C$ {{ Number(p.costo_unitario).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold">C$ {{ Number(p.precio_venta).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold">{{ Number(p.stock_actual).toFixed(1) }} uds</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Pestaña Insumos -->
        <div v-if="activeTab === 'insumos'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Registrar Materia Prima</h2>
                <p class="text-xs text-muted-foreground mb-4">Define un nuevo insumo para compras y formulación</p>

                <form @submit.prevent="guardarInsumo" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Código de Insumo</label>
                        <input
                            v-model="formInsumo.codigo"
                            type="text"
                            placeholder="INS-013"
                            required
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                        <p v-if="formInsumo.errors.codigo" class="text-xs text-estado-rojo-texto mt-1">{{ formInsumo.errors.codigo }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Descripción del Insumo</label>
                        <input
                            v-model="formInsumo.nombre"
                            type="text"
                            placeholder="Pistacho tostado sin sal"
                            required
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Categoría</label>
                            <select
                                v-model="formInsumo.categoria"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            >
                                <option value="Cereales y Harinas">Cereales y Harinas</option>
                                <option value="Hidrocoloides">Hidrocoloides</option>
                                <option value="Aguas y Líquidos">Aguas y Líquidos</option>
                                <option value="Endulzantes">Endulzantes</option>
                                <option value="Frutas">Frutas</option>
                                <option value="Lácteos">Lácteos</option>
                                <option value="Aromas y Esencias">Aromas y Esencias</option>
                                <option value="Empaques">Empaques</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Unidad de Medida</label>
                            <select
                                v-model="formInsumo.unidad_id"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            >
                                <option v-for="u in unidades" :key="u.id" :value="u.id">
                                    {{ u.nombre }} ({{ u.abreviatura }})
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Stock Mínimo</label>
                            <input
                                v-model="formInsumo.stock_minimo"
                                type="number"
                                step="0.1"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Costo Base (C$)</label>
                            <input
                                v-model="formInsumo.costo_promedio"
                                type="number"
                                step="0.01"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>
                    <button
                        type="submit"
                        :disabled="formInsumo.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Guardar Insumo
                    </button>
                </form>
            </section>

            <section class="lg:col-span-8 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">Código</th>
                                <th class="py-3 px-4 font-semibold">Insumo</th>
                                <th class="py-3 px-4 font-semibold">Categoría</th>
                                <th class="py-3 px-4 font-semibold text-right">Costo Promedio</th>
                                <th class="py-3 px-4 font-semibold text-right">Stock Actual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="ins in insumos" :key="ins.id" class="hover:bg-muted/25">
                                <td class="py-3 px-4 font-mono text-xs font-semibold text-frambuesa">{{ ins.codigo }}</td>
                                <td class="py-3 px-4 font-semibold text-foreground">{{ ins.nombre }}</td>
                                <td class="py-3 px-4 text-xs text-muted-foreground">{{ ins.categoria }}</td>
                                <td class="py-3 px-4 text-right">C$ {{ Number(ins.costo_promedio).toFixed(2) }} / {{ ins.unidad }}</td>
                                <td class="py-3 px-4 text-right font-semibold">{{ Number(ins.stock_actual).toFixed(2) }} {{ ins.unidad }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Pestaña Proveedores -->
        <div v-if="activeTab === 'proveedores'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Registrar Proveedor</h2>
                <p class="text-xs text-muted-foreground mb-4">Alta de proveedor autorizado para órdenes de compra</p>

                <form @submit.prevent="guardarProveedor" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Razón Social</label>
                        <input
                            v-model="formProveedor.nombre"
                            type="text"
                            placeholder="Distribuidora Láctea Nicaragüense S.A."
                            required
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                        <p v-if="formProveedor.errors.nombre" class="text-xs text-estado-rojo-texto mt-1">{{ formProveedor.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Número RUC / NIT</label>
                        <input
                            v-model="formProveedor.ruc_nit"
                            type="text"
                            placeholder="J031000099881"
                            required
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                        <p v-if="formProveedor.errors.ruc_nit" class="text-xs text-estado-rojo-texto mt-1">{{ formProveedor.errors.ruc_nit }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Ejecutivo de Contacto</label>
                            <input
                                v-model="formProveedor.contacto"
                                type="text"
                                placeholder="Ing. Luis Pérez"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Teléfono</label>
                            <input
                                v-model="formProveedor.telefono"
                                type="text"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Correo Electrónico</label>
                        <input
                            v-model="formProveedor.correo"
                            type="email"
                            placeholder="ventas@proveedor.com.ni"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Dirección Fiscal</label>
                        <input
                            v-model="formProveedor.direccion"
                            type="text"
                            placeholder="Managua, Nicaragua"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="formProveedor.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Guardar Proveedor
                    </button>
                </form>
            </section>

            <section class="lg:col-span-8 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">Razón Social</th>
                                <th class="py-3 px-4 font-semibold">RUC / NIT</th>
                                <th class="py-3 px-4 font-semibold">Contacto</th>
                                <th class="py-3 px-4 font-semibold">Teléfono</th>
                                <th class="py-3 px-4 font-semibold">Correo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="pr in proveedores" :key="pr.id" class="hover:bg-muted/25">
                                <td class="py-3 px-4 font-semibold text-foreground">{{ pr.nombre }}</td>
                                <td class="py-3 px-4 font-mono text-xs">{{ pr.ruc_nit }}</td>
                                <td class="py-3 px-4">{{ pr.contacto }}</td>
                                <td class="py-3 px-4 text-xs">{{ pr.telefono }}</td>
                                <td class="py-3 px-4 text-xs text-muted-foreground">{{ pr.correo }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Pestaña Clientes -->
        <div v-if="activeTab === 'clientes'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Registrar Cliente</h2>
                <p class="text-xs text-muted-foreground mb-4">Agrega clientes mayoristas o de mostrador para facturación y crédito</p>

                <form @submit.prevent="guardarCliente" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Nombre o Razón Social</label>
                        <input
                            v-model="formCliente.nombre"
                            type="text"
                            placeholder="Hotel y Restaurante Los Robles"
                            required
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                        <p v-if="formCliente.errors.nombre" class="text-xs text-estado-rojo-texto mt-1">{{ formCliente.errors.nombre }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">RUC / Cédula</label>
                            <input
                                v-model="formCliente.identificacion"
                                type="text"
                                placeholder="J031000055112"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Segmento</label>
                            <select
                                v-model="formCliente.tipo"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            >
                                <option value="Mayorista">Mayorista</option>
                                <option value="Mostrador">Mostrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Teléfono</label>
                            <input
                                v-model="formCliente.telefono"
                                type="text"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Correo Electrónico</label>
                            <input
                                v-model="formCliente.correo"
                                type="email"
                                placeholder="compras@cliente.com.ni"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            />
                        </div>
                    </div>
                    <button
                        type="submit"
                        :disabled="formCliente.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Guardar Cliente
                    </button>
                </form>
            </section>

            <section class="lg:col-span-8 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">Nombre / Razón Social</th>
                                <th class="py-3 px-4 font-semibold">Identificación</th>
                                <th class="py-3 px-4 font-semibold">Segmento</th>
                                <th class="py-3 px-4 font-semibold">Teléfono</th>
                                <th class="py-3 px-4 font-semibold">Correo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="cl in clientes" :key="cl.id" class="hover:bg-muted/25">
                                <td class="py-3 px-4 font-semibold text-foreground">{{ cl.nombre }}</td>
                                <td class="py-3 px-4 font-mono text-xs">{{ cl.identificacion }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-secondary text-secondary-foreground">
                                        {{ cl.tipo }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">{{ cl.telefono }}</td>
                                <td class="py-3 px-4 text-xs text-muted-foreground">{{ cl.correo }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </ErpLayout>
</template>
