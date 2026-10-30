import { reactive } from 'vue';
import apiService from '@/services/apiService';
import { notificarExito, notificarError } from '@/composables/useAuth';

export interface OpcionesEnvio {
    preserveScroll?: boolean;
    onSuccess?: (respuesta?: any) => void | Promise<void>;
    onError?: (errores: Record<string, string>) => void;
    onFinish?: () => void;
}

let suscriptoresRecarga: Set<() => void | Promise<void>> = new Set();

export function registrarRecargaVista(fn: () => void | Promise<void>) {
    suscriptoresRecarga.add(fn);
    return () => {
        suscriptoresRecarga.delete(fn);
    };
}

export async function ejecutarRecargaVista() {
    for (const fn of suscriptoresRecarga) {
        await fn();
    }
}

export async function ejecutarAccionApi(
    ruta: string,
    payload: Record<string, any> = {},
    opciones: OpcionesEnvio = {}
) {
    try {
        const endpointLimpio = ruta.replace(/^\/+/, '');
        const respuesta = await apiService('').post(endpointLimpio, payload);
        if (respuesta?.message) {
            notificarExito(respuesta.message);
        }
        await ejecutarRecargaVista();
        if (opciones.onSuccess) {
            await opciones.onSuccess(respuesta);
        }
        return respuesta;
    } catch (error: any) {
        const msg = error?.response?.data?.message || 'Error al procesar la solicitud.';
        notificarError(String(msg));
        if (opciones.onError) {
            opciones.onError({ general: String(msg) });
        }
    } finally {
        if (opciones.onFinish) {
            opciones.onFinish();
        }
    }
}

export function useFormulario<T extends Record<string, any>>(valoresIniciales: T) {
    const copiaInicial = JSON.parse(JSON.stringify(valoresIniciales));

    const form = reactive<Record<string, any>>({
        ...JSON.parse(JSON.stringify(copiaInicial)),
        processing: false,
        errors: {} as Record<string, string>,
        hasErrors: false,
        reset(...campos: string[]) {
            if (campos.length === 0) {
                Object.keys(copiaInicial).forEach((k) => {
                    form[k] = JSON.parse(JSON.stringify(copiaInicial[k]));
                });
            } else {
                campos.forEach((k) => {
                    if (k in copiaInicial) {
                        form[k] = JSON.parse(JSON.stringify(copiaInicial[k]));
                    }
                });
            }
        },
        clearErrors() {
            form.errors = {};
            form.hasErrors = false;
        },
        async post(ruta: string, opciones: OpcionesEnvio = {}) {
            form.processing = true;
            form.errors = {};
            form.hasErrors = false;

            const payload: Record<string, any> = {};
            Object.keys(copiaInicial).forEach((k) => {
                payload[k] = form[k];
            });

            try {
                const endpointLimpio = ruta.replace(/^\/+/, '');
                const respuesta = await apiService('').post(endpointLimpio, payload);
                if (respuesta?.message) {
                    notificarExito(respuesta.message);
                }
                await ejecutarRecargaVista();
                if (opciones.onSuccess) {
                    await opciones.onSuccess(respuesta);
                }
                return respuesta;
            } catch (error: any) {
                const rawErrors = error?.response?.data?.errors || {};
                const flatErrors: Record<string, string> = {};
                Object.entries(rawErrors).forEach(([campo, msgs]) => {
                    flatErrors[campo] = Array.isArray(msgs) ? String(msgs[0]) : String(msgs);
                });
                if (Object.keys(flatErrors).length === 0 && error?.response?.data?.message) {
                    flatErrors.general = String(error.response.data.message);
                }
                const primerMensaje = Object.values(flatErrors)[0];
                if (primerMensaje && ruta !== '/login') {
                    notificarError(primerMensaje);
                }
                form.errors = flatErrors;
                form.hasErrors = Object.keys(flatErrors).length > 0;
                if (opciones.onError) {
                    opciones.onError(flatErrors);
                }
            } finally {
                form.processing = false;
                if (opciones.onFinish) {
                    opciones.onFinish();
                }
            }
        },
    });

    return form as T & {
        processing: boolean;
        errors: Record<string, string>;
        hasErrors: boolean;
        reset: (...campos: string[]) => void;
        clearErrors: () => void;
        post: (ruta: string, opciones?: OpcionesEnvio) => Promise<any>;
    };
}
