<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Package,
    ShoppingBag,
    Boxes,
    FlaskConical,
    Receipt,
    Landmark,
    ShieldCheck,
    Sun,
    Moon,
    LogOut,
    PanelLeftClose,
    PanelLeftOpen,
    X,
} from 'lucide-vue-next';

defineProps<{
    title: string;
    subtitle?: string;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as { user: { id: number; name: string; email: string; rol: string } | null })?.user);
const flash = computed(() => page.props.flash as { success?: string | null; error?: string | null });

const isCollapsed = ref(false);
const isDark = ref(false);
const toastVisible = ref(false);
const toastMessage = ref('');
const toastVariant = ref<'success' | 'error'>('success');

const navItems = [
    { name: 'Tablero Principal', href: '/dashboard', routeMatch: '/dashboard', icon: LayoutDashboard },
    { name: 'Catálogos Maestros', href: '/catalogos', routeMatch: '/catalogos', icon: Package },
    { name: 'Compras a Proveedores', href: '/compras', routeMatch: '/compras', icon: ShoppingBag },
    { name: 'Inventario y Kardex', href: '/inventario', routeMatch: '/inventario', icon: Boxes },
    { name: 'Recetas y Producción', href: '/produccion', routeMatch: '/produccion', icon: FlaskConical },
    { name: 'Ventas y Turno de Caja', href: '/ventas', routeMatch: '/ventas', icon: Receipt },
    { name: 'Finanzas y Cuentas', href: '/finanzas', routeMatch: '/finanzas', icon: Landmark },
    { name: 'Usuarios y Auditoría', href: '/seguridad', routeMatch: '/seguridad', icon: ShieldCheck },
];

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
    const savedCollapse = localStorage.getItem('gelawitto-sidebar-collapsed');
    if (savedCollapse === 'true') {
        isCollapsed.value = true;
    }
});

watch(
    () => [flash.value?.success, flash.value?.error],
    ([successMsg, errorMsg]) => {
        if (successMsg) {
            toastVariant.value = 'success';
            toastMessage.value = successMsg;
            toastVisible.value = true;
            setTimeout(() => {
                toastVisible.value = false;
            }, 4000);
        } else if (errorMsg) {
            toastVariant.value = 'error';
            toastMessage.value = errorMsg;
            toastVisible.value = true;
            setTimeout(() => {
                toastVisible.value = false;
            }, 4500);
        }
    },
    { immediate: true }
);

function toggleTheme() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('gelawitto-theme', isDark.value ? 'dark' : 'light');
}

function toggleSidebar() {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('gelawitto-sidebar-collapsed', String(isCollapsed.value));
}

