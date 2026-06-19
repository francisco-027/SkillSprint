<template>
  <app-layout active-page="upload" page-title="My Materials">
    <!-- Header -->
    <div class="page-head-row">
      <div class="page-header" style="margin-bottom:0">
        <h1>My Materials</h1>
        <p>Your uploaded materials and the lessons AI generated from them.</p>
      </div>
      <button class="btn-grad" @click="openModal">+ Add Material</button>
    </div>

    <!-- Loading -->
    <div v-if="loadingMaterials" class="content-card mt-3">
      <div class="skel" style="width:50%;height:16px"></div>
      <div class="skel" style="width:30%;height:12px;margin-top:10px"></div>
    </div>

    <template v-else>
      <!-- Tabs -->
      <div class="material-tabs mt-3">
        <button type="button" class="material-tab" :class="{ active: activeListTab === 'owned' }" @click="activeListTab = 'owned'">
          Owned Materials <span class="material-tab-count">{{ successfulMaterials.length }}</span>
        </button>
        <button type="button" class="material-tab" :class="{ active: activeListTab === 'saved' }" @click="activeListTab = 'saved'">
          Saved Materials <span class="material-tab-count">{{ savedMaterials.length }}</span>
        </button>
      </div>

      <!-- Owned Materials -->
      <div v-if="activeListTab === 'owned'" class="content-card mt-2">
        <div v-if="successfulMaterials.length === 0" style="text-align:center;padding:30px 20px">
          <div style="margin-bottom:8px"><ic-book :size="30" color="#9d7bff" /></div>
          <p style="font-size:13px;color:var(--text-muted);margin:0">
            No materials yet. Click <strong>Add Material</strong> to generate your first lesson.
          </p>
        </div>
        <div v-for="m in successfulMaterials" :key="m.id"
             class="material-row material-row-clickable"
             @click="openMaterial(m)">
          <div class="material-ic"><component :is="typeIcon(m.type)" :size="18" /></div>
          <div class="material-main">
            <div class="material-name">{{ m.title || m.original_filename }}</div>
            <div class="material-sub">
              <span v-if="m.category" class="material-cat">{{ m.category }}</span>
              <span class="material-vis"><component :is="m.is_public ? 'ic-globe' : 'ic-lock'" :size="11" /> {{ m.is_public ? 'Public' : 'Private' }}</span>
              <span v-if="m.word_count"> · {{ m.word_count }} words</span>
              · {{ new Date(m.created_at).toLocaleDateString() }}
            </div>
          </div>
          <span v-if="m.quiz_taken" class="material-status status-done">Done</span>
          <span v-else-if="m.is_new" class="material-status status-new">New</span>
          <span v-else class="material-status status-progress">In Progress</span>

          <!-- 3-dot menu -->
          <div class="material-menu-wrap" @click.stop>
            <button type="button" class="material-menu-btn" @click="toggleMenu('o' + m.id)" aria-label="Material options">⋮</button>
            <div v-if="openMenuId === 'o' + m.id" class="material-menu">
              <button type="button" class="material-menu-item" @click="startEdit(m)">Edit</button>
              <button type="button" class="material-menu-item danger" @click="deleteMaterial(m)">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Saved Materials -->
      <div v-if="activeListTab === 'saved'" class="content-card mt-2">
        <div v-if="savedMaterials.length === 0" style="text-align:center;padding:30px 20px">
          <div style="margin-bottom:8px"><ic-bookmark :size="30" color="#9d7bff" /></div>
          <p style="font-size:13px;color:var(--text-muted);margin:0">
            No saved materials yet. Browse the <a href="/skills" style="color:var(--purple-bright)">Skills Library</a> to save materials from others.
          </p>
        </div>
        <div v-for="m in savedMaterials" :key="m.id"
             class="material-row material-row-clickable"
             @click="openMaterial(m)">
          <div class="material-ic"><ic-book :size="18" /></div>
          <div class="material-main">
            <div class="material-name">{{ m.title }}</div>
            <div class="material-sub">
              <span v-if="m.category" class="material-cat">{{ m.category }}</span>
              by {{ m.owner }}
            </div>
          </div>

          <!-- 3-dot menu -->
          <div class="material-menu-wrap" @click.stop>
            <button type="button" class="material-menu-btn" @click="toggleMenu('s' + m.id)" aria-label="Saved material options">⋮</button>
            <div v-if="openMenuId === 's' + m.id" class="material-menu">
              <button type="button" class="material-menu-item danger" @click="unsaveMaterial(m)">Remove from Saved</button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Upload Log (all uploads, collapsible) -->
    <div v-if="!loadingMaterials" class="content-card mt-3 upload-log">
      <button type="button" class="log-toggle" @click="showLog = !showLog">
        <span>Upload Log ({{ materials.length }})</span>
        <span class="chevron" :class="{ open: showLog }"><ic-chevron-down :size="16" /></span>
      </button>
      <div v-if="showLog" class="log-body">
        <div v-for="m in materials" :key="m.id" class="material-row">
          <div class="material-ic"><component :is="typeIcon(m.type)" :size="18" /></div>
          <div class="material-main">
            <div class="material-name">{{ m.title || m.original_filename }}</div>
            <div class="material-sub">
              {{ typeLabel(m.type) }}
              <span v-if="m.word_count"> · {{ m.word_count }} words</span>
              · {{ new Date(m.created_at).toLocaleDateString() }}
            </div>
          </div>
          <span class="material-status" :class="'status-' + m.status">{{ m.status }}</span>
        </div>
        <div v-if="materials.length === 0" style="font-size:13px;color:var(--text-muted);padding:8px 0">
          No uploads yet.
        </div>
      </div>
    </div>

    <!-- ===== Add Material Modal ===== -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-card">
        <button type="button" class="modal-close" @click="closeModal" aria-label="Close"><ic-x :size="15" /></button>
        <h2 class="modal-title">Add Material</h2>
        <p class="input-sub">Paste text, upload a file, or submit a link to generate a lesson.</p>

        <!-- Material title -->
        <div style="margin-bottom:18px">
          <div class="option-label">Material Title</div>
          <input v-model="materialTitle" class="form-control" type="text" maxlength="255"
                 style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:10px"
                 placeholder="e.g. Management Reviewer — Chapters 5-8">
          <div style="font-size:11px;color:var(--text-dim);margin-top:5px">Optional — leave blank to use the AI-generated title.</div>
        </div>

        <!-- Categories (shared across all input types) -->
        <div style="margin-bottom:18px">
          <div class="option-label">Categories</div>
          <div class="sample-chips" style="margin-bottom:8px">
            <button v-for="cat in categories" :key="cat" type="button"
                    class="sample-chip" :class="{ active: selectedCategory === cat }"
                    @click="selectedCategory = selectedCategory === cat ? null : cat">{{ cat }}</button>
            <span v-if="categories.length === 0" style="font-size:12px;color:var(--text-dim)">No categories yet — create one below.</span>
          </div>
          <div style="display:flex;gap:8px">
            <input v-model="newCategory" class="form-control" type="text" maxlength="100"
                   style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:10px;max-width:280px"
                   placeholder="Create a new category" @keyup.enter="addCategory">
            <button type="button" class="btn-ghost" @click="addCategory">Add</button>
          </div>
        </div>

        <!-- Visibility -->
        <div style="margin-bottom:18px">
          <div class="option-label">Visibility</div>
          <div class="difficulty-pills">
            <button type="button" class="difficulty-pill" :class="{ active: !isPublic }" @click="isPublic = false"><ic-lock :size="13" /> Private</button>
            <button type="button" class="difficulty-pill" :class="{ active: isPublic }" @click="isPublic = true"><ic-globe :size="13" /> Public</button>
          </div>
          <div style="font-size:11px;color:var(--text-dim);margin-top:5px">Public materials appear in the Skills Library for other learners to save.</div>
        </div>

        <!-- Input source tabs -->
        <div class="upload-tabs">
          <button v-for="tab in tabs" :key="tab.key"
                  class="upload-tab" :class="{ active: activeTab === tab.key }"
                  @click="activeTab = tab.key">
            <span v-html="tab.icon"></span>
            <span>{{ tab.label }}</span>
          </button>
        </div>

        <!-- Paste Text -->
        <div v-if="activeTab === 'paste'" class="content-card">
          <h3>Paste Your Content</h3>
          <div class="input-sub">Paste any text — notes, articles, textbook chapters, or study material.</div>
          <textarea v-model="pastedText" class="form-control"
                    style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:12px;min-height:180px;resize:vertical;font-size:14px"
                    placeholder="Paste your study material here...&#10;&#10;Example: Machine learning is a subset of artificial intelligence that enables systems to learn and improve from experience without being explicitly programmed. The key types include supervised learning, unsupervised learning, and reinforcement learning..."></textarea>
          <div class="char-counter-row">
            <span>{{ charCount }} / 50,000 characters</span>
            <button type="button" class="clear-btn" @click="clearText"><ic-x :size="13" /> Clear</button>
          </div>
        </div>

        <!-- PDF / DOCX -->
        <div v-if="activeTab === 'upload'" class="content-card">
          <h3>Upload a File</h3>
          <div class="input-sub">PDF, DOCX, or TXT — up to 10MB.</div>
          <div class="upload-drop-zone" :class="{ 'drag-over': dragOver }"
               @dragover.prevent="dragOver = true"
               @dragleave="dragOver = false"
               @drop.prevent="onDrop"
               @click="triggerFile">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <div class="drop-text">
              <span v-if="!selectedFile">Drag &amp; drop your file or <strong>browse</strong></span>
              <span v-else>{{ selectedFile.name }}</span>
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:6px" v-if="!selectedFile">PDF, DOCX, TXT — Max 10MB</div>
          </div>
          <input ref="fileInput" type="file" accept=".pdf,.docx,.txt" style="display:none" @change="onFileSelect">
        </div>

        <!-- Image (OCR) -->
        <div v-if="activeTab === 'image'" class="content-card">
          <h3>Image (OCR)</h3>
          <div class="input-sub">Extract text from an image of your notes or slides.</div>
          <div class="upload-drop-zone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg>
            <div class="drop-text">Image OCR is coming soon.</div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:6px">For now, use Paste Text, PDF / DOCX, or URL.</div>
          </div>
        </div>

        <!-- URL -->
        <div v-if="activeTab === 'url'" class="content-card">
          <h3>Submit a Link</h3>
          <div class="input-sub">We'll fetch the page and turn it into a lesson.</div>
          <input v-model="urlInput" class="form-control" type="url"
                 style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:12px"
                 placeholder="https://example.com/article-to-learn">
        </div>

        <!-- AI Processing Options -->
        <div class="content-card mt-3">
          <div class="options-header">
            <h3 style="margin:0"><ic-settings :size="17" /> AI Processing Options</h3>
            <span class="muted">Customize output</span>
          </div>

          <div class="row g-4 mt-1">
            <div class="col-md-6">
              <div class="option-label">Difficulty Level</div>
              <div class="difficulty-pills">
                <button v-for="d in difficulties" :key="d.value" type="button"
                        class="difficulty-pill" :class="{ active: difficulty === d.value }"
                        @click="difficulty = d.value">{{ d.label }}</button>
              </div>
            </div>
            <div class="col-md-6">
              <div class="option-label">Output Language</div>
              <select v-model="outputLanguage" class="form-select"
                      style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:10px">
                <option>English</option>
                <option>Filipino</option>
                <option>Spanish</option>
              </select>
            </div>
          </div>

          <div class="option-label" style="margin-top:20px">Generate Output Types</div>
          <div class="output-type-grid">
            <div v-for="ot in outputTypeList" :key="ot.key"
                 class="output-type-card"
                 :class="{ active: outputTypes[ot.key], disabled: ot.disabled }"
                 @click="!ot.disabled && (outputTypes[ot.key] = !outputTypes[ot.key])">
              <div class="ot-check"><ic-check v-if="outputTypes[ot.key]" :size="12" /></div>
              <div class="ot-icon"><component :is="ot.icon" :size="18" /></div>
              <div class="ot-title">{{ ot.title }}</div>
              <div class="ot-sub">{{ ot.sub }}</div>
            </div>
          </div>
        </div>

        <!-- Generate -->
        <div style="text-align:center;margin-top:24px">
          <button class="btn-grad lg" style="width:100%;max-width:520px" :disabled="generating" @click="generate">
            <template v-if="generating">{{ statusText || 'Generating...' }}</template>
            <template v-else><ic-sparkles :size="16" /> Generate with AI</template>
          </button>
          <div class="generate-meta">
            <span><ic-zap :size="13" /> Usually takes 15-30 seconds</span>
            <span><ic-lock :size="13" /> Your data is private</span>
          </div>
          <div v-if="error" style="margin-top:10px;color:#ff6b6b;font-size:13px">{{ error }}</div>
        </div>
      </div>
    </div>

    <!-- ===== Edit Material Modal ===== -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
      <div class="modal-card" style="max-width:520px">
        <button type="button" class="modal-close" @click="showEditModal = false" aria-label="Close"><ic-x :size="15" /></button>
        <h2 class="modal-title">Edit Material</h2>
        <p class="input-sub">Rename this material or change its category.</p>

        <div style="margin-bottom:16px">
          <div class="option-label">Material Title</div>
          <input v-model="editTitle" class="form-control" type="text" maxlength="255"
                 style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:10px"
                 placeholder="Material title">
        </div>

        <div style="margin-bottom:18px">
          <div class="option-label">Category</div>
          <div class="sample-chips" style="margin-bottom:8px">
            <button v-for="cat in categories" :key="cat" type="button"
                    class="sample-chip" :class="{ active: editCategory === cat }"
                    @click="editCategory = editCategory === cat ? null : cat">{{ cat }}</button>
          </div>
          <div style="display:flex;gap:8px">
            <input v-model="editNewCategory" class="form-control" type="text" maxlength="100"
                   style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:10px;max-width:280px"
                   placeholder="Create a new category" @keyup.enter="addEditCategory">
            <button type="button" class="btn-ghost" @click="addEditCategory">Add</button>
          </div>
        </div>

        <div style="margin-bottom:18px">
          <div class="option-label">Visibility</div>
          <div class="difficulty-pills">
            <button type="button" class="difficulty-pill" :class="{ active: !editIsPublic }" @click="editIsPublic = false"><ic-lock :size="13" /> Private</button>
            <button type="button" class="difficulty-pill" :class="{ active: editIsPublic }" @click="editIsPublic = true"><ic-globe :size="13" /> Public</button>
          </div>
        </div>

        <div v-if="editError" style="margin-bottom:10px;color:#ff6b6b;font-size:13px">{{ editError }}</div>

        <div style="display:flex;justify-content:flex-end;gap:10px">
          <button class="btn-ghost" @click="showEditModal = false">Cancel</button>
          <button class="btn-grad" :disabled="savingEdit" @click="saveEdit">{{ savingEdit ? 'Saving...' : 'Save' }}</button>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount, reactive } from 'vue';

