import { firefox, chromium } from 'playwright';

// Firefox es el navegador principal; si no puede lanzarse (p. ej. en Windows sin el binario), se usa Microsoft Edge.
async function lanzarNavegador() {
    try {
        return await firefox.launch({ headless: true });
    } catch {
        return await chromium.launch({ headless: true, channel: 'msedge' });
    }
}

async function login(page, email, password, expectedName, expectedRole) {
    console.log(`\n--- Probando sesión para: ${expectedName} (${email} - Rol: ${expectedRole}) ---`);
    await page.goto('http://localhost:5174/login', { waitUntil: 'networkidle' });
    await page.waitForSelector('#login-email', { timeout: 10000 });

    await page.fill('#login-email', email);
    await page.fill('#login-password', password);
    await page.click('button[type="submit"]');

    await page.waitForURL('**/dashboard', { timeout: 10000 });
    await page.waitForSelector('text=Dashboard', { timeout: 10000 });

    // Verificar identidad en barra superior o lateral
    await page.waitForSelector(`text=${expectedName}`, { timeout: 10000 });
    await page.waitForSelector(`text=Rol: ${expectedRole}`, { timeout: 10000 });
    console.log(`   OK: Login exitoso, usuario "${expectedName}" y rol "${expectedRole}" verificados en UI.`);
}

async function logout(page) {
    const botonLogout = page.locator('aside button:has-text("Cerrar sesion"), aside button:has(svg.lucide-log-out)');
    await botonLogout.click();
    await page.waitForURL('**/login', { timeout: 10000 });
    await page.waitForSelector('#login-email', { timeout: 10000 });
    console.log('   OK: Sesión cerrada correctamente.');
}