function isActive(match: string): boolean {
    return page.url.startsWith(match);
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen flex bg-background text-foreground">
        <!-- Menú lateral estilo semi-dark inspirado en Carnes San Martín con identidad Mora/Fresa -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-30 flex flex-col bg-sidebar-bg text-sidebar-fg border-r border-white/10 transition-all duration-200',
                isCollapsed ? 'w-20' : 'w-68',
            ]"
        >
            <div class="h-18 flex items-center justify-between px-5 border-b border-white/10">
                <Link href="/dashboard" class="flex items-center gap-3 overflow-hidden">
                    <div class="w-10 h-10 rounded-lg bg-fresa flex items-center justify-center text-white font-titulo font-bold text-xl shrink-0 shadow-sm">
                        G
                    </div>
                    <div v-if="!isCollapsed" class="leading-tight">
                        <span class="block font-titulo text-lg font-semibold tracking-tight text-white">Gelawitto</span>
                        <span class="block text-xs text-sidebar-muted">Sistema ERP Artesanal</span>
                    </div>
                </Link>
            </div>

            <nav class="flex-1 px-3 py-5 space-y-1.5 overflow-y-auto" aria-label="Navegación principal del ERP">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3.5 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors',
                        isActive(item.routeMatch)
                            ? 'bg-fresa text-white shadow-sm'
                            : 'text-sidebar-fg/80 hover:bg-white/8 hover:text-white',
                    ]"
                >
                    <component :is="item.icon" class="w-5 h-5 shrink-0" />
                    <span v-if="!isCollapsed" class="truncate">{{ item.name }}</span>
                </Link>
            </nav>

            <div class="p-3 border-t border-white/10">
                <div v-if="!isCollapsed && user" class="px-3 py-2.5 mb-2 rounded-lg bg-white/6">
                    <p class="text-sm font-semibold text-white truncate">{{ user.name }}</p>
                    <p class="text-xs text-sidebar-muted truncate">{{ user.rol }}, {{ user.email }}</p>
                </div>
                <button
                    type="button"
                    @click="logout"
                    class="w-full flex items-center justify-center gap-2.5 px-3.5 py-2.5 rounded-lg text-sm font-medium text-sidebar-fg/85 hover:bg-white/10 hover:text-white transition-colors cursor-pointer"
                >
                    <LogOut class="w-4 h-4 shrink-0" />
                    <span v-if="!isCollapsed">Cerrar sesión</span>
                </button>
            </div>
        </aside>

        <!-- Contenedor principal -->
        <div :class="['flex-1 flex flex-col min-w-0 transition-all duration-200', isCollapsed ? 'pl-20' : 'pl-68']">
            <!-- Barra superior sticky con desenfoque -->
            <header class="sticky top-0 z-20 h-18 px-6 lg:px-8 bg-surface/85 backdrop-blur-md border-b border-border flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <button
                        type="button"
                        @click="toggleSidebar"
                        class="p-2 rounded-lg border border-border bg-surface hover:bg-muted text-foreground transition-colors cursor-pointer"
                        aria-label="Alternar barra lateral"
                    >
                        <PanelLeftOpen v-if="isCollapsed" class="w-4 h-4" />
                        <PanelLeftClose v-else class="w-4 h-4" />
                    </button>
                    <div class="min-w-0">
                        <h1 class="text-xl font-titulo font-semibold text-foreground truncate">{{ title }}</h1>
                        <p v-if="subtitle" class="text-xs text-muted-foreground truncate">{{ subtitle }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span
                        v-if="user"
                        class="hidden sm:inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-secondary text-secondary-foreground border border-border"
                    >
                        Rol: {{ user.rol }}
                    </span>

                    <button
                        type="button"
                        @click="toggleTheme"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-border bg-surface hover:bg-muted text-xs font-medium text-foreground transition-colors cursor-pointer"
                        aria-label="Cambiar tema claro u oscuro"
                    >
                        <Sun v-if="isDark" class="w-4 h-4 text-durazno" />
                        <Moon v-else class="w-4 h-4 text-frambuesa" />
                        <span class="hidden md:inline">{{ isDark ? 'Modo Claro' : 'Modo Oscuro' }}</span>
                    </button>
                </div>
            </header>

            <!-- Notificación emergente superior derecha -->
            <div
                v-if="toastVisible"
                class="fixed top-5 right-5 z-50 max-w-md w-full rounded-lg border p-4 shadow-lg transition-all"
                :class="
                    toastVariant === 'success'
                        ? 'bg-estado-verde text-estado-verde-texto border-estado-verde-texto/25'
                        : 'bg-estado-rojo text-estado-rojo-texto border-estado-rojo-texto/25'
                "
                role="status"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold">
                            {{ toastVariant === 'success' ? 'Operación confirmada' : 'Aviso del sistema' }}
                        </p>
                        <p class="text-xs mt-0.5">{{ toastMessage }}</p>
                    </div>
                    <button
                        type="button"
                        @click="toastVisible = false"
                        class="p-1 rounded hover:bg-black/5 cursor-pointer"
                        aria-label="Cerrar notificación"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Contenido de la vista -->
            <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
                <slot />
            </main>
        </div>
    </div>
</template>
