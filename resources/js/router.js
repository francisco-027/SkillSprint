import { createRouter, createWebHistory } from 'vue-router';

import Skills        from './pages/Skills.vue';
import Dashboard     from './pages/Dashboard.vue';
import Upload        from './pages/Upload.vue';
import MaterialDetail from './pages/MaterialDetail.vue';
import Summary       from './pages/Summary.vue';
import Flashcards    from './pages/Flashcards.vue';
import Quiz          from './pages/Quiz.vue';
import QuizResults   from './pages/QuizResults.vue';
import Analytics     from './pages/Analytics.vue';
import Achievements  from './pages/Achievements.vue';
import Settings      from './pages/Settings.vue';

// `meta.active` drives the nav highlight; `meta.backHref`/`backLabel` render the
// desktop back button in the top bar.
const routes = [
  { path: '/home',                    component: Dashboard,      meta: { active: 'dashboard' } },
  { path: '/skills',                  component: Skills,         meta: { active: 'skills' } },
  { path: '/upload',                  component: Upload,         meta: { active: 'upload' } },
  { path: '/materials/:id',           component: MaterialDetail, meta: { active: 'upload', backHref: '/upload', backLabel: 'Back to My Materials' } },
  { path: '/summaries/:id',           component: Summary,        meta: { active: '', backHref: '/upload', backLabel: 'Back to My Materials' } },
  { path: '/flashcards/:deckId',      component: Flashcards,     meta: { active: 'flashcards' } },
  { path: '/quizzes/:quizId',         component: Quiz,           meta: { active: 'quizzes' } },
  { path: '/quizzes/:quizId/results', component: QuizResults,    meta: { active: 'quizzes' } },
  { path: '/analytics',               component: Analytics,      meta: { active: 'analytics' } },
  { path: '/achievements',            component: Achievements,   meta: { active: 'achievements' } },
  { path: '/settings',                component: Settings,       meta: { active: 'settings' } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

export default router;
