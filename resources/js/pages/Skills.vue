<template>
  <div class="skills-page route-view">
    <div class="row g-4">
      <!-- Left Panel -->
      <div class="col-lg-3">
        <div style="position:sticky;top:30px;display:flex;flex-direction:column;gap:16px">
          <div class="content-card">
            <div style="display:flex;align-items:center;gap:10px">
              <div class="sel-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
              </div>
              <div>
                <div style="font-size:15px;font-weight:700">Skill Library</div>
                <div style="font-size:12px;color:var(--text-muted)">Learn from the community</div>
              </div>
            </div>
            <p style="font-size:12px;color:var(--text-muted);margin:12px 0 0;line-height:1.6">
              Browse materials shared by other learners. Open one to preview it, or save it to study later.
            </p>
          </div>

          <div class="content-card">
            <h3 style="font-size:13px;letter-spacing:0.04em;color:var(--text-muted);text-transform:uppercase;margin:0 0 10px">Quick Tips</h3>
            <div class="tip-row" v-for="tip in quickTips" :key="tip">
              <span class="tip-check"><ic-check :size="12" /></span><span>{{ tip }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-lg-9">
        <router-link to="/home" class="back-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Back to Dashboard
        </router-link>
        <span class="badge-pill"><ic-zap :size="14" /> Explore Skills</span>
        <h1 class="skills-hero">What Do You Want to <span class="grad-text">Learn?</span></h1>
        <p style="color:var(--text-muted);max-width:560px;margin-bottom:22px">
          Browse materials shared by the community. Each one is broken into bite-sized, AI-powered lessons.
        </p>

        <!-- Search & Filters -->
        <div class="skills-search-row">
          <div class="search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="search" placeholder="Search materials, topics, or keywords...">
          </div>
          <select v-model="activeLevel" class="form-select level-select">
            <option>All Levels</option>
            <option>Beginner</option>
            <option>Intermediate</option>
            <option>Advanced</option>
          </select>
        </div>

        <!-- Category Tabs -->
        <div class="cat-tabs">
          <button v-for="cat in categoryCounts" :key="cat.label"
                  class="cat-tab" :class="{ active: activeCategory === cat.label }"
                  @click="activeCategory = cat.label">
            {{ cat.label }}
            <span class="cat-count">{{ cat.count }}</span>
          </button>
        </div>

        <!-- Toolbar -->
        <div class="skills-toolbar">
          <span style="font-size:13px;color:var(--text-muted)">Showing {{ filteredMaterials.length }} materials</span>
          <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:13px;color:var(--text-muted)">Sort by</span>
            <select v-model="sortBy" class="form-select level-select" style="max-width:150px">
              <option>Most Popular</option>
              <option>Newest</option>
            </select>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="row g-3">
          <div class="col-md-4" v-for="n in 6" :key="n">
            <div class="content-card"><div class="skel" style="width:70%;height:16px"></div><div class="skel" style="width:40%;height:12px;margin-top:8px"></div></div>
          </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

        <!-- Material Grid -->
        <div v-if="!loading && !error" class="row g-3">
          <div class="col-md-4" v-for="m in filteredMaterials" :key="m.upload_id">
            <div class="skill-card" @click="openMaterial(m)">
              <div class="skill-card-top">
                <div class="skill-icon" :style="{ background: categoryStyle(m.category).bg }">
                  <component :is="categoryStyle(m.category).icon" :size="18" :color="categoryStyle(m.category).fg" />
                </div>
                <button v-if="!m.is_owner" type="button" class="save-btn" :class="{ saved: m.is_saved }" @click.stop="toggleSave(m)">
                  <ic-bookmark :size="13" /> {{ m.is_saved ? 'Saved' : 'Save' }}
                </button>
                <span v-else class="owner-tag">Your material</span>
              </div>
              <div class="skill-title">{{ m.title }}</div>
              <div class="skill-meta">
                <span v-if="m.difficulty" class="skill-level-badge" :class="'lvl-' + m.difficulty.toLowerCase()">{{ m.difficulty }}</span>
                <span style="display:inline-flex;align-items:center;gap:4px">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                  {{ m.minutes }} min
                </span>
              </div>
              <span v-if="m.category" class="material-cat" style="margin-top:10px">{{ m.category }}</span>
              <div class="skill-owner-row">
                <span class="owner-avatar">{{ (m.owner || '?').charAt(0) }}</span>
                by {{ m.owner }}
              </div>
              <div class="skill-learners-row">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                {{ formatNumber(m.learner_count) }} {{ m.learner_count === 1 ? 'learner' : 'learners' }}
              </div>
            </div>
          </div>
          <div v-if="filteredMaterials.length === 0" class="col-12 text-center py-5">
            <div class="empty-mid">
              <div class="circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              </div>
              <h4>No materials found</h4>
              <p>Public materials shared by other learners will appear here.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'SkillsPage',
  setup() {
    const router = useRouter();
    const materials = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const search = ref('');
    const activeCategory = ref('All');
    const activeLevel = ref('All Levels');
    const sortBy = ref('Most Popular');

    const quickTips = [
      'Open a material to preview its lesson',
      'Save materials to study them later',
      'You count as a learner once you study a saved material',
    ];

    const categoryStyles = {
      Technology: { bg: 'rgba(124,92,252,0.15)', fg: '#8b6dff', icon: 'ic-cpu' },
      Science:    { bg: 'rgba(56,217,138,0.15)',  fg: '#38d98a', icon: 'ic-radar' },
      Humanities: { bg: 'rgba(245,166,35,0.15)',  fg: '#f5a623', icon: 'ic-book' },
      Business:   { bg: 'rgba(213,107,255,0.15)', fg: '#d56bff', icon: 'ic-bar-chart' },
      Health:     { bg: 'rgba(255,107,107,0.15)', fg: '#ff6b6b', icon: 'ic-accessibility' },
    };
    function categoryStyle(cat) {
      return categoryStyles[cat] || { bg: 'rgba(124,92,252,0.12)', fg: '#8b6dff', icon: 'ic-book' };
    }

    onMounted(async () => {
      try {
        const { data } = await axios.get('/api/library');
        materials.value = data;
      } catch (e) {
        error.value = 'Could not load the library. Please try again.';
      } finally {
        loading.value = false;
      }
    });

    const filteredMaterials = computed(() => {
      let list = materials.value
        .filter(m => !search.value || (m.title || '').toLowerCase().includes(search.value.toLowerCase()))
        .filter(m => activeCategory.value === 'All' || m.category === activeCategory.value)
        .filter(m => activeLevel.value === 'All Levels' || (m.difficulty || '').toLowerCase() === activeLevel.value.toLowerCase());

      if (sortBy.value === 'Most Popular') {
        list = [...list].sort((a, b) => (b.learner_count || 0) - (a.learner_count || 0));
      }
      return list;
    });

    const categoryCounts = computed(() => {
      const cats = [...new Set(materials.value.map(m => m.category).filter(Boolean))].sort();
      return [
        { label: 'All', count: materials.value.length },
        ...cats.map(c => ({ label: c, count: materials.value.filter(m => m.category === c).length })),
      ];
    });

    function openMaterial(m) {
      if (m.summary_id) router.push(`/materials/${m.summary_id}`);
    }

    async function toggleSave(m) {
      const was = m.is_saved;
      m.is_saved = !was; // optimistic
      try {
        if (was) await axios.delete(`/api/library/${m.upload_id}/save`);
        else await axios.post(`/api/library/${m.upload_id}/save`);
      } catch (e) {
        m.is_saved = was; // revert on failure
      }
    }

    function formatNumber(n) {
      n = n || 0;
      return n >= 1000 ? (n / 1000).toFixed(1) + 'k' : n.toString();
    }

    return {
      materials, loading, error, search, activeCategory, activeLevel, sortBy,
      quickTips, filteredMaterials, categoryCounts, categoryStyle,
      openMaterial, toggleSave, formatNumber,
    };
  },
};
</script>
