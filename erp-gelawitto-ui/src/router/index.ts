import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { defineComponent, h, onMounted, watch, type Component } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { fetchErpPage } from '@/services/api';
import { pageState, registerSpaNavigator } from '@/adapters/inertia-spa';

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

const componentMap: Record<string, Component> = {
    'Auth/Login': Login,
    'Erp/Dashboard': Dashboard,
    'Erp/Catalogos': Catalogos,
    'Erp/Compras': Compras,
    'Erp/Inventario': Inventario,
    'Erp/Produccion': Produccion,
    'Erp/Calidad': Calidad,
    'Erp/Ventas': Ventas,
    'Erp/Finanzas': Finanzas,
    'Erp/Seguridad': Seguridad,
};

const SpaRouteHost = defineComponent({
    name: 'SpaRouteHost',
    setup() {
        const route = useRoute();
        const vueRouter = useRouter();

        registerSpaNavigator(async (targetUrl: string) => {
            if (route.fullPath !== targetUrl) {
                await vueRouter.push(targetUrl);
            }
        });

        async function loadRouteData(path: string) {
            pageState.loading = true;
            try {
                const payload = await fetchErpPage(path);
                pageState.component = payload.component;
                pageState.props = payload.props;
                pageState.url = payload.url;

                if (payload.url && payload.url !== path) {
                    await vueRouter.replace(payload.url);
                }
            } finally {
                pageState.loading = false;
            }
        }

        onMounted(() => {
            if (pageState.url !== route.fullPath || !pageState.component) {
                loadRouteData(route.fullPath);
            }
        });

        watch(
            () => route.fullPath,
            (newPath) => {
                if (pageState.url !== newPath || !pageState.component) {
                    loadRouteData(newPath);
                }
            }
        );

        return () => {
            const ActivePage = componentMap[pageState.component];
            if (!ActivePage) {
                return h(
                    'div',
                    { class: 'min-h-screen flex items-center justify-center bg-background text-muted-foreground text-sm' },
                    'Sincronizando módulo con Gelawitto API...'
                );
            }
            return h(ActivePage, pageState.props);
        };
    },
});

const routes: RouteRecordRaw[] = [
    { path: '/', redirect: '/dashboard' },
    { path: '/login', name: 'login', component: SpaRouteHost },
    { path: '/dashboard', name: 'dashboard', component: SpaRouteHost },
    { path: '/catalogos', name: 'catalogos', component: SpaRouteHost },
    { path: '/compras', name: 'compras', component: SpaRouteHost },
    { path: '/inventario', name: 'inventario', component: SpaRouteHost },
    { path: '/produccion', name: 'produccion', component: SpaRouteHost },
    { path: '/calidad', name: 'calidad', component: SpaRouteHost },
    { path: '/ventas', name: 'ventas', component: SpaRouteHost },
    { path: '/finanzas', name: 'finanzas', component: SpaRouteHost },
    { path: '/seguridad', name: 'seguridad', component: SpaRouteHost },
];

export const spaRouter = createRouter({
    history: createWebHistory(),
    routes,
});
