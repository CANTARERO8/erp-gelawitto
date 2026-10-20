<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Sun, Moon } from 'lucide-vue-next';

const props = defineProps<{
    failedAttempts?: number;
    lockoutSeconds?: number;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const isDark = ref(false);
const attemptsCount = ref(props.failedAttempts ?? 0);
const lockoutTimer = ref(props.lockoutSeconds ?? 0);
const showRecoveryNote = ref(false);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

const isLockedOut = computed(() => lockoutTimer.value > 0);

const buttonLabel = computed(() => {
    if (isLockedOut.value) {
        return 'Por favor espere...';
    }
    if (form.processing) {
        return 'Validando credenciales...';
    }
    return 'Inicia sesión';
});



onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
    if (lockoutTimer.value > 0) {
        startCountdown();
    }
});

onUnmounted(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});

function startCountdown() {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
    countdownInterval = setInterval(() => {
        if (lockoutTimer.value > 0) {
            lockoutTimer.value -= 1;
        } else if (countdownInterval) {
            clearInterval(countdownInterval);
            attemptsCount.value = 0;
        }
    }, 1000);
}

function toggleTheme() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('gelawitto-theme', isDark.value ? 'dark' : 'light');
}


function submitLogin() {
    if (isLockedOut.value) return;

    form.post('/login', {
        onError: (errors) => {
            if (errors.attempts) {
                attemptsCount.value = Number(errors.attempts);
            } else {
                attemptsCount.value += 1;
            }
            if (errors.timer) {
                lockoutTimer.value = Number(errors.timer);
                startCountdown();
            } else if (attemptsCount.value >= 5 && lockoutTimer.value === 0) {
                lockoutTimer.value = 60;
                startCountdown();
            }
        },
    });
}
</script>

<template>
    <Head title="Iniciar sesión" />

    <div class="min-h-screen w-full grid grid-cols-12 bg-background text-foreground">
        <!-- Columna izquierda heroica (8 columnas en escritorio, inspirada en el Login v2 de Carnes San Martín) -->
        <div class="hidden lg:flex lg:col-span-8 items-center justify-center p-8 bg-surface-elevated relative overflow-hidden">
            <div class="relative w-full max-w-5xl flex items-center justify-center px-6">
                <div class="relative w-full overflow-hidden rounded-[24px] shadow-2xl border border-border/60">
                    <img
                        src="/images/login-gelawitto.jpg"
                        alt="Selección de gelato artesanal Gelawitto sobre piedra pizarra"
                        class="w-full h-[78vh] object-cover block"
                    />



                    <!-- Pie editorial sobre la fotografía -->
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-8 text-white">
                        <p class="text-xs uppercase tracking-widest text-durazno font-semibold">
                            Control Operativo Integrado
                        </p>
                        <h2 class="text-2xl font-titulo font-semibold mt-1 max-w-2xl">
                            Trazabilidad de insumos lácteos, fruta fresca, lotes de mantecación y punto de venta.
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha de autenticación (4 columnas en escritorio) -->
        <div class="col-span-12 lg:col-span-4 flex flex-col justify-between bg-surface px-6 py-8 sm:px-10 lg:px-12 border-l border-border">
            <!-- Barra superior con logo y selector de tema -->
            <div class="flex items-center justify-between gap-4">
                <img
                    src="/images/gelawitto.svg"
                    alt="Gelawitto ERP"
                    class="h-10 w-auto object-contain rounded-xl"
                />

                <button
                    type="button"
                    @click="toggleTheme"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-border bg-background hover:bg-muted text-xs font-medium text-foreground transition-colors cursor-pointer"
                    aria-label="Cambiar entre modo claro y oscuro"
                >
                    <Sun v-if="isDark" class="w-4 h-4 text-durazno" />
                    <Moon v-else class="w-4 h-4 text-frambuesa" />
                    <span>{{ isDark ? 'Claro' : 'Oscuro' }}</span>
                </button>
            </div>

            <!-- Formulario central -->
            <div class="w-full max-w-md mx-auto my-auto py-8">
                <h1 class="text-2xl sm:text-3xl font-titulo font-bold text-foreground mb-1.5">
                    Bienvenido a Gelawitto
                </h1>
                <p class="text-sm text-muted-foreground mb-6">
                    Inicia sesión para acceder al sistema de planificación y control interno
                </p>

                <form @submit.prevent="submitLogin" class="space-y-4" novalidate>
                    <!-- Correo electrónico -->
                    <div>
                        <label for="login-email" class="block text-sm font-medium text-foreground mb-1.5">
                            Correo electrónico
                        </label>
                        <input
                            id="login-email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            autofocus
                            placeholder="admin@gelawitto.com"
                            class="w-full h-11 px-3.5 rounded-lg border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-fresa transition-shadow"
                            :class="form.errors.email ? 'border-destructive' : 'border-input'"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs font-medium text-estado-rojo-texto">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Contraseña con conmutador de visibilidad -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="login-password" class="block text-sm font-medium text-foreground">
                                Contraseña
                            </label>
                            <button
                                type="button"
                                @click="showRecoveryNote = !showRecoveryNote"
                                class="text-xs font-medium text-frambuesa hover:underline cursor-pointer"
                            >
                                Olvidé mi contraseña
                            </button>
                        </div>

                        <div class="relative">
                            <input
                                id="login-password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="Ingresa tu contraseña"
                                class="w-full h-11 pl-3.5 pr-11 rounded-lg border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-fresa transition-shadow"
                                :class="form.errors.password ? 'border-destructive' : 'border-input'"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 px-3.5 flex items-center text-muted-foreground hover:text-foreground cursor-pointer"
                                :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                            >
                                <EyeOff v-if="showPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs font-medium text-estado-rojo-texto">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Nota de recuperación de acceso -->
                    <div
                        v-if="showRecoveryNote"
                        class="p-3 rounded-lg bg-secondary/70 border border-border text-xs text-secondary-foreground"
                    >
                        Por políticas de seguridad interna, solicita el restablecimiento de tu credencial al Administrador del ERP en el módulo de Seguridad.
                    </div>

                    <!-- Casilla Recuérdame -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember-me" class="inline-flex items-center gap-2.5 text-sm text-foreground cursor-pointer">
                            <input
                                id="remember-me"
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 rounded border-input text-fresa focus:ring-fresa"
                            />
                            <span>Recuérdame en este equipo</span>
                        </label>
                    </div>

                    <!-- Alerta de bloqueo temporal por intentos fallidos (igual a Carnes San Martín) -->
                    <div
                        v-if="isLockedOut"
                        class="p-3.5 rounded-lg bg-estado-rojo border border-estado-rojo-texto/30 text-estado-rojo-texto text-xs font-semibold text-center"
                        role="alert"
                    >
                        Límite de intentos superados, por favor espere {{ lockoutTimer }} segundos.
                    </div>

                    <!-- Botón de envío -->
                    <button
                        type="submit"
                        :disabled="form.processing || isLockedOut"
                        class="w-full h-11 rounded-lg bg-fresa hover:bg-frambuesa disabled:opacity-55 text-white font-semibold text-sm shadow-sm transition-colors cursor-pointer"
                    >
                        {{ buttonLabel }}
                    </button>
                </form>
            </div>

            <!-- Pie de columna -->
            <div class="text-center text-xs text-muted-foreground">
                Gelawitto Heladería Artesanal, PostgreSQL 16 y Laravel Inertia
            </div>
        </div>
    </div>
</template>
