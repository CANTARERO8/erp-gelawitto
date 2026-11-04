// Conteo animado de cifras: cualquier .kpi-num nuevo o actualizado cuenta desde su valor previo.
const previo = {};
function contar(el) {
  const k = el.dataset.k, hasta = parseFloat(el.dataset.v), dec = parseInt(el.dataset.d || 0);
  const desde = previo[k] ?? 0; previo[k] = hasta;
  const fmt = v => v.toLocaleString("es-MX", { minimumFractionDigits: dec, maximumFractionDigits: dec });
  if (matchMedia("(prefers-reduced-motion: reduce)").matches) { el.textContent = fmt(hasta); return; }
  const t0 = performance.now();
  (function paso(t) {
    const p = Math.min((t - t0) / 900, 1), e = 1 - Math.pow(1 - p, 3);
    el.textContent = fmt(desde + (hasta - desde) * e);
    if (p < 1) requestAnimationFrame(paso);
  })(t0);
}
new MutationObserver(ms => ms.forEach(m => m.addedNodes.forEach(n => {
  if (n.nodeType !== 1) return;
  if (n.classList.contains("kpi-num")) contar(n);
  n.querySelectorAll && n.querySelectorAll(".kpi-num").forEach(contar);
}))).observe(document.documentElement, { childList: true, subtree: true });
