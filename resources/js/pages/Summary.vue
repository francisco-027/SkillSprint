<template>
  <div class="route-view">
    <div v-if="loading" class="text-center py-5">
      <div class="skel" style="width:60%;max-width:400px;margin:0 auto 12px;height:24px"></div>
      <div class="skel" style="width:80%;max-width:500px;margin:0 auto 20px;height:16px"></div>
      <div class="skel" style="width:100%;height:120px;margin-bottom:12px"></div>
      <div class="skel" style="width:100%;height:120px;margin-bottom:12px"></div>
      <div class="skel" style="width:100%;height:120px;margin-bottom:12px"></div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

    <template v-if="summary">
      <div class="page-header">
        <p><span style="color:var(--purple-bright);font-weight:600">{{ summary.difficulty }}</span> · {{ summary.estimated_minutes }} min read · {{ summary.source_filename }}</p>
        <h1>{{ summary.title }}</h1>
      </div>

      <!-- Accessibility Bar -->
      <div class="content-card" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:14px 20px">
        <span style="font-size:13px;font-weight:600;color:var(--text-muted)">Accessibility:</span>
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
          <input type="checkbox" v-model="a11yOptions.tts" class="form-check-input" style="margin:0"> TTS
        </label>
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
          <input type="checkbox" v-model="a11yOptions.simplify" class="form-check-input" style="margin:0"> Simplify
        </label>
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
          <input type="checkbox" v-model="a11yOptions.dyslexia" class="form-check-input" style="margin:0"> Dyslexia Font
        </label>
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
          <input type="checkbox" v-model="a11yOptions.high_contrast" class="form-check-input" style="margin:0"> High Contrast
        </label>
      </div>

      <!-- Tab Nav -->
      <div style="display:flex;gap:8px;margin:20px 0">
        <button class="upload-tab-btn" :class="{ active: activeTab === 'summary' }" @click="activeTab = 'summary'">Summary</button>
        <button class="upload-tab-btn" :class="{ active: activeTab === 'timeline' }" @click="activeTab = 'timeline'">Timeline</button>
      </div>

      <!-- Summary View -->
      <div v-if="activeTab === 'summary'">
        <div v-for="section in (summary.content_sections || [])" :key="section.number" class="content-card glow-card">
          <div style="display:flex;align-items:flex-start;gap:14px">
            <span style="font-size:24px;font-weight:800;color:var(--purple-bright);flex-shrink:0;line-height:1">{{ section.number }}</span>
            <div style="flex:1">
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                <h3 style="margin:0;font-size:17px">{{ section.title }}</h3>
                <span class="card-tag" style="margin:0">{{ section.tag }}</span>
              </div>
              <p>{{ section.body }}</p>
              <div v-if="section.subtypes" style="margin-top:12px">
                <div v-for="sub in section.subtypes" :key="sub.label"
                     style="padding:10px 14px;border:1px solid var(--card-border);border-radius:10px;margin-bottom:8px;background:rgba(255,255,255,0.02)">
                  <span style="font-weight:700;font-size:14px">{{ sub.label }}</span>
                  <span style="color:var(--text-muted);font-size:13px;margin-left:8px">{{ sub.desc }}</span>
                </div>
              </div>
              <div v-if="section.tags" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px">
                <span v-for="tag in section.tags" :key="tag" class="skill-tag">{{ tag }}</span>
              </div>
              <div v-if="section.analogy" style="margin-top:12px;padding:10px 14px;border-radius:10px;background:rgba(124,92,252,0.06);border:1px solid rgba(124,92,252,0.15)">
                <span style="font-size:12px;font-weight:600;color:var(--purple-bright)">Analogy</span>
                <p style="margin:4px 0 0;font-size:14px">{{ section.analogy }}</p>
              </div>
              <div style="font-size:12px;color:var(--text-dim);margin-top:8px">{{ section.read_minutes }} min read</div>
            </div>
          </div>
        </div>

        <!-- Key Terms -->
        <div class="content-card glow-card">
          <h3>Key Terms</h3>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
            <span v-for="term in (summary.key_terms || [])" :key="term"
                  style="padding:6px 13px;border-radius:999px;font-size:13px;font-weight:500;background:rgba(124,92,252,0.1);color:var(--purple-bright)">{{ term }}</span>
          </div>
        </div>
      </div>

      <!-- Timeline View -->
      <div v-if="activeTab === 'timeline'">
        <div v-for="step in (summary.timeline_steps || [])" :key="step.step" class="content-card glow-card"
             style="display:flex;align-items:flex-start;gap:16px">
          <div style="width:36px;height:36px;border-radius:50%;background:var(--grad);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0">
            {{ step.step }}
          </div>
          <div>
            <h3 style="font-size:16px;margin:0">{{ step.title }}</h3>
            <p style="font-size:14px;color:var(--text-muted);margin:4px 0 0">{{ step.desc }}</p>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:12px;margin-top:24px;justify-content:center;flex-wrap:wrap">
        <button class="btn-grad" @click="goFlashcards">
          Study Flashcards
        </button>
        <button class="btn-ghost" @click="goQuiz">
          Take Quiz
        </button>
      </div>
    </template>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'SummaryPage',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const summaryId = route.params.id;
    const summary = ref(null);
    const loading = ref(true);
    const error = ref(null);
    const activeTab = ref('summary');
    const a11yOptions = reactive({ tts: true, simplify: true, dyslexia: false, high_contrast: false });

    function goFlashcards() { router.push(`/flashcards/${summaryId}`); }
    function goQuiz() { router.push(`/materials/${summaryId}`); }

    onMounted(async () => {
      try {
        const { data } = await axios.get(`/api/summaries/${summaryId}`);
        summary.value = data;
      } catch (e) {
        error.value = 'Could not load content. Please try again.';
      } finally {
        loading.value = false;
      }
    });

    return { summary, loading, error, activeTab, a11yOptions, goFlashcards, goQuiz };
  },
};
</script>