export default {
  name: 'UploadPage',
  setup() {
    const activeTab = ref('paste');
    const materialTitle = ref('');
    const pastedText = ref('');
    const dragOver = ref(false);
    const selectedFile = ref(null);
    const fileInput = ref(null);
    const urlInput = ref('');
    const difficulty = ref('beginner');
    const outputLanguage = ref('English');
    const outputTypes = reactive({ summary: true, flashcards: true, quiz: true, mindmap: false });
    const generating = ref(false);
    const statusText = ref('');
    const error = ref(null);
    const isPublic = ref(false);
    const activeListTab = ref('owned');
    const showModal = ref(false);
    const showLog = ref(false);
    const materials = ref([]);
    const savedMaterials = ref([]);
    const loadingMaterials = ref(true);

    // Categories
    const categories = ref([]);
    const selectedCategory = ref(null);
    const newCategory = ref('');

    // Row menu + edit modal
    const openMenuId = ref(null);
    const showEditModal = ref(false);
    const editingMaterial = ref(null);
    const editTitle = ref('');
    const editCategory = ref(null);
    const editNewCategory = ref('');
    const editIsPublic = ref(false);
    const editError = ref(null);
    const savingEdit = ref(false);

    const tabs = [
      { key: 'paste',  label: 'Paste Text',  icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>' },
      { key: 'upload', label: 'PDF / DOCX',  icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>' },
      { key: 'image',  label: 'Image (OCR)', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg>' },
      { key: 'url',    label: 'URL / Link',  icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>' },
    ];

    const difficulties = [
      { value: 'beginner',     label: 'Beginner' },
      { value: 'intermediate', label: 'Intermediate' },
      { value: 'advanced',     label: 'Advanced' },
    ];

    const outputTypeList = [
      { key: 'summary',    title: 'AI Summary', sub: 'Timeline view', icon: 'ic-file' },
      { key: 'flashcards', title: 'Flashcards', sub: 'Key terms',     icon: 'ic-layers' },
      { key: 'quiz',       title: 'Quiz',       sub: 'MCQ & short',    icon: 'ic-list-checks' },
      { key: 'mindmap',    title: 'Mind Map',   sub: 'Visual web',     icon: 'ic-network', disabled: true },
    ];

    const charCount = computed(() => pastedText.value.length);
    const successfulMaterials = computed(() => materials.value.filter((m) => m.status === 'done'));

    async function loadMaterials() {
      loadingMaterials.value = true;
      try {
        const { data } = await axios.get('/api/uploads');
        materials.value = data;
      } catch (e) {
        console.error('Failed to load materials', e);
      } finally {
        loadingMaterials.value = false;
      }
    }

    async function loadCategories() {
      try {
        const { data } = await axios.get('/api/uploads/categories');
        categories.value = data;
      } catch (e) {
        console.error('Failed to load categories', e);
      }
    }

    async function loadSaved() {
      try {
        const { data } = await axios.get('/api/library/saved');
        savedMaterials.value = data;
      } catch (e) {
        console.error('Failed to load saved materials', e);
      }
    }

    function closeMenuOnOutside() {
      openMenuId.value = null;
    }

    onMounted(() => {
      loadMaterials();
      loadCategories();
      loadSaved();
      document.addEventListener('click', closeMenuOnOutside);
    });

    onBeforeUnmount(() => document.removeEventListener('click', closeMenuOnOutside));

    function openModal() {
      error.value = null;
      showModal.value = true;
    }

    function openMaterial(m) {
      if (m.summary_id) window.location.href = `/materials/${m.summary_id}`;
    }

    function closeModal() {
      if (generating.value) return;
      showModal.value = false;
    }

    function triggerFile() {
      if (fileInput.value) fileInput.value.click();
    }

    function onDrop(e) {
      dragOver.value = false;
      if (e.dataTransfer.files.length) selectedFile.value = e.dataTransfer.files[0];
    }

    function onFileSelect(e) {
      if (e.target.files.length) selectedFile.value = e.target.files[0];
    }

    function addCategory() {
      const name = newCategory.value.trim();
      if (!name) return;
      if (!categories.value.includes(name)) categories.value.push(name);
      selectedCategory.value = name;
      newCategory.value = '';
    }

    function clearText() {
      pastedText.value = '';
    }

    function typeIcon(type) {
      return { text: 'ic-pen', file: 'ic-file', url: 'ic-link', sample: 'ic-book', image: 'ic-image' }[type] || 'ic-file';
    }

    function typeLabel(type) {
      return { text: 'Pasted text', file: 'File upload', url: 'Web link', sample: 'Sample topic', image: 'Image' }[type] || type;
    }

    // ---- Row menu / edit / delete ----
    function toggleMenu(id) {
      openMenuId.value = openMenuId.value === id ? null : id;
    }

    function startEdit(m) {
      openMenuId.value = null;
      editingMaterial.value = m;
      editTitle.value = m.title || '';
      editCategory.value = m.category || null;
      editIsPublic.value = !!m.is_public;
      editNewCategory.value = '';
      editError.value = null;
      showEditModal.value = true;
    }

    function addEditCategory() {
      const name = editNewCategory.value.trim();
      if (!name) return;
      if (!categories.value.includes(name)) categories.value.push(name);
      editCategory.value = name;
      editNewCategory.value = '';
    }

    async function saveEdit() {
      if (!editingMaterial.value) return;
      savingEdit.value = true;
      editError.value = null;
      try {
        await axios.put(`/api/uploads/${editingMaterial.value.id}`, {
          title: editTitle.value.trim(),
          category: editCategory.value,
          is_public: editIsPublic.value,
        });
        showEditModal.value = false;
        await Promise.all([loadMaterials(), loadCategories()]);
      } catch (e) {
        editError.value = 'Could not save changes. Please try again.';
      } finally {
        savingEdit.value = false;
      }
    }

    async function deleteMaterial(m) {
      openMenuId.value = null;
      if (!window.confirm(`Delete "${m.title || m.original_filename}"? This removes its summary, flashcards, and quiz.`)) return;
      try {
        await axios.delete(`/api/uploads/${m.id}`);
        await loadMaterials();
      } catch (e) {
        alert('Could not delete this material. Please try again.');
      }
    }

    async function unsaveMaterial(m) {
      openMenuId.value = null;
      try {
        await axios.delete(`/api/library/${m.id}/save`);
        await loadSaved();
      } catch (e) {
        alert('Could not remove this material. Please try again.');
      }
    }

    // Poll the upload status until the job finishes (works for both sync and queued).
    async function waitForDone(uploadId) {
      for (let i = 0; i < 60; i++) {
        const { data } = await axios.get(`/api/uploads/${uploadId}/status`);
        if (data.status === 'done' && data.summary_id) return true;
        if (data.status === 'failed') {
          throw new Error(data.error_message || 'Processing failed. Please try again.');
        }
        await new Promise((r) => setTimeout(r, 2000));
      }
      throw new Error('Generation timed out. Please try again.');
    }

    async function generate() {
      if (generating.value) return;
      error.value = null;

      let body;
      const headers = {};

      const title = materialTitle.value.trim();
      const category = selectedCategory.value;
      const is_public = isPublic.value;

      if (activeTab.value === 'paste') {
        if (pastedText.value.trim().length < 50) {
          error.value = 'Paste at least 50 characters of text.';
          return;
        }
        body = { type: 'text', text: pastedText.value.trim(), title, category, is_public };
      } else if (activeTab.value === 'upload') {
        if (!selectedFile.value) {
          error.value = 'Please choose a file to upload.';
          return;
        }
        body = new FormData();
        body.append('type', 'file');
        body.append('file', selectedFile.value);
        if (title) body.append('title', title);
        if (category) body.append('category', category);
        body.append('is_public', is_public ? '1' : '0');
      } else if (activeTab.value === 'image') {
        error.value = 'Image OCR is coming soon. Use Paste Text, PDF / DOCX, or URL for now.';
        return;
      } else if (activeTab.value === 'url') {
        if (!urlInput.value.trim()) {
          error.value = 'Please enter a URL.';
          return;
        }
        body = { type: 'url', url: urlInput.value.trim(), title, category, is_public };
      } else {
        return;
      }

      generating.value = true;
      statusText.value = 'Uploading...';

      try {
        const { data } = await axios.post('/api/uploads', body, { headers });
        statusText.value = 'Generating with AI...';

        await waitForDone(data.upload_id);
        window.location.href = '/upload'; // back to My Materials
      } catch (e) {
        error.value =
          e.response?.data?.message ||
          e.message ||
          'Something went wrong while generating. Please try again.';
        generating.value = false;
        statusText.value = '';
        loadMaterials(); // refresh list so a failed attempt shows up
      }
    }

    return {
      activeTab, materialTitle, pastedText, dragOver, selectedFile, fileInput, urlInput,
      difficulty, outputLanguage, outputTypes, isPublic, activeListTab,
      generating, statusText, error,
      showModal, showLog, materials, successfulMaterials, savedMaterials, loadingMaterials,
      categories, selectedCategory, newCategory,
      openMenuId, showEditModal, editTitle, editCategory, editNewCategory, editIsPublic, editError, savingEdit,
      tabs, difficulties, outputTypeList,
      charCount, openModal, openMaterial, closeModal, onDrop, onFileSelect, triggerFile,
      addCategory, clearText, typeIcon, typeLabel,
      toggleMenu, startEdit, addEditCategory, saveEdit, deleteMaterial, unsaveMaterial, generate,
    };
  },
};
</script>
