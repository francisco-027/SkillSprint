<template>
  <div class="route-view">
    <!-- Loading -->
    <div v-if="loading" class="text-center py-5">
      <div class="skel" style="width:50%;max-width:300px;margin:0 auto 16px;height:20px"></div>
      <div class="skel" style="width:100%;max-width:500px;height:220px;margin:0 auto"></div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

    <!-- ============ MATERIAL VIEW ============ -->
    <template v-if="!loading && !error && mode === 'material'">
      <div class="page-header">
        <p><span style="color:var(--purple-bright);font-weight:600">{{ summary?.difficulty }}</span>
           · {{ summary?.estimated_minutes }} min · {{ summary?.source_filename }}</p>
        <h1>{{ summary?.title }}</h1>
      </div>

      <!-- ===== Flashcards (top) ===== -->
      <div v-if="cards.length" class="content-card">
        <h3 style="margin:0 0 16px">Flashcards</h3>
        <flashcard-deck :cards="cards" :deck-id="summary?.id" />
      </div>

      <!-- ===== Quizzes ===== -->
      <div class="content-card" style="margin-top:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
          <h3 style="margin:0">Quizzes</h3>
          <button class="btn-grad" @click="openGenModal"><ic-sparkles :size="15" /> Generate a Quiz</button>
        </div>

        <div v-if="!quizzes.length" style="text-align:center;padding:24px 16px">
          <p style="font-size:13px;color:var(--text-muted);margin:0">
            No quizzes yet. Click <strong>Generate a Quiz</strong> to test yourself on this material.
          </p>
        </div>

        <div v-for="qz in quizzes" :key="qz.id" class="material-row" style="margin-top:10px">
          <div class="material-ic"><ic-list-checks :size="18" /></div>
          <div class="material-main">
            <div class="material-name">{{ qz.title }}</div>
            <div class="material-sub">
              {{ qz.difficulty }} · {{ qz.question_count }} questions · {{ new Date(qz.created_at).toLocaleDateString() }}
              <span v-if="qz.attempted"> · Last score {{ qz.accuracy }}% ({{ qz.grade }})</span>
            </div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="btn-ghost" @click="openQuiz(qz.id)">{{ qz.attempted ? 'Re-take' : 'Take Quiz' }}</button>
            <button v-if="qz.attempted" class="btn-ghost" @click="viewResults(qz.id)">Results</button>
          </div>
        </div>
      </div>

      <!-- ===== AI Summary (below) ===== -->
      <div v-if="summary" style="margin-top:24px">
        <div style="display:flex;gap:8px;margin-bottom:16px">
          <button class="upload-tab-btn" :class="{ active: summaryTab === 'summary' }" @click="summaryTab = 'summary'">AI Summary</button>
          <button v-if="(summary.timeline_steps || []).length" class="upload-tab-btn" :class="{ active: summaryTab === 'timeline' }" @click="summaryTab = 'timeline'">Timeline</button>
        </div>

        <!-- Summary sections -->
        <div v-if="summaryTab === 'summary'">
          <div v-for="section in (summary.content_sections || [])" :key="section.number" class="content-card glow-card">
            <div style="display:flex;align-items:flex-start;gap:14px">
              <span style="font-size:24px;font-weight:800;color:var(--purple-bright);flex-shrink:0;line-height:1">{{ section.number }}</span>
              <div style="flex:1">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                  <h3 style="margin:0;font-size:17px">{{ section.title || section.heading }}</h3>
                  <span v-if="section.tag" class="card-tag" style="margin:0">{{ section.tag }}</span>
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
                <div v-if="section.read_minutes" style="font-size:12px;color:var(--text-dim);margin-top:8px">{{ section.read_minutes }} min read</div>
              </div>
            </div>
          </div>

          <!-- Key terms -->
          <div v-if="(summary.key_terms || []).length" class="content-card glow-card">
            <h3>Key Terms</h3>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
              <span v-for="term in summary.key_terms" :key="termLabel(term)"
                    style="padding:6px 13px;border-radius:999px;font-size:13px;font-weight:500;background:rgba(124,92,252,0.1);color:var(--purple-bright)">{{ termLabel(term) }}</span>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div v-if="summaryTab === 'timeline'">
          <div v-for="step in (summary.timeline_steps || [])" :key="step.step" class="content-card glow-card"
               style="display:flex;align-items:flex-start;gap:16px">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--grad);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0">
              {{ step.step }}
            </div>
            <div>
              <h3 style="font-size:16px;margin:0">{{ step.title }}</h3>
              <p style="font-size:14px;color:var(--text-muted);margin:4px 0 0">{{ step.description || step.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ============ QUIZ VIEW ============ -->
    <template v-if="!loading && !error && mode === 'quiz'">
      <div class="material-topbar">
        <button class="back-link" style="margin-bottom:0;cursor:pointer" @click="backToMaterial">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Back to Material
        </button>
        <span style="font-size:12px;color:var(--text-dim)">Progress is kept while this page stays open</span>
      </div>

      <div v-if="quizLoading" class="text-center py-5">
        <div class="skel" style="width:40%;margin:0 auto 12px;height:24px"></div>
        <div class="skel" style="width:100%;max-width:500px;height:140px;margin:0 auto"></div>
      </div>

      <template v-if="!quizLoading && quiz">
        <div class="page-header">
          <p>{{ quiz.mode }} · {{ quiz.difficulty }}</p>
          <h1>{{ quiz.title }}</h1>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
          <div style="flex:1;max-width:400px">
            <div class="dash-progress-track">
              <div class="dash-progress-fill" :style="{ width: ((qIndex + 1) / questions.length * 100) + '%' }"></div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:16px">
            <span style="font-size:13px;color:var(--text-muted)">Question {{ qIndex + 1 }}/{{ questions.length }}</span>
            <span style="font-size:14px;font-weight:700;color:var(--purple-bright)">{{ timer }}s</span>
          </div>
        </div>

        <div class="content-card">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <span class="card-tag" style="margin:0">{{ typeLabel(currentQuestion?.type) }}</span>
            <span class="card-tag" style="margin:0">{{ currentQuestion?.difficulty }}</span>
            <span style="font-size:12px;color:var(--text-muted)">{{ currentQuestion?.xp_reward }} XP</span>
          </div>
          <h3 style="font-size:20px;margin-bottom:20px">{{ currentQuestion?.body }}</h3>

          <!-- Multiple choice / True-False -->
          <template v-if="currentQuestion?.type === 'multiple_choice' || currentQuestion?.type === 'true_false'">
            <div v-for="(option, i) in (currentQuestion?.options || [])" :key="i"
                 class="quiz-option" :class="{ selected: currentAnswer === option }"
                 @click="currentAnswer = option">
              <div class="quiz-option-radio"></div>
              <div class="quiz-option-label">{{ option }}</div>
            </div>
          </template>

          <!-- Identification -->
          <template v-else-if="currentQuestion?.type === 'identification'">
            <input v-model="currentAnswer" type="text" class="form-control"
                   style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:10px"
                   placeholder="Type your answer" @keyup.enter="hasAnswer && (qIndex < questions.length - 1 ? nextQuestion() : submitQuiz())">
          </template>

          <!-- Enumeration -->
          <template v-else-if="currentQuestion?.type === 'enumeration'">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:10px">List {{ currentQuestion.expected_count }} answer{{ currentQuestion.expected_count === 1 ? '' : 's' }} (order doesn't matter):</div>
            <input v-for="n in currentQuestion.expected_count" :key="n"
                   v-model="currentAnswer[n - 1]" type="text" class="form-control"
                   style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:10px;margin-bottom:8px"
                   :placeholder="'Answer ' + n">
          </template>

          <!-- Options fallback (legacy quizzes with no/other type) -->
          <template v-else>
            <div v-for="(option, i) in (currentQuestion?.options || [])" :key="i"
                 class="quiz-option" :class="{ selected: currentAnswer === option }"
                 @click="currentAnswer = option">
              <div class="quiz-option-radio"></div>
              <div class="quiz-option-label">{{ option }}</div>
            </div>
          </template>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;flex-wrap:wrap;gap:10px">
          <button class="btn-ghost" @click="prevQuestion" :disabled="qIndex === 0">Previous</button>
          <div style="display:flex;gap:8px">
            <button class="btn-ghost" @click="skipQuestion">Skip</button>
            <button v-if="qIndex < questions.length - 1" class="btn-grad" @click="nextQuestion" :disabled="!hasAnswer">Next</button>
            <button v-else class="btn-grad" style="background:var(--green)" @click="submitQuiz" :disabled="!hasAnswer || submitting">
              {{ submitting ? 'Submitting...' : 'Submit Quiz' }}
            </button>
          </div>
        </div>
      </template>
    </template>

    <!-- ===== Generate Quiz Modal ===== -->
    <div v-if="showGenModal" class="modal-overlay" @click.self="closeGenModal">
      <div class="modal-card" style="max-width:540px">
        <button type="button" class="modal-close" @click="closeGenModal" aria-label="Close"><ic-x :size="15" /></button>
        <h2 class="modal-title">Generate a Quiz</h2>
        <p class="input-sub">Choose the difficulty, how many questions, and which question types to include.</p>

        <!-- Difficulty -->
        <div style="margin-bottom:18px">
          <div class="option-label">Difficulty</div>
          <div class="difficulty-pills">
            <button v-for="d in genDifficulties" :key="d" type="button"
                    class="difficulty-pill" :class="{ active: genDifficulty === d }"
                    @click="genDifficulty = d">{{ d }}</button>
          </div>
        </div>

        <!-- Number of questions -->
        <div style="margin-bottom:18px">
          <div class="option-label">Number of Questions</div>
          <input v-model.number="genCount" type="number" min="3" max="30" class="form-control"
                 style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:10px;max-width:160px">
          <div style="font-size:11px;color:var(--text-dim);margin-top:5px">Between 3 and 30.</div>
        </div>

        <!-- Question types -->
        <div style="margin-bottom:18px">
          <div class="option-label">Question Types</div>
          <label v-for="t in genTypeList" :key="t.key" class="gen-type-row">
            <input type="checkbox" v-model="genTypes[t.key]">
            <span>{{ t.label }}</span>
          </label>
          <div style="font-size:11px;color:var(--text-dim);margin-top:6px">
            Pick more than one for a mixed quiz — the AI splits the questions into sections by type.
          </div>
        </div>

        <div v-if="genError" style="margin-bottom:10px;color:#ff6b6b;font-size:13px">{{ genError }}</div>

        <div style="display:flex;justify-content:flex-end;gap:10px">
          <button class="btn-ghost" @click="closeGenModal" :disabled="generatingQuiz">Cancel</button>
          <button class="btn-grad" :disabled="generatingQuiz" @click="generateQuiz">
            <template v-if="generatingQuiz">Generating…</template>
            <template v-else><ic-sparkles :size="15" /> Generate</template>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, reactive, watch, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'MaterialDetailPage',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const summaryId = route.params.id;

    const summary = ref(null);
    const deck = ref(null);
    const cards = ref([]);
    const quizzes = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const mode = ref('material');     // 'material' | 'quiz'
    const summaryTab = ref('summary');

    // Quiz-taking state
    const quiz = ref(null);
    const questions = ref([]);
    const activeQuizId = ref(null);
    const quizLoading = ref(false);
    const qIndex = ref(0);
    const currentAnswer = ref('');    // string, or array for enumeration
    const submitting = ref(false);
    const timer = ref(30);
    const answers = ref([]);
    let timerInterval = null;

    // Generate-quiz modal state
    const showGenModal = ref(false);
    const genDifficulties = ['Beginner', 'Intermediate', 'Advanced'];
    const genDifficulty = ref('Intermediate');
    const genCount = ref(10);
    const genTypeList = [
      { key: 'multiple_choice', label: 'Multiple Choice' },
      { key: 'true_false',      label: 'True / False' },
      { key: 'identification',  label: 'Identification' },
      { key: 'enumeration',     label: 'Enumeration' },
    ];
    const genTypes = reactive({ multiple_choice: true, true_false: false, identification: false, enumeration: false });
    const generatingQuiz = ref(false);
    const genError = ref(null);

    const currentQuestion = computed(() => questions.value[qIndex.value]);

    const hasAnswer = computed(() => {
      const q = currentQuestion.value;
      if (!q) return false;
      if (q.type === 'enumeration') {
        return Array.isArray(currentAnswer.value) && currentAnswer.value.some((s) => s && String(s).trim());
      }
      return !!(currentAnswer.value && String(currentAnswer.value).trim());
    });

    // Reset the input when moving to a new question, matching its type.
    watch(qIndex, () => initAnswer());

    function initAnswer() {
      const q = currentQuestion.value;
      if (q && q.type === 'enumeration') {
        currentAnswer.value = Array(Math.max(q.expected_count || 1, 1)).fill('');
      } else {
        currentAnswer.value = '';
      }
    }

    onMounted(async () => {
      try {
        const [sumRes, fcRes] = await Promise.all([
          axios.get(`/api/summaries/${summaryId}`),
          axios.get(`/api/flashcards/${summaryId}`).catch(() => ({ data: { deck: null, cards: [] } })),
        ]);
        summary.value = sumRes.data;
        quizzes.value = sumRes.data.quizzes || [];
        deck.value = fcRes.data.deck;
        cards.value = fcRes.data.cards || [];

        if (summary.value?.upload_id) {
          axios.patch(`/api/uploads/${summary.value.upload_id}/open`).catch(() => {});
        }
      } catch (e) {
        error.value = 'Could not load this material. Please try again.';
      } finally {
        loading.value = false;
      }
    });

    onUnmounted(() => clearInterval(timerInterval));

    function termLabel(term) {
      return typeof term === 'object' ? (term.term || term.label) : term;
    }

    function typeLabel(type) {
      return {
        multiple_choice: 'Multiple Choice',
        true_false: 'True / False',
        identification: 'Identification',
        enumeration: 'Enumeration',
      }[type] || 'Question';
    }

    // ---- Quiz history actions ----
    async function reloadQuizzes() {
      try {
        const { data } = await axios.get(`/api/summaries/${summaryId}`);
        summary.value = data;
        quizzes.value = data.quizzes || [];
      } catch (e) { /* keep existing list on failure */ }
    }

    function viewResults(quizId) {
      router.push(`/quizzes/${quizId}/results`);
    }

    // ---- Generate modal ----
    function openGenModal() {
      genError.value = null;
      showGenModal.value = true;
    }

    function closeGenModal() {
      if (generatingQuiz.value) return;
      showGenModal.value = false;
    }

    async function generateQuiz() {
      const types = Object.keys(genTypes).filter((k) => genTypes[k]);
      if (!types.length) { genError.value = 'Select at least one question type.'; return; }
      if (!(genCount.value >= 3 && genCount.value <= 30)) { genError.value = 'Choose between 3 and 30 questions.'; return; }

      generatingQuiz.value = true;
      genError.value = null;
      try {
        const { data } = await axios.post(`/api/summaries/${summaryId}/quizzes`, {
          difficulty: genDifficulty.value,
          question_count: genCount.value,
          types,
        });
        showGenModal.value = false;
        await reloadQuizzes();
        openQuiz(data.quiz_id);
      } catch (e) {
        genError.value = e.response?.data?.message || 'Could not generate the quiz. Please try again.';
      } finally {
        generatingQuiz.value = false;
      }
    }

    // ---- Quiz taking ----
    function startTimer(reset = true) {
      if (reset) timer.value = 30;
      clearInterval(timerInterval);
      timerInterval = setInterval(() => {
        if (timer.value > 0) timer.value--;
        else skipQuestion();
      }, 1000);
    }

    async function openQuiz(quizId) {
      mode.value = 'quiz';
      quizLoading.value = true;
      // Fresh attempt state
      qIndex.value = 0;
      answers.value = [];
      try {
        const { data } = await axios.get(`/api/quizzes/${quizId}`);
        quiz.value = data.quiz;
        questions.value = data.questions;
        activeQuizId.value = quizId;
        initAnswer();
        startTimer(true);
      } catch (e) {
        error.value = 'Could not load the quiz. Please try again.';
        mode.value = 'material';
      } finally {
        quizLoading.value = false;
      }
    }

    function backToMaterial() {
      clearInterval(timerInterval);
      mode.value = 'material';
    }

    function snapshotAnswer() {
      return Array.isArray(currentAnswer.value) ? [...currentAnswer.value] : currentAnswer.value;
    }

    function nextQuestion() {
      if (hasAnswer.value) {
        answers.value.push({ question_id: currentQuestion.value.id, selected: snapshotAnswer() });
      }
      qIndex.value++;
      startTimer(true);
    }

    function prevQuestion() {
      if (qIndex.value > 0) qIndex.value--;
    }

    function skipQuestion() {
      if (!currentQuestion.value) return;
      answers.value.push({ question_id: currentQuestion.value.id, selected: null });
      if (qIndex.value < questions.value.length - 1) {
        qIndex.value++;
        startTimer(true);
      }
    }

    async function submitQuiz() {
      if (hasAnswer.value) {
        answers.value.push({ question_id: currentQuestion.value.id, selected: snapshotAnswer() });
      }
      submitting.value = true;
      clearInterval(timerInterval);
      try {
        const { data } = await axios.post(`/api/quizzes/${activeQuizId.value}/submit`, { answers: answers.value });
        router.push(data.redirect);
      } catch (e) {
        error.value = 'Submission failed. Please try again.';
        submitting.value = false;
      }
    }

    return {
      summary, deck, cards, quizzes, loading, error, mode, summaryTab,
      quiz, questions, quizLoading, qIndex, currentAnswer, submitting, timer, currentQuestion, hasAnswer,
      showGenModal, genDifficulties, genDifficulty, genCount, genTypeList, genTypes, generatingQuiz, genError,
      termLabel, typeLabel,
      reloadQuizzes, viewResults, openGenModal, closeGenModal, generateQuiz,
      openQuiz, backToMaterial, nextQuestion, prevQuestion, skipQuestion, submitQuiz,
    };
  },
};
</script>

<style scoped>
.gen-type-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  font-size: 14px;
  cursor: pointer;
}
.gen-type-row input { width: 16px; height: 16px; accent-color: var(--purple-bright); cursor: pointer; }
</style>
