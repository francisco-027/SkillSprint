<template>
  <div class="route-view">
    <div v-if="loading" class="text-center py-5">
      <div class="skel" style="width:50%;margin:0 auto 12px;height:28px"></div>
      <div class="skel" style="width:80%;max-width:400px;margin:0 auto 20px;height:16px"></div>
      <div class="skel" style="width:100%;max-width:600px;height:200px;margin:0 auto"></div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

    <template v-if="results">
      <div class="page-header text-center">
        <h1>{{ results.score.passed ? 'Great Job!' : 'Keep Trying!' }}</h1>
        <p>You scored {{ results.score.accuracy }}% on "{{ results.quiz.title }}"</p>
      </div>

      <!-- Score Card -->
      <div class="content-card" style="text-align:center;max-width:600px;margin:0 auto 24px">
        <div style="font-size:52px;font-weight:800;background:var(--grad-text);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent">
          {{ results.score.correct }}/{{ results.score.correct + results.score.wrong + results.score.skipped }}
        </div>
        <div style="font-size:16px;font-weight:700;margin-bottom:4px">Grade: {{ results.score.grade }}</div>
        <div style="font-size:13px;color:var(--text-muted)">{{ results.score.accuracy }}% accuracy</div>
        <div style="display:flex;justify-content:center;gap:24px;margin-top:16px;font-size:14px">
          <span style="color:var(--green)">{{ results.score.correct }} correct</span>
          <span v-if="results.score.wrong" style="color:#ff5a6e">{{ results.score.wrong }} wrong</span>
          <span v-if="results.score.skipped" style="color:var(--text-muted)">{{ results.score.skipped }} skipped</span>
        </div>
      </div>

      <!-- XP Earned -->
      <div class="content-card" style="max-width:600px;margin:0 auto 24px;display:flex;justify-content:space-around;text-align:center;flex-wrap:wrap;gap:16px">
        <div>
          <div style="font-size:24px;font-weight:800;color:var(--purple-bright)">+{{ results.xp.earned }}</div>
          <div style="font-size:12px;color:var(--text-muted)">XP Earned</div>
        </div>
        <div>
          <div style="font-size:24px;font-weight:800;color:#f5a623">+{{ results.xp.streak_bonus }}</div>
          <div style="font-size:12px;color:var(--text-muted)">Streak Bonus</div>
        </div>
        <div>
          <div style="font-size:24px;font-weight:800;color:var(--text-muted)">+{{ results.xp.speed_bonus }}</div>
          <div style="font-size:12px;color:var(--text-muted)">Speed Bonus</div>
        </div>
      </div>

      <!-- Achievements -->
      <div v-if="results.achievements_unlocked.length" class="content-card" style="max-width:600px;margin:0 auto 24px">
        <h3>Achievements Unlocked</h3>
        <div v-for="ach in results.achievements_unlocked" :key="ach.slug"
             style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-top:1px solid var(--card-border)">
          <span style="font-weight:600">{{ ach.title }}</span>
          <span style="color:var(--purple-bright);font-weight:700">+{{ ach.xp }} XP</span>
        </div>
      </div>

      <!-- Question Review -->
      <div class="content-card">
        <h3>Question Review</h3>
        <div style="display:flex;gap:8px;margin:14px 0;flex-wrap:wrap">
          <button class="upload-tab-btn" :class="{ active: activeReviewTab === 'all' }" @click="activeReviewTab = 'all'">All ({{ results.questions.length }})</button>
          <button class="upload-tab-btn" :class="{ active: activeReviewTab === 'correct' }" @click="activeReviewTab = 'correct'">
            Correct ({{ results.questions.filter(q => q.is_correct).length }})
          </button>
          <button class="upload-tab-btn" :class="{ active: activeReviewTab === 'wrong' }" @click="activeReviewTab = 'wrong'">
            Wrong ({{ results.questions.filter(q => !q.is_correct).length }})
          </button>
        </div>

        <div v-for="(q, i) in reviewedQuestions" :key="i"
             class="quiz-option"
             :class="{ correct: q.is_correct, wrong: !q.is_correct }"
             style="cursor:default;flex-direction:column;align-items:flex-start;gap:8px">
          <div style="display:flex;align-items:center;gap:10px;width:100%">
            <span class="card-tag" style="margin:0">{{ q.tag }}</span>
            <span style="font-size:12px;color:var(--text-muted)">{{ q.xp }} XP</span>
          </div>
          <div style="font-weight:600;font-size:15px">{{ q.body }}</div>
          <div style="font-size:13px;color:var(--text-muted)">
            Your answer: <span :style="{ color: q.is_correct ? 'var(--green)' : '#ff5a6e' }">{{ q.user_answer }}</span>
          </div>
          <div v-if="!q.is_correct" style="font-size:13px;color:var(--green)">
            Correct: {{ q.correct_answer }}
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap">
        <button class="btn-grad" @click="retryQuiz">Retake Quiz</button>
        <button v-if="results.summary_id" class="btn-ghost" @click="studyMaterial">Study Flashcards</button>
        <button class="btn-ghost" @click="backToDashboard">Back to Dashboard</button>
      </div>
    </template>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'QuizResultsPage',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const results = ref(null);
    const loading = ref(true);
    const error = ref(null);
    const activeReviewTab = ref('all');
    const quizId = ref(route.params.quizId);

    const reviewedQuestions = computed(() => {
      if (!results.value) return [];
      if (activeReviewTab.value === 'correct') return results.value.questions.filter(q => q.is_correct);
      if (activeReviewTab.value === 'wrong') return results.value.questions.filter(q => !q.is_correct);
      return results.value.questions;
    });

    onMounted(async () => {
      try {
        const { data } = await axios.get(`/api/quizzes/${quizId.value}/results`);
        results.value = data;
      } catch (e) {
        error.value = 'Could not load content. Please try again.';
      } finally {
        loading.value = false;
      }
    });

    function studyMaterial() {
      if (results.value?.summary_id) router.push(`/materials/${results.value.summary_id}`);
    }
    function backToDashboard() {
      router.push('/home');
    }
    function retryQuiz() {
      router.push(`/quizzes/${quizId.value}`);
    }

    return {
      results, loading, error, activeReviewTab, reviewedQuestions,
      studyMaterial, backToDashboard, retryQuiz,
    };
  },
};
</script>