<template>
  <app-layout active-page="upload" :page-title="summary?.title || 'Material'">
    <!-- Loading -->
    <div v-if="loading" class="text-center py-5">
      <div class="skel" style="width:50%;max-width:300px;margin:0 auto 16px;height:20px"></div>
      <div class="skel" style="width:100%;max-width:500px;height:220px;margin:0 auto"></div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

    <!-- ============ MATERIAL VIEW ============ -->
    <template v-if="!loading && !error && mode === 'material'">
      <!-- Top bar: back (left) + take quiz (right) -->
      <div class="material-topbar">
        <a href="/upload" class="back-link" style="margin-bottom:0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Back to My Materials
        </a>
        <button v-if="summary?.quiz_id" class="btn-grad" @click="quizAction">
          {{ summary.quiz_attempted ? 'View Quiz Results' : 'Take a Quiz →' }}
        </button>
      </div>

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

      <!-- ===== AI Summary (below flashcards) ===== -->
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
            <span class="card-tag" style="margin:0">{{ currentQuestion?.difficulty }}</span>
            <span style="font-size:12px;color:var(--text-muted)">{{ currentQuestion?.xp_reward }} XP</span>
          </div>
          <h3 style="font-size:20px;margin-bottom:20px">{{ currentQuestion?.body }}</h3>

          <div v-for="(option, i) in (currentQuestion?.options || [])" :key="i"
               class="quiz-option" :class="{ selected: selectedOption === option }"
               @click="selectOption(option)">
            <div class="quiz-option-radio"></div>
            <div class="quiz-option-label">{{ option }}</div>
          </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;flex-wrap:wrap;gap:10px">
          <button class="btn-ghost" @click="prevQuestion" :disabled="qIndex === 0">Previous</button>
          <div style="display:flex;gap:8px">
            <button class="btn-ghost" @click="skipQuestion">Skip</button>
            <button v-if="qIndex < questions.length - 1" class="btn-grad" @click="nextQuestion" :disabled="!selectedOption">Next</button>
            <button v-else class="btn-grad" style="background:var(--green)" @click="submitQuiz" :disabled="!selectedOption || submitting">
              {{ submitting ? 'Submitting...' : 'Submit Quiz' }}
            </button>
          </div>
        </div>
      </template>
    </template>
  </app-layout>
</template>

<script>
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue';

export default {
  name: 'MaterialDetailPage',
  setup() {
    const summaryId = window.location.pathname.split('/').pop();

    const summary = ref(null);
    const deck = ref(null);
    const cards = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const mode = ref('material');     // 'material' | 'quiz'
    const summaryTab = ref('summary');

    // Quiz state (preserved across mode toggles for the session)
    const quiz = ref(null);
    const questions = ref([]);
    const quizLoaded = ref(false);
    const quizLoading = ref(false);
    const qIndex = ref(0);
    const selectedOption = ref(null);
    const submitting = ref(false);
    const timer = ref(30);
    const score = reactive({ correct: 0, wrong: 0, skipped: 0 });
    const answers = ref([]);
    let timerInterval = null;

    const currentQuestion = computed(() => questions.value[qIndex.value]);

    onMounted(async () => {
      try {
        const [sumRes, fcRes] = await Promise.all([
          axios.get(`/api/summaries/${summaryId}`),
          axios.get(`/api/flashcards/${summaryId}`).catch(() => ({ data: { deck: null, cards: [] } })),
        ]);
        summary.value = sumRes.data;
        deck.value = fcRes.data.deck;
        cards.value = fcRes.data.cards || [];

        // Mark the underlying upload as opened so the "New" badge clears.
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

    // ---- Quiz ----
    function startTimer(reset = true) {
      if (reset) timer.value = 30;
      clearInterval(timerInterval);
      timerInterval = setInterval(() => {
        if (timer.value > 0) timer.value--;
        else skipQuestion();
      }, 1000);
    }

    function quizAction() {
      // If the quiz was already taken, jump to the results; otherwise start it in-page.
      if (summary.value?.quiz_attempted) {
        window.location.href = `/quizzes/${summary.value.quiz_id}/results`;
      } else {
        openQuiz();
      }
    }

    async function openQuiz() {
      mode.value = 'quiz';
      if (!quizLoaded.value) {
        quizLoading.value = true;
        try {
          const { data } = await axios.get(`/api/quizzes/${summary.value.quiz_id}`);
          quiz.value = data.quiz;
          questions.value = data.questions;
          quizLoaded.value = true;
          startTimer(true);
        } catch (e) {
          error.value = 'Could not load the quiz. Please try again.';
          mode.value = 'material';
        } finally {
          quizLoading.value = false;
        }
      } else {
        startTimer(false); // resume without resetting progress
      }
    }

    function backToMaterial() {
      clearInterval(timerInterval); // pause; state is preserved
      mode.value = 'material';
    }

    function selectOption(option) { selectedOption.value = option; }

    function nextQuestion() {
      if (selectedOption.value) {
        answers.value.push({ question_id: currentQuestion.value.id, selected: selectedOption.value });
        selectedOption.value = null;
      }
      qIndex.value++;
      startTimer(true);
    }

    function prevQuestion() {
      if (qIndex.value > 0) { qIndex.value--; selectedOption.value = null; }
    }

    function skipQuestion() {
      if (!currentQuestion.value) return;
      answers.value.push({ question_id: currentQuestion.value.id, selected: null });
      score.skipped++;
      selectedOption.value = null;
      if (qIndex.value < questions.value.length - 1) { qIndex.value++; startTimer(true); }
    }

    async function submitQuiz() {
      if (selectedOption.value) {
        answers.value.push({ question_id: currentQuestion.value.id, selected: selectedOption.value });
      }
      submitting.value = true;
      clearInterval(timerInterval);
      try {
        const { data } = await axios.post(`/api/quizzes/${summary.value.quiz_id}/submit`, { answers: answers.value });
        window.location.href = data.redirect;
      } catch (e) {
        error.value = 'Submission failed. Please try again.';
        submitting.value = false;
      }
    }

    return {
      summary, deck, cards, loading, error, mode, summaryTab,
      quiz, questions, quizLoading, qIndex, selectedOption, submitting, timer, currentQuestion,
      termLabel,
      quizAction, openQuiz, backToMaterial, selectOption, nextQuestion, prevQuestion, skipQuestion, submitQuiz,
    };
  },
};
</script>
