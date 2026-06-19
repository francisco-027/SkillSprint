<template>
  <app-layout active-page="quizzes" page-title="Quiz">
    <div v-if="loading" class="text-center py-5">
      <div class="skel" style="width:40%;margin:0 auto 12px;height:24px"></div>
      <div class="skel" style="width:100%;max-width:500px;height:140px;margin:0 auto"></div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

    <template v-if="!loading && !error && quiz">
      <div class="page-header">
        <p>{{ quiz.mode }} · {{ quiz.difficulty }}</p>
        <h1>{{ quiz.title }}</h1>
      </div>

      <!-- Progress -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
        <div style="flex:1;max-width:400px">
          <div class="dash-progress-track">
            <div class="dash-progress-fill" :style="{ width: ((currentIndex + 1) / questions.length * 100) + '%' }"></div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:16px">
          <span style="font-size:13px;color:var(--text-muted)">Question {{ currentIndex + 1 }}/{{ questions.length }}</span>
          <span style="font-size:14px;font-weight:700;color:var(--purple-bright)">{{ timer }}s</span>
        </div>
      </div>

      <!-- Question Card -->
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
                 placeholder="Type your answer">
        </template>

        <!-- Enumeration -->
        <template v-else-if="currentQuestion?.type === 'enumeration'">
          <div style="font-size:13px;color:var(--text-muted);margin-bottom:10px">List {{ currentQuestion.expected_count }} answer{{ currentQuestion.expected_count === 1 ? '' : 's' }} (order doesn't matter):</div>
          <input v-for="n in currentQuestion.expected_count" :key="n"
                 v-model="currentAnswer[n - 1]" type="text" class="form-control"
                 style="background:var(--input-bg);color:var(--text);border:1px solid var(--input-border);border-radius:10px;margin-bottom:8px"
                 :placeholder="'Answer ' + n">
        </template>

        <!-- Multiple choice fallback (legacy quizzes with no type) -->
        <template v-else>
          <div v-for="(option, i) in (currentQuestion?.options || [])" :key="i"
               class="quiz-option" :class="{ selected: currentAnswer === option }"
               @click="currentAnswer = option">
            <div class="quiz-option-radio"></div>
            <div class="quiz-option-label">{{ option }}</div>
          </div>
        </template>
      </div>

      <!-- Navigation -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;flex-wrap:wrap;gap:10px">
        <button class="btn-ghost" @click="prevQuestion" :disabled="currentIndex === 0">Previous</button>
        <div style="display:flex;gap:8px">
          <button class="btn-ghost" @click="skipQuestion">Skip</button>
          <button v-if="currentIndex < questions.length - 1" class="btn-grad" @click="nextQuestion" :disabled="!hasAnswer">
            Next
          </button>
          <button v-else class="btn-grad" style="background:var(--green)" @click="submitQuiz" :disabled="!hasAnswer">
            Submit Quiz
          </button>
        </div>
      </div>
    </template>
  </app-layout>
</template>

<script>
import { ref, computed, reactive, watch, onMounted, onUnmounted } from 'vue';

export default {
  name: 'QuizPage',
  setup() {
    const quiz = ref(null);
    const questions = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const currentIndex = ref(0);
    const currentAnswer = ref('');    // string, or array for enumeration
    const timer = ref(30);
    const score = reactive({ correct: 0, wrong: 0, skipped: 0 });
    const answers = ref([]);

    let timerInterval = null;

    const currentQuestion = computed(() => questions.value[currentIndex.value]);

    const hasAnswer = computed(() => {
      const q = currentQuestion.value;
      if (!q) return false;
      if (q.type === 'enumeration') {
        return Array.isArray(currentAnswer.value) && currentAnswer.value.some((s) => s && String(s).trim());
      }
      return !!(currentAnswer.value && String(currentAnswer.value).trim());
    });

    watch(currentIndex, () => initAnswer());

    function initAnswer() {
      const q = currentQuestion.value;
      if (q && q.type === 'enumeration') {
        currentAnswer.value = Array(Math.max(q.expected_count || 1, 1)).fill('');
      } else {
        currentAnswer.value = '';
      }
    }

    function typeLabel(type) {
      return {
        multiple_choice: 'Multiple Choice',
        true_false: 'True / False',
        identification: 'Identification',
        enumeration: 'Enumeration',
      }[type] || 'Question';
    }

    function snapshotAnswer() {
      return Array.isArray(currentAnswer.value) ? [...currentAnswer.value] : currentAnswer.value;
    }

    onMounted(async () => {
      try {
        const pathParts = window.location.pathname.split('/');
        const quizId = pathParts[pathParts.length - 1] || 1;
        const { data } = await axios.get(`/api/quizzes/${quizId}`);
        quiz.value = data.quiz;
        questions.value = data.questions;
        initAnswer();
        startTimer();
      } catch (e) {
        error.value = 'Could not load content. Please try again.';
      } finally {
        loading.value = false;
      }
    });

    onUnmounted(() => clearInterval(timerInterval));

    function startTimer() {
      timer.value = 30;
      clearInterval(timerInterval);
      timerInterval = setInterval(() => {
        if (timer.value > 0) timer.value--;
        else skipQuestion();
      }, 1000);
    }

    function nextQuestion() {
      if (hasAnswer.value) {
        answers.value.push({ question_id: currentQuestion.value.id, selected: snapshotAnswer() });
      }
      currentIndex.value++;
      startTimer();
    }

    function prevQuestion() {
      if (currentIndex.value > 0) currentIndex.value--;
    }

    function skipQuestion() {
      answers.value.push({ question_id: currentQuestion.value.id, selected: null });
      score.skipped++;
      if (currentIndex.value < questions.value.length - 1) {
        currentIndex.value++;
        startTimer();
      }
    }

    async function submitQuiz() {
      if (hasAnswer.value) {
        answers.value.push({ question_id: currentQuestion.value.id, selected: snapshotAnswer() });
      }
      loading.value = true;
      try {
        const pathParts = window.location.pathname.split('/');
        const quizId = pathParts[pathParts.length - 1] || 1;
        const { data } = await axios.post(`/api/quizzes/${quizId}/submit`, { answers: answers.value });
        window.location.href = data.redirect;
      } catch (e) {
        error.value = 'Submission failed. Please try again.';
        loading.value = false;
      }
    }

    return {
      quiz, questions, loading, error, currentIndex, currentAnswer, hasAnswer,
      timer, score, answers, currentQuestion,
      typeLabel, nextQuestion, prevQuestion, skipQuestion, submitQuiz,
    };
  },
};
</script>