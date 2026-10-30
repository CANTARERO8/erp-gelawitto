<script setup lang="ts">
import { computed } from 'vue';
import { useFormulario, ejecutarAccionApi } from '@/composables/useFormulario';
import ErpLayout from '@/Layouts/ErpLayout.vue';

const props = defineProps<{
    usuarios: Array<{
        id: number;
        name: string;
        email: string;
        rol: string;
        activo: boolean;
        ultimo_acceso: string | null;
    }>;
    bitacora: Array<{
        id: number;
        modulo: string;
        accion: string;
        descripcion: string;
        ip_address: string;
        usuario_nombre: string | null;
        usuario_rol: string | null;
        created_at: string;
    }>;
    roles?: Array<{
        id: number;
        nombre: string;
        descripcion?: string;
    }>;
}>();

const listaRoles = computed(() =>
    props.roles && props.roles.length > 0
        ? props.roles
        : [
              { id: 1, nombre: 'Administrador' },
              { id: 2, nombre: 'Producción' },
              { id: 3, nombre: 'Cajero' },
              { id: 4, nombre: 'Contador' },
          ]
);

const form = useFormulario({
    name: '',
    email: '',
    password: 'password123',
    rol: props.roles?.[0]?.nombre ?? 'Cajero',
});

function crearUsuario() {
    form.post('/seguridad/usuarios', {
        onSuccess: () => form.reset('name', 'email'),
    });
}

function alternarEstado(id: number) {
    ejecutarAccionApi(`/seguridad/usuarios/${id}/toggle`);
}
</script>

<template>
    <ErpLayout
        title="Seguridad, Roles y Auditoría"
        subtitle="Control de accesos por perfil operativo y bitácora inmutable de acciones del sistema"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Alta de nuevo colaborador -->
            <section class="lg:col-span-4 rounded-lg bg-surface border border-border p-5 h-fit">
                <h2 class="text-base font-titulo font-semibold text-foreground mb-1">Registrar Colaborador</h2>
                <p class="text-xs text-muted-foreground mb-4">Asigna credenciales y rol operativo dentro del ERP</p>

                <form @submit.prevent="crearUsuario" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Nombre Completo</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Ej. Sofía Castellón"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1">Correo Corporativo</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="sofia@gelawitto.com"
                            class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                        />
                        <p v-if="form.errors.email" class="text-xs text-estado-rojo-texto mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Rol Operativo</label>
                            <select
                                v-model="form.rol"
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm"
                            >
                                <option v-for="r in listaRoles" :key="r.id" :value="r.nombre">
                                    {{ r.nombre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Contraseña Inicial</label>
                            <input
                                v-model="form.password"
                                type="text"
                                required
                                class="w-full h-10 px-3 rounded-lg border border-input bg-background text-sm font-mono"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-10 rounded-lg bg-fresa hover:bg-frambuesa text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        Habilitar Usuario
                    </button>
                </form>
            </section>

            <!-- Lista de usuarios y estado -->
            <section class="lg:col-span-8 rounded-lg bg-surface border border-border overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h2 class="text-base font-titulo font-semibold text-foreground">Directorio de Usuarios del Sistema</h2>
                    <p class="text-xs text-muted-foreground">Los usuarios suspendidos son rechazados en el Login</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                                <th class="py-3 px-4 font-semibold">Nombre</th>
                                <th class="py-3 px-4 font-semibold">Correo</th>
                                <th class="py-3 px-4 font-semibold">Rol</th>
                                <th class="py-3 px-4 font-semibold">Estado</th>
                                <th class="py-3 px-4 font-semibold text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="u in usuarios" :key="u.id" class="hover:bg-muted/25">
                                <td class="py-3 px-4 font-semibold text-foreground">{{ u.name }}</td>
                                <td class="py-3 px-4 text-xs text-muted-foreground">{{ u.email }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-secondary text-secondary-foreground">
                                        {{ u.rol }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-2.5 py-0.5 rounded text-xs font-semibold"
                                        :class="
                                            u.activo
                                                ? 'bg-estado-verde text-estado-verde-texto'
                                                : 'bg-estado-rojo text-estado-rojo-texto'
                                        "
                                    >
                                        {{ u.activo ? 'Activo' : 'Suspendido' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button
                                        type="button"
                                        @click="alternarEstado(u.id)"
                                        class="px-3 py-1 rounded border border-border hover:bg-muted text-xs font-semibold cursor-pointer"
                                    >
                                        {{ u.activo ? 'Suspender' : 'Reactivar' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Bitácora de auditoría -->
        <section class="rounded-lg bg-surface border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-titulo font-semibold text-foreground">Bitácora de Auditoría del Sistema</h2>
                <p class="text-xs text-muted-foreground">Trazabilidad cronológica de sesiones y operaciones críticas</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                            <th class="py-3 px-4 font-semibold">Fecha y Hora</th>
                            <th class="py-3 px-4 font-semibold">Módulo</th>
                            <th class="py-3 px-4 font-semibold">Acción</th>
                            <th class="py-3 px-4 font-semibold">Detalle</th>
                            <th class="py-3 px-4 font-semibold">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="b in bitacora" :key="b.id" class="hover:bg-muted/25">
                            <td class="py-3 px-4 text-xs text-muted-foreground">{{ String(b.created_at).slice(0, 19) }}</td>
                            <td class="py-3 px-4 text-xs font-semibold text-frambuesa">{{ b.modulo }}</td>
                            <td class="py-3 px-4 font-semibold text-foreground">{{ b.accion }}</td>
                            <td class="py-3 px-4 text-xs">{{ b.descripcion }}</td>
                            <td class="py-3 px-4 text-xs text-muted-foreground">
                                {{ b.usuario_nombre ?? 'Sistema' }} ({{ b.usuario_rol ?? 'Auto' }})
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </ErpLayout>
</template>
