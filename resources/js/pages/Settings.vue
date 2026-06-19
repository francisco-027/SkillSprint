<template>
  <app-layout active-page="settings" page-title="Settings">
    <!-- Header -->
    <div class="page-head-row">
      <div>
        <span class="badge-pill"><ic-settings :size="14" /> Settings & Accessibility</span>
        <h1 class="skills-hero" style="font-size:30px;margin:8px 0 4px">Your <span class="grad-text">Preferences</span></h1>
        <p style="color:var(--text-muted);margin:0">Customize SkillSprint to work best for you. All changes are saved automatically.</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn-ghost" @click="resetDefaults"><ic-repeat :size="15" /> Reset Defaults</button>
        <button class="btn-grad" @click="saveAll"><ic-check :size="15" /> Save All</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-4">
      <div class="skel" style="width:100%;height:120px;margin-bottom:12px"></div>
      <div class="skel" style="width:100%;height:240px"></div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

    <template v-if="prefs && !loading">
      <div class="row g-4 mt-1">
        <!-- Category Nav -->
        <div class="col-lg-3">
          <div class="content-card" style="position:sticky;top:20px">
            <div class="settings-cats-title">Categories</div>
            <button v-for="c in categories" :key="c.key" type="button"
                    class="settings-cat" :class="{ active: activeCategory === c.key }"
                    @click="activeCategory = c.key">
              <component :is="c.icon" :size="16" /> {{ c.label }}
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="col-lg-9">
          <!-- ============ ACCESSIBILITY ============ -->
          <template v-if="activeCategory === 'accessibility'">
            <!-- WCAG banner -->
            <div class="content-card wcag-banner">
              <div class="wcag-ic"><ic-accessibility :size="18" color="#2fe39a" /></div>
              <div style="flex:1">
                <div style="font-weight:700">WCAG 2.2 AA Compliant</div>
                <div style="font-size:13px;color:var(--text-muted)">SkillSprint meets international accessibility standards. These settings let you go further.</div>
              </div>
              <span class="material-status status-done"><ic-check :size="12" /> Active</span>
            </div>

            <!-- Dyslexia-Friendly Mode -->
            <div class="content-card a11y-section">
              <div class="a11y-head">
                <span class="a11y-head-ic" style="background:rgba(124,92,252,0.15)">A</span>
                <div><div class="a11y-head-title">Dyslexia-Friendly Mode</div><div class="a11y-head-sub">Optimize text for easier reading</div></div>
              </div>

              <div class="a11y-row">
                <div><div class="row-label">Enable Dyslexia Font</div><div class="row-hint">Switch to OpenDyslexic-style font across all content for improved readability.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.dyslexia_font"><span class="slider"></span></label>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Increased Letter Spacing</div><div class="row-hint">Add extra space between letters to reduce visual crowding.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="letterSpacingOn"><span class="slider"></span></label>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Increased Line Height</div><div class="row-hint">More vertical space between lines for easier tracking.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="lineHeightOn"><span class="slider"></span></label>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Word Spacing Boost</div><div class="row-hint">Increase spacing between words to improve word recognition.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="wordSpacingOn"><span class="slider"></span></label>
              </div>

              <div class="a11y-label" style="margin-top:18px">Text Preview</div>
              <div class="settings-preview" :style="previewStyle">
                <p>The quick brown fox jumps over the lazy dog. Learning new skills is easier when text is clear and comfortable to read.</p>
              </div>
              <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
                <span v-if="prefs.dyslexia_font" class="a11y-chip on"><ic-check :size="12" /> Dyslexia Font Active</span>
                <span v-if="letterSpacingOn" class="a11y-chip">+0.05em Spacing</span>
                <span v-if="wordSpacingOn" class="a11y-chip">+0.1em Words</span>
              </div>
            </div>

            <!-- High Contrast Mode -->
            <div class="content-card a11y-section">
              <div class="a11y-head">
                <span class="a11y-head-ic" style="background:rgba(245,166,35,0.15)">◐</span>
                <div><div class="a11y-head-title">High Contrast Mode</div><div class="a11y-head-sub">Enhance visual distinction between elements</div></div>
              </div>

              <div class="a11y-row">
                <div><div class="row-label">Enable High Contrast</div><div class="row-hint">Boost contrast ratios above WCAG AAA levels (7:1+) for maximum visibility.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.high_contrast"><span class="slider"></span></label>
              </div>

              <div class="a11y-label" style="margin-top:16px">Contrast Theme</div>
              <div class="theme-grid">
                <button v-for="t in themes" :key="t.key" type="button"
                        class="theme-swatch" :class="{ active: prefs.contrast_theme === t.key }"
                        @click="prefs.contrast_theme = t.key">
                  <span class="theme-dot" :style="{ background: t.dotBg, border: t.dotBorder, color: t.dotFg }">●</span>
                  <div class="theme-name">{{ t.name }}</div>
                  <div class="theme-desc">{{ t.desc }}</div>
                </button>
              </div>

              <div class="a11y-row" style="margin-top:16px">
                <div><div class="row-label">Bold Text Everywhere</div><div class="row-hint">Make all body text bold for improved visibility.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.bold_text"><span class="slider"></span></label>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Focus Indicators</div><div class="row-hint">Show enhanced visible focus rings on all interactive elements.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.focus_indicators"><span class="slider"></span></label>
              </div>
            </div>

            <!-- Font Size & Readability -->
            <div class="content-card a11y-section">
              <div class="a11y-head">
                <span class="a11y-head-ic" style="background:rgba(56,217,138,0.15)">Tt</span>
                <div><div class="a11y-head-title">Font Size & Readability</div><div class="a11y-head-sub">Adjust text size to your comfort</div></div>
              </div>

              <div style="display:flex;align-items:center;justify-content:space-between">
                <div class="a11y-label">Base Font Size</div>
                <div style="font-size:14px;font-weight:700;color:var(--purple-bright)">{{ prefs.font_size }} <span style="font-size:11px;color:var(--text-muted)">px</span></div>
              </div>
              <div style="display:flex;align-items:center;gap:12px;margin:8px 0">
                <span style="font-size:13px;color:var(--text-muted)">A</span>
                <input type="range" min="12" max="24" v-model.number="prefs.font_size" class="range-slider" style="flex:1">
                <span style="font-size:20px;color:var(--text-muted)">A</span>
              </div>
              <div class="size-presets">
                <button v-for="p in sizePresets" :key="p.px" type="button"
                        class="size-preset" :class="{ active: prefs.font_size === p.px }"
                        @click="prefs.font_size = p.px">{{ p.label }}</button>
              </div>

              <div class="a11y-row" style="margin-top:18px">
                <div><div class="row-label">Wider Reading Column</div><div class="row-hint">Limit content width to ~70 characters for optimal reading comfort.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.wider_reading_column"><span class="slider"></span></label>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Highlight Active Line</div><div class="row-hint">Subtly highlight the paragraph being read to aid focus tracking.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.highlight_active_line"><span class="slider"></span></label>
              </div>

              <div class="a11y-label" style="margin-top:18px">Font Family</div>
              <div class="font-grid">
                <button v-for="f in fonts" :key="f.key" type="button"
                        class="font-chip" :class="{ active: prefs.font_family === f.key }"
                        :style="{ fontFamily: f.stack }"
                        @click="prefs.font_family = f.key">{{ f.label }}</button>
              </div>
            </div>
          </template>

          <!-- ============ APPEARANCE (motion) ============ -->
          <template v-else-if="activeCategory === 'appearance'">
            <div class="content-card a11y-section">
              <div class="a11y-head"><span class="a11y-head-ic" style="background:rgba(213,107,255,0.15)"><ic-sparkles :size="16" color="#e06bff" /></span>
                <div><div class="a11y-head-title">Motion & Animation</div><div class="a11y-head-sub">Reduce movement that can cause distraction</div></div></div>
              <div class="a11y-row">
                <div><div class="row-label">Reduce Motion</div><div class="row-hint">Minimize animations and transitions across the app.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.reduce_motion"><span class="slider"></span></label>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Slow Flashcard Flip</div><div class="row-hint">Use a slower flip animation for flashcards.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.slow_flip_speed"><span class="slider"></span></label>
              </div>
            </div>
          </template>

          <!-- ============ NOTIFICATIONS ============ -->
          <template v-else-if="activeCategory === 'notifications'">
            <div class="content-card a11y-section">
              <div class="a11y-head"><span class="a11y-head-ic" style="background:rgba(245,166,35,0.15)"><ic-bell :size="16" color="#ffb13d" /></span>
                <div><div class="a11y-head-title">Notifications</div><div class="a11y-head-sub">Stay on track with reminders</div></div></div>
              <div class="a11y-row">
                <div><div class="row-label">Enable Notifications</div><div class="row-hint">Receive streak reminders and learning nudges.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.notifications"><span class="slider"></span></label>
              </div>
            </div>
          </template>

          <!-- ============ LEARNING ============ -->
          <template v-else-if="activeCategory === 'learning'">
            <div class="content-card a11y-section">
              <div class="a11y-head"><span class="a11y-head-ic" style="background:rgba(56,217,138,0.15)"><ic-book :size="16" color="#2fe39a" /></span>
                <div><div class="a11y-head-title">Learning Preferences</div><div class="a11y-head-sub">Tune how lessons are generated</div></div></div>
              <div class="a11y-row">
                <div><div class="row-label">Learning Pace</div></div>
                <select v-model="prefs.learning_pace" class="form-select setting-select"><option>casual</option><option>regular</option><option>intensive</option></select>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Default Difficulty</div></div>
                <select v-model="prefs.difficulty_default" class="form-select setting-select"><option>beginner</option><option>intermediate</option><option>advanced</option></select>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Output Language</div></div>
                <select v-model="prefs.output_language" class="form-select setting-select"><option>English</option><option>Filipino</option><option>Spanish</option></select>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Simplify Language</div><div class="row-hint">Ask the AI to use simpler wording in generated content.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.simplify_language"><span class="slider"></span></label>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Visual Diagrams</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.visual_diagrams"><span class="slider"></span></label>
              </div>
            </div>

            <div class="content-card a11y-section">
              <div class="a11y-head"><span class="a11y-head-ic" style="background:rgba(124,92,252,0.15)"><ic-volume :size="16" color="#9d7bff" /></span>
                <div><div class="a11y-head-title">Audio Narration</div><div class="a11y-head-sub">Read content aloud</div></div></div>
              <div class="a11y-row">
                <div><div class="row-label">Text-to-Speech</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.tts_enabled"><span class="slider"></span></label>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Auto-Read Flashcards</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.auto_read_cards"><span class="slider"></span></label>
              </div>
              <div class="a11y-row">
                <div><div class="row-label">Auto-Read Quiz Questions</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.auto_read_questions"><span class="slider"></span></label>
              </div>
            </div>
          </template>

          <!-- ============ ACCOUNT / PRIVACY ============ -->
          <template v-else-if="activeCategory === 'account'">
            <div class="content-card a11y-section">
              <div class="a11y-head"><span class="a11y-head-ic" style="background:rgba(124,92,252,0.15)"><ic-user :size="16" color="#9d7bff" /></span>
                <div><div class="a11y-head-title">Account</div><div class="a11y-head-sub">Profile management coming soon</div></div></div>
              <p style="font-size:13px;color:var(--text-muted);margin:0">Profile editing, password, and avatar settings will live here.</p>
            </div>
          </template>

          <template v-else-if="activeCategory === 'privacy'">
            <div class="content-card a11y-section">
              <div class="a11y-head"><span class="a11y-head-ic" style="background:rgba(56,217,138,0.15)"><ic-shield :size="16" color="#2fe39a" /></span>
                <div><div class="a11y-head-title">Privacy</div><div class="a11y-head-sub">Control your data</div></div></div>
              <div class="a11y-row">
                <div><div class="row-label">Offline Mode</div><div class="row-hint">Keep generated content available without a connection.</div></div>
                <label class="toggle-switch"><input type="checkbox" v-model="prefs.offline_mode"><span class="slider"></span></label>
              </div>
            </div>
          </template>
        </div>
      </div>
    </template>
  </app-layout>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { applyPreferences } from '../applyPreferences';

