/**
 * Applies a user's accessibility/appearance preferences to the live DOM.
 * Used by AppLayout (on load) and Settings (live, as the user toggles).
 * Pairs with the `body.*` rules + base CSS variables in resources/css/pages.css.
 */
const FONT_STACKS = {
  inter:     "'Inter', system-ui, sans-serif",
  jakarta:   "'Plus Jakarta Sans', system-ui, sans-serif",
  georgia:   "Georgia, 'Times New Roman', serif",
  monospace: "'SFMono-Regular', Menlo, Consolas, monospace",
};

export function applyPreferences(prefs) {
  if (!prefs) return;

  const body = document.body;
  const root = document.documentElement;

  // --- toggles -> body classes ---
  body.classList.toggle('dyslexia-font', !!prefs.dyslexia_font);
  body.classList.toggle('bold-text', !!prefs.bold_text);
  body.classList.toggle('focus-indicators', prefs.focus_indicators !== false);
  body.classList.toggle('reduce-motion', !!prefs.reduce_motion);
  body.classList.toggle('wider-column', !!prefs.wider_reading_column);
  body.classList.toggle('highlight-line', !!prefs.highlight_active_line);

  // --- high contrast + theme ---
  body.classList.toggle('high-contrast', !!prefs.high_contrast);
  ['contrast-high_dark', 'contrast-high_light', 'contrast-yellow'].forEach((c) => body.classList.remove(c));
  if (prefs.high_contrast && prefs.contrast_theme && prefs.contrast_theme !== 'default') {
    body.classList.add('contrast-' + prefs.contrast_theme);
  }

  // --- numeric / variable-driven (reuse the base variables in pages.css) ---
  root.style.setProperty('--font-size-base', (prefs.font_size || 16) + 'px');
  root.style.setProperty('--line-height-base', prefs.line_height || 1.6);
  root.style.setProperty('--letter-spacing-base', Number(prefs.letter_spacing) > 0 ? '0.05em' : '0');
  root.style.setProperty('--word-spacing-base', Number(prefs.word_spacing) > 0 ? '0.1em' : '0');
  root.style.setProperty('--font-family-base', FONT_STACKS[prefs.font_family] || FONT_STACKS.inter);
}

export default applyPreferences;
