import { describe, it, expect } from 'vitest';
import { useAuth, establecerSesion, limpiarSesion, notificarExito, notificarError } from '../src/composables/useAuth';
import { useFormulario } from '../src/composables/useFormulario';

describe('useAuth & useFormulario composables', () => {
    it('gestiona la sesión reactiva y parámetros de base de datos correctamente', () => {
        const { usuario, modulos, parametros, ivaPorcentaje, mensajeExito, mensajeError } = useAuth();

        establecerSesion({
            user: { id: 1, name: 'Admin', email: 'admin@gelawitto.com', rol: 'Administrador' },
            modulos: [{ id: 1, clave: 'dashboard', nombre: 'Tablero Principal', ruta: '/dashboard', seccion: 'Operaciones', orden: 1 }],
            parametros: { iva_porcentaje: '15' },
        });

        expect(usuario.value?.name).toBe('Admin');
        expect(modulos.value.length).toBe(1);
        expect(modulos.value[0].clave).toBe('dashboard');
        expect(parametros.value.iva_porcentaje).toBe('15');
        expect(ivaPorcentaje.value).toBe(15);

        notificarExito('Guardado con éxito');
        expect(mensajeExito.value).toBe('Guardado con éxito');
        expect(mensajeError.value).toBeNull();

        notificarError('Error de validación');
        expect(mensajeError.value).toBe('Error de validación');
        expect(mensajeExito.value).toBeNull();

        limpiarSesion();
        expect(usuario.value).toBeNull();
        expect(modulos.value.length).toBe(0);
    });

    it('useFormulario inicializa, resetea campos y gestiona errores', () => {
        const form = useFormulario({
            nombre: 'Mango Real',
            precio: '150.00',
        });

        expect(form.nombre).toBe('Mango Real');
        expect(form.precio).toBe('150.00');
        expect(form.processing).toBe(false);

        form.nombre = 'Fresa Silvestre';
        form.precio = '200.00';
        expect(form.nombre).toBe('Fresa Silvestre');

        form.reset('nombre');
        expect(form.nombre).toBe('Mango Real');
        expect(form.precio).toBe('200.00');

        form.reset();
        expect(form.precio).toBe('150.00');
    });
});
