// Genera las capturas anotadas del informe final desde el sistema en ejecución.
// Requiere: API en :8000, interfaz en :5174 y, para la última captura, el dashboard de Python en :8501.
// Uso: node tests/capturas.mjs
import { firefox, chromium } from 'playwright';

const salida = '../docs/entregable/capturas/';

async function lanzarNavegador() {
    try {
        return await firefox.launch({ headless: true });
    } catch {
        return await chromium.launch({ headless: true, channel: 'msedge' });
    }
}

// Dibuja un recuadro rojo y una insignia numerada sobre el elemento más pequeño que contiene el texto.
async function marcar(page, marcas) {
    const faltantes = await page.evaluate((lista) => {
        const noEncontrados = [];
        lista.forEach(([texto, n], i) => {
            const t = texto.toLowerCase();
            let mejor = null;
            let area = Infinity;
            document.querySelectorAll('body *').forEach((el) => {
                if (el.closest('[data-marca]')) return;
                const txt = (el.innerText || '').toLowerCase();
                if (!txt.includes(t)) return;
                const r = el.getBoundingClientRect();
                if (r.width < 4 || r.height < 4) return;
                const a = r.width * r.height;
                if (a < area) { area = a; mejor = el; }
            });
            if (!mejor) { noEncontrados.push(texto); return; }
            const r = mejor.getBoundingClientRect();
            const x = r.left + window.scrollX;
            const y = r.top + window.scrollY;
            const caja = document.createElement('div');
            caja.setAttribute('data-marca', '1');
            caja.style.cssText = `position:absolute;left:${x - 4}px;top:${y - 4}px;width:${r.width + 8}px;height:${r.height + 8}px;border:3px solid #e11d48;border-radius:6px;pointer-events:none;z-index:99999;`;
            const ins = document.createElement('div');
            ins.setAttribute('data-marca', '1');
            ins.textContent = String(n);
            ins.style.cssText = `position:absolute;left:${x - 16}px;top:${y - 16}px;width:26px;height:26px;border-radius:50%;background:#e11d48;color:#fff;font:bold 15px Arial;display:flex;align-items:center;justify-content:center;z-index:100000;`;
            document.body.appendChild(caja);
            document.body.appendChild(ins);
        });
        return noEncontrados;
    }, marcas);
    if (faltantes.length) console.log('   (sin marcar:', faltantes.join(' | '), ')');
}

const browser = await lanzarNavegador();
const page = await (await browser.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
await page.goto('http://localhost:5174/login', { waitUntil: 'networkidle' });
await page.fill('#login-email', 'admin@gelawitto.com');
await page.fill('#login-password', 'password123');
await page.click('button[type="submit"]');
await page.waitForURL('**/dashboard');

async function pagina(ruta) {
    await page.goto(`http://localhost:5174/${ruta}`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2500);
    return page.evaluate(() => Math.max(document.body.scrollHeight, document.documentElement.scrollHeight));
}

async function tomar(nombre, y, alto, marcas = []) {
    await marcar(page, marcas);
    await page.screenshot({ path: `${salida}${nombre}.png`, fullPage: true, clip: { x: 0, y, width: 1440, height: alto } });
    console.log('OK', nombre);
    await page.evaluate(() => document.querySelectorAll('[data-marca]').forEach((e) => e.remove()));
}

let h = await pagina('dashboard');
await tomar('c01-dashboard-rentabilidad', 0, 1170, [['MARGEN BRUTO PROMEDIO', 1], ['Curva de Pareto de Ingresos', 2], ['Rentabilidad y Costeo de Recetas Maestras', 3]]);
await tomar('c02-dashboard-compras', 1170, 1220, [['CRÍTICOS BAJO MÍNIMO', 1], ['PRESUPUESTO SUGERIDO DE COMPRA', 2], ['Sugerencia de Reabastecimiento por Proveedor', 3]]);
await tomar('c03-dashboard-oee', 2390, h - 2390, [['OEE GLOBAL PLANTA', 1], ['TIEMPO DE CICLO REAL', 2], ['Desglose de OEE y Tiempo de Ciclo', 3]]);

h = await pagina('catalogos');
await tomar('c04-catalogos', 0, Math.min(h, 900), []);

h = await pagina('compras');
await tomar('c05-compras', 0, Math.min(h, 1000), [['Lote', 1], ['Vencimiento', 2]]);

h = await pagina('inventario');
await tomar('c06-inventario', 0, Math.min(h, 1100), []);

h = await pagina('produccion');
await tomar('c07-produccion-bom', 0, 1000, [['EXPLOSIÓN MRP', 1], ['Nivel 2', 2], ['RUTA DE OPERACIÓN', 3]]);
await tomar('c08-produccion-lotes', Math.max(0, h - 1350), 1350, [['Subensamble Nivel 1', 1], ['Órdenes de Producción', 2]]);

h = await pagina('calidad');
await tomar('c09-calidad', 0, Math.min(h, 1100), [['INSUMOS Y LOTE ORIGEN', 1], ['VENTAS Y CLIENTES', 2], ['INSPECCIONES', 3]]);

h = await pagina('ventas');
await tomar('c10-ventas', 0, h, [['LOTES FEFO', 1], ['Solo permite vender stock liberado', 2]]);

h = await pagina('finanzas');
await tomar('c11-finanzas', 0, Math.min(h, 930), []);

try {
    await page.goto('http://localhost:8501', { waitUntil: 'networkidle', timeout: 15000 });
    await page.waitForTimeout(4000);
    const hd = await page.evaluate(() => Math.max(document.body.scrollHeight, document.documentElement.scrollHeight));
    await page.screenshot({ path: `${salida}c12-dashboard-python.png`, fullPage: true, clip: { x: 0, y: 0, width: 1440, height: Math.min(hd, 2400) } });
    console.log('OK c12-dashboard-python');
} catch (e) {
    console.log('Dashboard de Python no disponible:', e.message);
}

await browser.close();
