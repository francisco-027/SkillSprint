/**
 * Pointer-tracking glow.
 * Feeds the cursor position (relative to the hovered element) into
 * --glow-x / --glow-y CSS variables; the glow visuals live in pages.css.
 * One throttled listener for the whole document.
 */
const SELECTOR = [
  '.glow-card', '.fc-card', '.fc-list-item',
  '.stat-card', '.skill-card', '.insight-card', '.skill-prog-card',
  '.output-type-card', '.theme-swatch', '.badge-item', '.btn-grad', '.btn-ghost',
  '.material-row-clickable', '.upload-tab', '.sample-chip', '.font-chip', '.size-preset',
].join(',');

export function initGlow() {
  let queued = false;
  let lastEvent = null;

  document.addEventListener('pointermove', (e) => {
    lastEvent = e;
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => {
      queued = false;
      const el = lastEvent.target.closest ? lastEvent.target.closest(SELECTOR) : null;
      if (!el) return;
      const r = el.getBoundingClientRect();
      el.style.setProperty('--glow-x', (lastEvent.clientX - r.left) + 'px');
      el.style.setProperty('--glow-y', (lastEvent.clientY - r.top) + 'px');
    });
  }, { passive: true });
}

export default initGlow;
