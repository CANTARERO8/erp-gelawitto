import { reactive, computed } from 'vue';
import apiService from '@/services/apiService';

export interface UsuarioAutenticado {
    id: number;
    name: string;
    email: string;
    rol: string;
    rol_id?: number | null;
}

export interface ModuloMenu {
    id: number;
    clave: string;
    nombre: string;
    ruta: string;
    seccion: string;
    orden: number;
}

const estado = reactive<{
    usuario: UsuarioAutenticado | null;
    modulos: ModuloMenu[];
    parametros: Record<string, string>;
    mensajeExito: string | null;
    mensajeError: string | null;
    inicializado: boolean;
}>({
    usuario: null,
    modulos: [],
    parametros: {},
    mensajeExito: null,
    mensajeError: null,
    inicializado: false,
});

let temporizadorMensaje: ReturnType<typeof setTimeout> | null = null;

export function notificarExito(mensaje: string | null) {
    estado.mensajeError = null;
    estado.mensajeExito = mensaje;
    if (temporizadorMensaje) {
        clearTimeout(temporizadorMensaje);
    }
    if (mensaje) {
        temporizadorMensaje = setTimeout(() => {
            estado.mensajeExito = null;
        }, 4500);
    }
}

export function notificarError(mensaje: string | null) {
    estado.mensajeExito = null;
    estado.mensajeError = mensaje;
    if (temporizadorMensaje) {
        clearTimeout(temporizadorMensaje);
    }
    if (mensaje) {
        temporizadorMensaje = setTimeout(() => {
            estado.mensajeError = null;
        }, 4500);
    }
}

export function aplicarTemaDom(tema?: string) {
    if (typeof document !== 'undefined') {
        document.documentElement.classList.toggle('dark', tema === 'dark');
    }
}

export function establecerSesion(datos: {
    user: UsuarioAutenticado | null;
    modulos?: ModuloMenu[];
    parametros?: Record<string, string>;
}) {
    estado.usuario = datos.user;
    estado.modulos = datos.modulos ?? [];
    estado.parametros = { ...estado.parametros, ...(datos.parametros ?? {}) };
    aplicarTemaDom(estado.parametros.ui_tema);
    estado.inicializado = true;
}

export async function guardarPreferencia(clave: 'ui_tema' | 'ui_sidebar_colapsado', valor: string) {
    estado.parametros[clave] = valor;
    if (clave === 'ui_tema') {
        aplicarTemaDom(valor);
    }
    try {
        const res = await apiService('preferencias').post('', { [clave]: valor });
        if (res?.parametros) {
            estado.parametros = { ...estado.parametros, ...res.parametros };
        }
    } catch {
        // En entorno de pruebas sin red mantiene el estado reactivo en memoria
    }
}

export function limpiarSesion() {
    estado.usuario = null;
    estado.modulos = [];
    estado.inicializado = true;
}

export async function cargarSesion(forzar = false): Promise<UsuarioAutenticado | null> {
    if (estado.inicializado && !forzar) {
        return estado.usuario;
    }
    try {
        const res = await apiService('me').get();
        establecerSesion(res);
        return estado.usuario;
    } catch {
        limpiarSesion();
        return null;
    }
}

export function useAuth() {
    return {
        usuario: computed(() => estado.usuario),
        modulos: computed(() => estado.modulos),
        parametros: computed(() => estado.parametros),
        ivaPorcentaje: computed(() => Number(estado.parametros.iva_porcentaje ?? 15)),
        mensajeExito: computed(() => estado.mensajeExito),
        mensajeError: computed(() => estado.mensajeError),
        cargarSesion,
        establecerSesion,
        limpiarSesion,
        notificarExito,
        notificarError,
        guardarPreferencia,
        aplicarTemaDom,
    };
}
