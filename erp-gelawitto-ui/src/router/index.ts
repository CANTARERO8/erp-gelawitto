import { createRouter, createWebHistory, createMemoryHistory, useRouter, type RouteRecordRaw } from 'vue-router';
import { defineComponent, h, onMounted, onUnmounted, ref, type Component } from 'vue';
import apiService from '@/services/apiService';
import { cargarSesion, limpiarSesion, notificarError, useAuth } from '@/composables/useAuth';
import { registrarRecargaVista } from '@/composables/useFormulario';

import Login from '@/Pages/Auth/Login.vue';
import Dashboard from '@/Pages/Erp/Dashboard.vue';
import Catalogos from '@/Pages/Erp/Catalogos.vue';
import Compras from '@/Pages/Erp/Compras.vue';
import Inventario from '@/Pages/Erp/Inventario.vue';
import Produccion from '@/Pages/Erp/Produccion.vue';
import Calidad from '@/Pages/Erp/Calidad.vue';
import Ventas from '@/Pages/Erp/Ventas.vue';
import Finanzas from '@/Pages/Erp/Finanzas.vue';
import Seguridad from '@/Pages/Erp/Seguridad.vue';

export const cargandoVista = ref(false);

function crearVistaErp(endpoint: string, titulo: string, Componente: Component) {
    return defineComponent({
        name: `Vista_${endpoint}`,
        setup() {
            const router = useRouter();
            const datos = ref<Record<string, any> | null>(null);
            let cancelarSuscripcion: (() => void) | null = null;

            async function cargarDatos() {
                cargandoVista.value = true;
                try {
                    const respuesta = await apiService(endpoint).get();
                    datos.value = respuesta ?? {};
                } catch (error: any) {
                    const status = error?.response?.status;
                    if (status === 401) {
                        limpiarSesion();
                        await router.replace('/login');
                    } else if (status === 403) {
                        notificarError(error?.response?.data?.message || 'No tiene permisos para acceder a este módulo.');
                        await router.replace('/dashboard');
                    }
                } finally {
                    cargandoVista.value = false;
                }
            }

            onMounted(() => {
                document.title = `${titulo} | Gelawitto ERP`;
                cargarDatos();
                cancelarSuscripcion = registrarRecargaVista(cargarDatos);
            });

            onUnmounted(() => {
                if (cancelarSuscripcion) {
                    cancelarSuscripcion();
                }
            });

            return () => {
                if (!datos.value) {
                    return h(
                        'div',
                        { class: 'min-h-screen flex items-center justify-center bg-background text-muted-foreground text-sm' },
                        'Sincronizando módulo con Gelawitto API...'
                    );
                }
                return h(Componente, datos.value);
            };
        },
    });
}

const routes: RouteRecordRaw[] = [
    { path: '/', redirect: '/dashboard' },
    {
        path: '/login',
        name: 'login',
        meta: { requiereAuth: false },
        component: crearVistaErp('login', 'Iniciar sesión', Login),
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        meta: { requiereAuth: true, claveModulo: 'dashboard' },
        component: crearVistaErp('dashboard', 'Tablero Principal', Dashboard),
    },
    {
        path: '/catalogos',
        name: 'catalogos',
        meta: { requiereAuth: true, claveModulo: 'catalogos' },
        component: crearVistaErp('catalogos', 'Catálogos Maestros', Catalogos),
    },
    {
        path: '/compras',
        name: 'compras',
        meta: { requiereAuth: true, claveModulo: 'compras' },
        component: crearVistaErp('compras', 'Compras a Proveedores', Compras),
    },
    {
        path: '/inventario',
        name: 'inventario',
        meta: { requiereAuth: true, claveModulo: 'inventario' },
        component: crearVistaErp('inventario', 'Inventario y Kardex', Inventario),
    },
    {
        path: '/produccion',
        name: 'produccion',
        meta: { requiereAuth: true, claveModulo: 'produccion' },
        component: crearVistaErp('produccion', 'Recetas y Producción', Produccion),
    },
    {
        path: '/calidad',
        name: 'calidad',
        meta: { requiereAuth: true, claveModulo: 'calidad' },
        component: crearVistaErp('calidad', 'Calidad y Trazabilidad', Calidad),
    },
    {
        path: '/ventas',
        name: 'ventas',
        meta: { requiereAuth: true, claveModulo: 'ventas' },
        component: crearVistaErp('ventas', 'Ventas y Turno de Caja', Ventas),
    },
    {
        path: '/finanzas',
        name: 'finanzas',
        meta: { requiereAuth: true, claveModulo: 'finanzas' },
        component: crearVistaErp('finanzas', 'Finanzas y Contabilidad', Finanzas),
    },
    {
        path: '/seguridad',
        name: 'seguridad',
        meta: { requiereAuth: true, claveModulo: 'seguridad' },
        component: crearVistaErp('seguridad', 'Usuarios y Auditoría', Seguridad),
    },
];

export const spaRouter = createRouter({
    history: typeof window !== 'undefined' ? createWebHistory() : createMemoryHistory(),
    routes,
});

spaRouter.beforeEach(async (to) => {
    const usuario = await cargarSesion();
    const { modulos } = useAuth();

    if (to.meta.requiereAuth === false) {
        if (usuario) {
            return '/dashboard';
        }
        return true;
    }

    if (!usuario) {
        return '/login';
    }

    const claveModulo = to.meta.claveModulo as string | undefined;
    if (claveModulo && modulos.value.length > 0) {
        const permitido = modulos.value.some((m) => m.clave === claveModulo);
        if (!permitido) {
            notificarError('Su rol operativo no tiene permiso para acceder a esa sección.');
            return '/dashboard';
        }
    }

    return true;
});

export default spaRouter;