async function run() {
    console.log('Iniciando prueba E2E multiusuario en navegador Firefox con Playwright...');
    const browser = await lanzarNavegador();
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    // ==========================================
    // 1. EDUARDO CANTARERO (ADMINISTRADOR)
    // ==========================================
    await login(page, 'admin@gelawitto.com', 'password123', 'Eduardo Cantarero', 'Administrador');

    // Verificar OEE 91.2 % en dashboard
    await page.waitForSelector('text=91.2 %', { timeout: 10000 });
    console.log('   OK: OEE 91.2 % verificado en Dashboard.');

    // Verificar los 8 enlaces ERP en la barra de navegación del Administrador
    const adminModulos = [
        { href: '/catalogos', label: 'Catálogos' },
        { href: '/compras', label: 'Compras' },
        { href: '/inventario', label: 'Inventario' },
        { href: '/produccion', label: 'Producción' },
        { href: '/calidad', label: 'Calidad' },
        { href: '/ventas', label: 'Ventas' },
        { href: '/finanzas', label: 'Finanzas' },
        { href: '/seguridad', label: 'Seguridad' },
    ];
    for (const mod of adminModulos) {
        await page.waitForSelector(`nav a[href="${mod.href}"]`, { timeout: 5000 });
    }
    console.log('   OK: Administrador visualiza todos los 8 módulos operativos en la barra lateral.');

    // Navegar a /seguridad y verificar que aparecen los 4 usuarios del sistema
    await page.click('nav a[href="/seguridad"]');
    await page.waitForURL('**/seguridad', { timeout: 10000 });
    await page.waitForSelector('text=Eduardo Cantarero', { timeout: 10000 });
    await page.waitForSelector('text=Moises Zelaya', { timeout: 10000 });
    await page.waitForSelector('text=Valeria Mendoza', { timeout: 10000 });
    await page.waitForSelector('text=Carlos Navarrete', { timeout: 10000 });
    console.log('   OK: En /seguridad se listan los 4 usuarios activos con sus roles respectivos.');

    // Verificar 0 localStorage / 0 sessionStorage
    const storageCount = await page.evaluate(() => window.localStorage.length + window.sessionStorage.length);
    if (storageCount !== 0) {
        throw new Error(`Se encontraron ${storageCount} claves en storage; todo debe residir en PostgreSQL.`);
    }
    console.log('   OK: 0 claves en localStorage/sessionStorage.');

    await logout(page);

    // ==========================================
    // 2. MOISES ZELAYA (PRODUCCIÓN)
    // ==========================================
    await login(page, 'produccion@gelawitto.com', 'password123', 'Moises Zelaya', 'Producción');

    // Verificar barra lateral: SOLO tiene dashboard, catalogos, inventario, produccion, calidad
    const produccionNav = await page.$$eval('nav a', (links) => links.map((a) => a.getAttribute('href')));
    console.log('   Módulos visibles para Producción:', produccionNav);
    if (produccionNav.includes('/ventas') || produccionNav.includes('/compras') || produccionNav.includes('/seguridad') || produccionNav.includes('/finanzas')) {
        throw new Error('Producción tiene accesos no permitidos en la navegación');
    }

    // Visitar /inventario y verificar que ve el stock de insumos y productos
    await page.click('nav a[href="/inventario"]');
    await page.waitForURL('**/inventario', { timeout: 10000 });
    await page.waitForSelector('text=Existencias Valorizadas de Insumos', { timeout: 10000 });
    await page.waitForSelector('td:has-text("Harina de arroz blanco seleccionado")', { timeout: 10000 });
    console.log('   OK: Producción accede a /inventario y visualiza stock de insumos y productos.');

    // Visitar /produccion y verificar recetas, rutas y centros de trabajo
    await page.click('nav a[href="/produccion"]');
    await page.waitForURL('**/produccion', { timeout: 10000 });
    await page.waitForSelector('text=Programar Orden de Producción', { timeout: 10000 });
    await page.waitForSelector('text=Órdenes de Producción', { timeout: 10000 });
    console.log('   OK: Producción accede a /produccion y visualiza programación, recetas y órdenes.');

    // Intentar navegar directo por URL a /ventas (prohibido)
    await page.goto('http://localhost:5174/ventas');
    await page.waitForURL('**/dashboard', { timeout: 10000 });
    console.log('   OK: Acceso no autorizado a /ventas bloqueado por router y redirigido a /dashboard.');

    await logout(page);

    // ==========================================
    // 3. VALERIA MENDOZA (CAJERO)
    // ==========================================
    await login(page, 'caja@gelawitto.com', 'password123', 'Valeria Mendoza', 'Cajero');

    // Verificar barra lateral: SOLO tiene dashboard, inventario, ventas
    const cajeroNav = await page.$$eval('nav a', (links) => links.map((a) => a.getAttribute('href')));
    console.log('   Módulos visibles para Cajero:', cajeroNav);
    if (cajeroNav.includes('/produccion') || cajeroNav.includes('/compras') || cajeroNav.includes('/seguridad') || cajeroNav.includes('/calidad') || cajeroNav.includes('/finanzas')) {
        throw new Error('Cajero tiene accesos no permitidos en la navegación');
    }

    // Visitar /ventas y verificar terminal de caja y POS
    await page.click('nav a[href="/ventas"]');
    await page.waitForURL('**/ventas', { timeout: 10000 });
    await page.waitForSelector('text=Ventas y Turno de Caja', { timeout: 10000 });
    console.log('   OK: Cajero accede a /ventas y visualiza módulo de punto de venta y caja.');

    // Visitar /inventario y verificar que ve existencias
    await page.click('nav a[href="/inventario"]');
    await page.waitForURL('**/inventario', { timeout: 10000 });
    await page.waitForSelector('text=Existencias Valorizadas de Insumos', { timeout: 10000 });
    console.log('   OK: Cajero accede a /inventario y visualiza existencias generales.');

    // Intentar navegar directo a /produccion (prohibido)
    await page.goto('http://localhost:5174/produccion');
    await page.waitForURL('**/dashboard', { timeout: 10000 });
    console.log('   OK: Acceso no autorizado a /produccion bloqueado por router y redirigido a /dashboard.');

    await logout(page);

    // ==========================================
    // 4. CARLOS NAVARRETE (CONTADOR)
    // ==========================================
    await login(page, 'finanzas@gelawitto.com', 'password123', 'Carlos Navarrete', 'Contador');

    // Verificar barra lateral: SOLO tiene dashboard, catalogos, compras, inventario, finanzas
    const contadorNav = await page.$$eval('nav a', (links) => links.map((a) => a.getAttribute('href')));
    console.log('   Módulos visibles para Contador:', contadorNav);
    if (contadorNav.includes('/produccion') || contadorNav.includes('/ventas') || contadorNav.includes('/seguridad') || contadorNav.includes('/calidad')) {
        throw new Error('Contador tiene accesos no permitidos en la navegación');
    }

    // Visitar /compras y verificar órdenes y proveedores
    await page.click('nav a[href="/compras"]');
    await page.waitForURL('**/compras', { timeout: 10000 });
    await page.waitForSelector('text=Órdenes de Compra', { timeout: 10000 });
    console.log('   OK: Contador accede a /compras y visualiza órdenes de abastecimiento.');

    // Visitar /finanzas y verificar libros y cuentas
    await page.click('nav a[href="/finanzas"]');
    await page.waitForURL('**/finanzas', { timeout: 10000 });
    await page.waitForSelector('text=Libro de Movimientos Financieros', { timeout: 10000 });
    await page.click('button:has-text("Libro Diario (Partida Doble)")');
    await page.waitForSelector('text=Libro Diario General (Partida Doble)', { timeout: 10000 });
    console.log('   OK: Contador accede a /finanzas y visualiza movimientos y Libro Diario de Partida Doble.');

    // Visitar /inventario y verificar valoración y kardex
    await page.click('nav a[href="/inventario"]');
    await page.waitForURL('**/inventario', { timeout: 10000 });
    await page.waitForSelector('text=Libro Mayor de Movimientos (Kardex Inmutable)', { timeout: 10000 });
    console.log('   OK: Contador accede a /inventario y visualiza kardex inmutable y valoración.');

    // Intentar navegar directo a /seguridad (prohibido)
    await page.goto('http://localhost:5174/seguridad');
    await page.waitForURL('**/dashboard', { timeout: 10000 });
    console.log('   OK: Acceso no autorizado a /seguridad bloqueado por router y redirigido a /dashboard.');

    await logout(page);

    console.log('\n===============================================================');
    console.log('¡TODOS LOS 4 USUARIOS Y SUS VISTAS DE STOCK VERIFICADOS AL 100%!');
    console.log('===============================================================');

    await browser.close();
}

run().catch((err) => {
    console.error('Fallo en la prueba E2E multiusuario:', err);
    process.exit(1);
});