export default {
  name: 'SettingsPage',
  setup() {
    const prefs = ref(null);
    const loading = ref(true);
    const error = ref(null);
    const activeCategory = ref('accessibility');
    let saveTimer = null;

    const categories = [
      { key: 'accessibility', label: 'Accessibility', icon: 'ic-accessibility' },
      { key: 'appearance',    label: 'Appearance',    icon: 'ic-palette' },
      { key: 'notifications', label: 'Notifications', icon: 'ic-bell' },
      { key: 'learning',      label: 'Learning',      icon: 'ic-book' },
      { key: 'account',       label: 'Account',       icon: 'ic-user' },
      { key: 'privacy',       label: 'Privacy',       icon: 'ic-shield' },
    ];

    const themes = [
      { key: 'default',    name: 'Default',    desc: 'Dark mode',    dotBg: 'var(--purple)', dotBorder: 'none', dotFg: '#fff' },
      { key: 'high_dark',  name: 'High Dark',  desc: 'Black & white', dotBg: '#000', dotBorder: '2px solid #fff', dotFg: '#fff' },
      { key: 'high_light', name: 'High Light', desc: 'White & black', dotBg: '#fff', dotBorder: '2px solid #000', dotFg: '#000' },
      { key: 'yellow',     name: 'Yellow',     desc: 'On black',      dotBg: '#000', dotBorder: '2px solid #ffff00', dotFg: '#ffff00' },
    ];

    const sizePresets = [
      { label: 'Small', px: 14 }, { label: 'Medium', px: 16 }, { label: 'Large', px: 18 }, { label: 'XL', px: 20 },
    ];

    const fonts = [
      { key: 'jakarta',   label: 'Jakarta Sans', stack: "'Plus Jakarta Sans', sans-serif" },
      { key: 'inter',     label: 'Inter',        stack: "'Inter', sans-serif" },
      { key: 'georgia',   label: 'Georgia Serif', stack: "Georgia, serif" },
      { key: 'monospace', label: 'Monospace',    stack: "monospace" },
    ];

    // Toggle-backed numeric prefs
    const letterSpacingOn = computed({
      get: () => Number(prefs.value?.letter_spacing) > 0,
      set: (v) => { prefs.value.letter_spacing = v ? 1 : 0; },
    });
    const wordSpacingOn = computed({
      get: () => Number(prefs.value?.word_spacing) > 0,
      set: (v) => { prefs.value.word_spacing = v ? 1 : 0; },
    });
    const lineHeightOn = computed({
      get: () => Number(prefs.value?.line_height) >= 1.8,
      set: (v) => { prefs.value.line_height = v ? 1.8 : 1.6; },
    });

    const previewStyle = computed(() => ({
      fontFamily: prefs.value?.dyslexia_font
        ? "'OpenDyslexic', sans-serif"
        : (fonts.find(f => f.key === prefs.value?.font_family)?.stack || 'inherit'),
      fontSize: `${prefs.value?.font_size ?? 16}px`,
      letterSpacing: letterSpacingOn.value ? '0.05em' : 'normal',
      wordSpacing: wordSpacingOn.value ? '0.1em' : 'normal',
      lineHeight: prefs.value?.line_height ?? 1.6,
      fontWeight: prefs.value?.bold_text ? 700 : 400,
    }));

    onMounted(async () => {
      try {
        const { data } = await axios.get('/api/user/preferences');
        prefs.value = reactive(data);
        applyPreferences(prefs.value);
      } catch (e) {
        error.value = 'Could not load your settings. Please try again.';
      } finally {
        loading.value = false;
      }
    });

    // Apply live + debounce-save on any change
    watch(prefs, (val) => {
      if (!val) return;
      applyPreferences(val);
      clearTimeout(saveTimer);
      saveTimer = setTimeout(() => axios.put('/api/user/preferences', val).catch(() => {}), 500);
    }, { deep: true });

    function saveAll() {
      if (prefs.value) axios.put('/api/user/preferences', prefs.value).catch(() => {});
    }

    function resetDefaults() {
      if (!prefs.value) return;
      Object.assign(prefs.value, {
        dyslexia_font: false, letter_spacing: 0, line_height: 1.6, word_spacing: 0, font_size: 16,
        high_contrast: false, contrast_theme: 'default', bold_text: false, focus_indicators: true,
        wider_reading_column: false, highlight_active_line: false, font_family: 'inter',
      });
    }

    return {
      prefs, loading, error, activeCategory, categories, themes, sizePresets, fonts,
      letterSpacingOn, wordSpacingOn, lineHeightOn, previewStyle, saveAll, resetDefaults,
    };
  },
};
</script>
