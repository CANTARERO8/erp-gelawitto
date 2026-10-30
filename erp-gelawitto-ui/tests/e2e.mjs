import { firefox } from 'playwright';

async function run() {
    console.log('Iniciando prueba E2E en navegador Firefox con Playwright...');
    const browser = await firefox.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    // 1. Login
    console.log('1. Navegando a http://localhost:5174/login...');
    await page.goto('http://localhost:5174/login', { waitUntil: 'networkidle' });
    await page.waitForSelector('#login-email');

    console.log('2. Ingresando credenciales y haciendo clic en "Inicia sesion"...');
    await page.fill('#login-email', 'admin@gelawitto.com');
    await page.fill('#login-password', 'password123');
    await page.click('button[type="submit"]');

    await page.waitForURL('**/dashboard', { timeout: 10000 });
    await page.waitForSelector('text=Tablero Ejecutivo', { timeout: 10000 });
    console.log('   OK: Clic en Login exitoso, redirigido a /dashboard.');

    // 2. Modulos
    const modulos = [
        { nombre: 'Catalogos Maestros', href: '/catalogos', texto: 'Catalogos Maestros' },
        { nombre: 'Compras a Proveedores', href: '/compras', texto: 'Compras y Abastecimiento' },
        { nombre: 'Inventario y Kardex', href: '/inventario', texto: 'Inventario y Libro de Kardex' },
        { nombre: 'Recetas y Produccion', href: '/produccion', texto: 'Formulacion, MRP y Produccion por Lotes' },
        { nombre: 'Calidad y Trazabilidad', href: '/calidad', texto: 'Control de Calidad y Trazabilidad' },
        { nombre: 'Ventas y Turno de Caja', href: '/ventas', texto: 'Punto de Venta y Control de Caja' },
        { nombre: 'Finanzas y Cuentas', href: '/finanzas', texto: 'Finanzas Operativas y Contabilidad General' },
        { nombre: 'Usuarios y Auditoria', href: '/seguridad', texto: 'Seguridad, Roles y Auditoria' },
    ];

    for (const mod of modulos) {
        console.log(`3. Haciendo clic en menu "${mod.nombre}" (${mod.href})...`);
        const navLink = page.locator(`nav a[href="${mod.href}"]`);
        await navLink.click();
        await page.waitForURL(`**${mod.href}`, { timeout: 10000 });
        console.log(`   OK: Modulo "${mod.nombre}" cargado correctamente.`);
    }

    // 3. Tema y Sidebar
    console.log('4. Haciendo clic en boton de tema...');
    const botonTema = page.locator('button[aria-label="Cambiar tema claro u oscuro"]');
    await botonTema.click();
    await page.waitForTimeout(200);

    console.log('5. Haciendo clic en alternar barra lateral...');
    const botonSidebar = page.locator('button[aria-label="Alternar barra lateral"]');
    await botonSidebar.click();
    await page.waitForTimeout(200);

    // 4. Logout
    console.log('6. Haciendo clic en "Cerrar sesion"...');
    const botonLogout = page.locator('aside button:has-text("Cerrar sesion"), aside button:has(svg.lucide-log-out)');
    await botonLogout.click();
    await page.waitForURL('**/login', { timeout: 10000 });
    await page.waitForSelector('#login-email', { timeout: 10000 });
    console.log('   OK: Clic en Cerrar sesion exitoso, sesion cerrada.');

    console.log('\nPrueba E2E en navegador completada exitosamente sin errores.');
    await browser.close();
}

run().catch((err) => {
    console.error('Fallo en la prueba E2E:', err);
    process.exit(1);
});
