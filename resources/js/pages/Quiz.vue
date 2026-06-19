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
          <span class="card-tag" style="margin:0">{{ currentQuestion?.difficulty }}</span>
          <span style="font-size:12px;color:var(--text-muted)">{{ currentQuestion?.xp_reward }} XP</span>
        </div>
        <h3 style="font-size:20px;margin-bottom:20px">{{ currentQuestion?.body }}</h3>

        <div v-for="(option, i) in (currentQuestion?.options || [])" :key="i"
             class="quiz-option"
             :class="{
               selected: selectedOption === option && !submitted,
               correct: submitted && option === currentQuestion?.correct_answer,
               wrong: submitted && selectedOption === option && option !== currentQuestion?.correct_answer
             }"
             @click="selectOption(option)">
          <div class="quiz-option-radio"></div>
          <div class="quiz-option-label">{{ option }}</div>
        </div>
      </div>

      <!-- Navigation -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;flex-wrap:wrap;gap:10px">
        <button class="btn-ghost" @click="prevQuestion" :disabled="currentIndex === 0">Previous</button>
        <div style="display:flex;gap:8px">
          <button class="btn-ghost" @click="skipQuestion">Skip</button>
          <button v-if="currentIndex < questions.length - 1" class="btn-grad" @click="nextQuestion" :disabled="!selectedOption">
            Next
          </button>
          <button v-else class="btn-grad" style="background:var(--green)" @click="submitQuiz" :disabled="!selectedOption">
            Submit Quiz
          </button>
        </div>
      </div>
    </template>
  </app-layout>
</template>

<script>
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue';

export default {
  name: 'QuizPage',
  setup() {
    const quiz = ref(null);
    const questions = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const currentIndex = ref(0);
    const selectedOption = ref(null);
    const submitted = ref(false);
    const timer = ref(30);
    const score = reactive({ correct: 0, wrong: 0, skipped: 0 });
    const answers = ref([]);

    let timerInterval = null;

    const currentQuestion = computed(() => questions.value[currentIndex.value]);

    onMounted(async () => {
      try {
        const pathParts = window.location.pathname.split('/');
        const quizId = pathParts[pathParts.length - 1] || 1;
        const { data } = await axios.get(`/api/quizzes/${quizId}`);
        quiz.value = data.quiz;
        questions.value = data.questions;
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

    function selectOption(option) {
      if (submitted.value) return;
      selectedOption.value = option;
    }

    function nextQuestion() {
      if (selectedOption.value) {
        answers.value.push({
          question_id: currentQuestion.value.id,
          selected: selectedOption.value,
        });
        selectedOption.value = null;
      }
      currentIndex.value++;
      startTimer();
    }

    function prevQuestion() {
      if (currentIndex.value > 0) {
        currentIndex.value--;
        selectedOption.value = null;
      }
    }

    function skipQuestion() {
      answers.value.push({ question_id: currentQuestion.value.id, selected: null });
      score.skipped++;
      selectedOption.value = null;
      if (currentIndex.value < questions.value.length - 1) {
        currentIndex.value++;
        startTimer();
      }
    }

    async function submitQuiz() {
      if (selectedOption.value) {
        answers.value.push({
          question_id: currentQuestion.value.id,
          selected: selectedOption.value,
        });
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
      quiz, questions, loading, error, currentIndex, selectedOption,
      submitted, timer, score, answers, currentQuestion,
      selectOption, nextQuestion, prevQuestion, skipQuestion, submitQuiz,
    };
  },
};
</script>