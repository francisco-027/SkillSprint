<template>
  <div class="quickstart-wrap">
    <!-- Left Column -->
    <div class="quickstart-left">
      <div class="step-badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px;margin-right:6px"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        Fast Track
      </div>
      <h2>Jumpstart Your Learning</h2>

      <div style="display:flex;align-items:center;gap:10px;margin-bottom:28px;padding:12px 16px;border:1px solid rgba(56,217,138,0.25);border-radius:12px;background:rgba(56,217,138,0.06)">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--green);flex-shrink:0"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span style="font-size:14px;font-weight:600;color:var(--green)">Takes ~3 min</span>
      </div>

      <div style="margin-bottom:28px">
        <h4 style="font-size:15px;font-weight:700;margin-bottom:14px;color:var(--text)">What You'll Get</h4>
        <div style="display:flex;flex-direction:column;gap:10px">
          <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid var(--card-border);border-radius:11px;background:rgba(255,255,255,0.02);font-size:14px;color:var(--text)">
            <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg, #6d4dfc, #9b6dff);display:flex;align-items:center;justify-content:center;flex-shrink:0"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></span>
            Personalized AI Summary
          </div>
          <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid var(--card-border);border-radius:11px;background:rgba(255,255,255,0.02);font-size:14px;color:var(--text)">
            <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg, #c14dff, #ff6dc1);display:flex;align-items:center;justify-content:center;flex-shrink:0"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><rect x="2" y="2" width="8" height="9" rx="1.5"/><rect x="14" y="2" width="8" height="5" rx="1.5"/><rect x="14" y="11" width="8" height="11" rx="1.5"/><rect x="2" y="15" width="8" height="7" rx="1.5"/></svg></span>
            Interactive Flashcards
          </div>
          <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid var(--card-border);border-radius:11px;background:rgba(255,255,255,0.02);font-size:14px;color:var(--text)">
            <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg, #1f9d6b, #38d98a);display:flex;align-items:center;justify-content:center;flex-shrink:0"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
            Adaptive Quiz
          </div>
        </div>
      </div>

      <div style="padding:14px 16px;border:1px solid var(--card-border);border-radius:12px;background:var(--card-bg);display:flex;align-items:flex-start;gap:12px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--purple-bright);flex-shrink:0;margin-top:1px"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:3px">Safe &amp; Private</div>
          <div style="font-size:12px;color:var(--text-muted);line-height:1.5">Your content is encrypted and never shared. We process files locally before generating your learning materials.</div>
        </div>
      </div>
    </div>

    <!-- Right Column -->
    <div class="quickstart-right">
      <div style="width:100%;max-width:520px">
        <div class="onboarding-card" style="padding:28px">
          <h3 style="font-size:18px;font-weight:700;margin-bottom:20px">Quick Start Learning Path</h3>

          <!-- Tabs -->
          <div class="quickstart-tabs">
            <button
              class="upload-tab-btn"
              :class="{ active: activeTab === 'upload' }"
              @click="activeTab = 'upload'"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px;margin-right:5px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Upload File
            </button>
            <button
              class="upload-tab-btn"
              :class="{ active: activeTab === 'sample' }"
              @click="activeTab = 'sample'"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px;margin-right:5px"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              Sample Topics
            </button>
            <button
              class="upload-tab-btn"
              :class="{ active: activeTab === 'paste' }"
              @click="activeTab = 'paste'"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px;margin-right:5px"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
              Paste Text
            </button>
          </div>

          <!-- Upload File Tab -->
          <div v-if="activeTab === 'upload'">
            <div
              class="upload-drop-zone"
              :class="{ 'drag-over': dragOver }"
              @dragover.prevent="dragOver = true"
              @dragleave="dragOver = false"
              @drop.prevent="onDrop"
              @click="document.getElementById('quickstartFileInput').click()"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <div class="drop-text">
                <span v-if="!selectedFile">Drag &amp; drop your file or <strong>browse</strong></span>
                <span v-else style="font-weight:600;color:var(--text)">{{ selectedFile.name }}</span>
              </div>
              <div style="font-size:12px;color:var(--text-dim);margin-top:6px">PDF, DOCX, TXT — Max 10MB</div>
            </div>
            <input id="quickstartFileInput" type="file" accept=".pdf,.docx,.txt" style="display:none" @change="onFileSelect">
          </div>

          <!-- Sample Topics Tab -->
          <div v-if="activeTab === 'sample'">
            <div v-if="loadingSample" style="text-align:center;padding:30px">
              <div class="skel" style="width:60%;margin:0 auto 8px;height:16px"></div>
              <div class="skel" style="width:40%;margin:0 auto;height:14px"></div>
            </div>
            <div v-else style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px">
              <div class="sample-topic-card" v-for="topic in sampleTopics" :key="topic.id" @click="selectSample(topic)">
                <div class="topic-icon"><ic-book :size="20" /></div>
                <div class="topic-name">{{ topic.title }}</div>
                <div class="topic-meta">{{ topic.level }} · {{ topic.estimated_minutes }} min</div>
              </div>
            </div>
          </div>

          <!-- Paste Text Tab -->
          <div v-if="activeTab === 'paste'">
            <textarea
              v-model="pastedText"
              style="width:100%;background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:12px;padding:14px;min-height:200px;resize:vertical;font-size:14px;line-height:1.6;outline:none"
              placeholder="Paste your notes, article text, or any content you want to learn from..."
            ></textarea>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px">
              <span style="font-size:13px;color:var(--text-muted)">{{ pastedText.length }} characters</span>
            </div>
          </div>

          <!-- Generate Button -->
          <div style="text-align:center;margin-top:24px">
            <button class="btn-grad lg" :disabled="generating" @click="generate" style="width:100%">
              <svg v-if="generating" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
              {{ generating ? 'Generating...' : 'Generate' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'QuickStart',
  setup() {
    const activeTab = ref('upload');
    const pastedText = ref('');
    const dragOver = ref(false);
    const selectedFile = ref(null);
    const generating = ref(false);
    const sampleTopics = ref([]);
    const loadingSample = ref(true);

    onMounted(async () => {
      try {
        const { data } = await axios.get('/api/skills?is_featured=1');
        sampleTopics.value = data;
      } catch (e) {
        console.error('Failed to load sample topics', e);
      } finally {
        loadingSample.value = false;
      }
    });

    function onDrop(e) {
      dragOver.value = false;
      if (e.dataTransfer.files.length) {
        selectedFile.value = e.dataTransfer.files[0];
      }
    }

    function onFileSelect(e) {
      if (e.target.files.length) {
        selectedFile.value = e.target.files[0];
      }
    }

    function selectSample(topic) {
      pastedText.value = '';
      selectedFile.value = null;
      activeTab.value = 'sample';
    }

    async function generate() {
      if (generating.value) return;

      generating.value = true;
      try {
        let payload = {};

        if (activeTab.value === 'paste' && pastedText.value.trim()) {
          payload.text = pastedText.value.trim();
        } else if (activeTab.value === 'upload' && selectedFile.value) {
          const formData = new FormData();
          formData.append('file', selectedFile.value);
          const { data } = await axios.post('/api/uploads', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          });
          window.location.href = `/summaries/${data.id}`;
          return;
        } else if (activeTab.value === 'sample' && sampleTopics.value.length > 0) {
          payload.skill_id = sampleTopics.value[0].id;
        } else {
          generating.value = false;
          return;
        }

        const { data } = await axios.post('/api/uploads', payload);
        window.location.href = `/summaries/${data.id}`;
      } catch (e) {
        console.error('Generate failed', e);
        generating.value = false;
      }
    }

    return {
      activeTab,
      pastedText,
      dragOver,
      selectedFile,
      generating,
      sampleTopics,
      loadingSample,
      onDrop,
      onFileSelect,
      selectSample,
      generate,
    };
  },
};
</script>

<style>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>