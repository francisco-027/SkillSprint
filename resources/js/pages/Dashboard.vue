<template>
  <app-layout active-page="dashboard" page-title="Dashboard">
    <!-- Header -->
    <div class="page-header">
      <span class="badge-pill"><ic-hand :size="14" /> Welcome back!</span>
      <h1 class="skills-hero" style="font-size:30px;margin:8px 0 4px">{{ greeting }}, <span class="grad-text">{{ firstName }}</span>!</h1>
      <p style="color:var(--text-muted);margin:0">Ready to continue your learning journey? You're on a roll! <ic-rocket :size="15" /></p>
    </div>

    <!-- Loading -->
    <div v-if="loading && !dashboard" class="row g-3 mt-1">
      <div class="col-md-3" v-for="n in 4" :key="n">
        <div class="stat-card"><div class="skel" style="width:80%;margin:0 auto 8px;height:24px"></div><div class="skel" style="width:50%;margin:0 auto;height:14px"></div></div>
      </div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Could not load dashboard</h5><p>{{ error }}</p></div></div>

    <template v-if="dashboard">
      <!-- Stat Cards -->
      <div class="row g-3 mt-1">
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-top"><span class="stat-ic" style="background:rgba(124,92,252,0.15)"><ic-star :size="18" color="#9d7bff" /></span><span class="stat-delta positive">+{{ dashboard.stats.xp_delta }} today</span></div>
            <div class="stat-value">{{ dashboard.stats.xp.toLocaleString() }}</div>
            <div class="stat-label">Total XP Earned</div>
            <div class="dash-progress-track" style="margin-top:10px;height:5px"><div class="dash-progress-fill" :style="{ width: levelProgress + '%' }"></div></div>
            <div class="stat-foot">{{ xpToNext }} XP to Level {{ nextLevel }}</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-top"><span class="stat-ic" style="background:rgba(255,177,61,0.15)"><ic-flame :size="18" color="#ffb13d" /></span><span class="stat-delta neutral">Best: {{ dashboard.stats.streak_best }}</span></div>
            <div class="stat-value">{{ dashboard.stats.streak }}</div>
            <div class="stat-label">Day Streak</div>
            <div class="day-dots">
              <span v-for="n in 10" :key="n" class="day-dot" :class="{ on: n <= Math.min(dashboard.stats.streak, 10) }"></span>
            </div>
            <div class="stat-foot">{{ Math.min(dashboard.stats.streak, 10) }} of 10 day goal</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-top"><span class="stat-ic" style="background:rgba(47,227,154,0.15)"><ic-book :size="18" color="#2fe39a" /></span><span class="stat-delta positive">+{{ dashboard.progress_overview.in_progress }} active</span></div>
            <div class="stat-value">{{ dashboard.stats.lessons }}</div>
            <div class="stat-label">Lessons Completed</div>
            <div class="stat-foot" style="margin-top:14px;color:var(--green)">● {{ dashboard.progress_overview.in_progress }} in progress</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-top"><span class="stat-ic" style="background:rgba(224,107,255,0.15)"><ic-target :size="18" color="#e06bff" /></span><span class="stat-delta positive">↑ 5%</span></div>
            <div class="stat-value">{{ dashboard.stats.quiz_accuracy }}%</div>
            <div class="stat-label">Quiz Accuracy</div>
            <div class="dash-progress-track" style="margin-top:10px;height:5px"><div class="dash-progress-fill" :style="{ width: dashboard.stats.quiz_accuracy + '%' }"></div></div>
          </div>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <!-- Left column -->
        <div class="col-lg-8">
          <!-- Continue Learning -->
          <div class="content-card dash-continue" v-if="dashboard.continue_learning">
            <div style="display:flex;align-items:flex-start;justify-content:space-between">
              <span class="badge-pill"><ic-book :size="14" /> Continue Learning</span>
              <span class="dash-continue-ic"><ic-brain :size="22" color="#9d7bff" /></span>
            </div>
            <h2 style="font-size:22px;font-weight:800;margin:12px 0 2px">{{ dashboard.continue_learning.title }}</h2>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:14px">{{ dashboard.continue_learning.chapter || 'Pick up where you left off' }}</div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
              <span style="color:var(--text-muted)">Overall Progress</span><span style="font-weight:700;color:var(--purple-bright)">{{ dashboard.continue_learning.progress }}%</span>
            </div>
            <div class="dash-progress-track"><div class="dash-progress-fill" :style="{ width: dashboard.continue_learning.progress + '%' }"></div></div>
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:18px">
              <button class="btn-grad" @click="go('/materials/' + dashboard.continue_learning.summary_id)"><ic-zap :size="16" /> Continue Lesson</button>
              <div style="font-size:12px;color:var(--text-muted);display:flex;gap:14px;flex-wrap:wrap">
                <span><ic-layers :size="13" /> {{ dashboard.continue_learning.flashcard_count }} Flashcards</span>
                <span><ic-clock :size="13" /> {{ dashboard.continue_learning.minutes_left }} min left</span>
              </div>
            </div>
          </div>
          <div class="content-card" v-else>
            <span class="badge-pill"><ic-book :size="14" /> Continue Learning</span>
            <p style="font-size:13px;color:var(--text-muted);margin-top:12px">You haven't started a lesson yet. <a href="/upload" style="color:var(--purple-bright)">Add a material</a> to begin.</p>
          </div>

          <!-- Recommended -->
          <div class="content-card mt-3" v-if="dashboard.recommended.length">
            <div class="card-head"><h3 style="margin:0">Recommended Next Lesson</h3><span class="badge-pill" style="background:rgba(47,227,154,0.14);color:var(--green)"><ic-sparkles :size="13" /> AI Picked</span></div>
            <div class="row g-3 mt-1">
              <div class="col-md-6" v-for="rec in dashboard.recommended" :key="rec.upload_id">
                <div class="skill-card" @click="go('/materials/' + rec.summary_id)">
                  <div class="skill-card-top">
                    <span class="rec-ic"><ic-book :size="16" color="#9d7bff" /></span>
                    <button type="button" class="save-btn" :class="{ saved: rec.is_saved }" @click.stop="toggleSaveReco(rec)">
                      <ic-bookmark :size="13" /> {{ rec.is_saved ? 'Saved' : 'Save' }}
                    </button>
                  </div>
                  <div class="skill-title">{{ rec.title }}</div>
                  <div class="skill-meta">
                    <span v-if="rec.difficulty" class="skill-level-badge" :class="'lvl-' + rec.difficulty.toLowerCase()">{{ rec.difficulty }}</span>
                    <span v-if="rec.category" class="material-cat">{{ rec.category }}</span>
                  </div>
                  <div class="skill-owner-row">
                    <span class="owner-avatar">{{ (rec.owner || '?').charAt(0) }}</span> by {{ rec.owner }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Daily Challenge -->
          <div class="content-card mt-3 challenge-card" v-if="dashboard.daily_challenge">
            <div style="display:flex;align-items:flex-start;justify-content:space-between">
              <span class="badge-pill" style="background:rgba(255,177,61,0.14);color:var(--amber)"><ic-zap :size="13" /> Daily Challenge</span>
              <span class="challenge-ic"><ic-flame :size="22" color="#ffb13d" /></span>
            </div>
            <h3 style="font-size:18px;font-weight:800;margin:12px 0 4px">{{ dashboard.daily_challenge.title }}</h3>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:14px">Test your knowledge and keep your streak alive.</div>
            <div class="challenge-stats">
              <div><div class="cs-val">{{ dashboard.daily_challenge.questions }}</div><div class="cs-lbl">Questions</div></div>
              <div><div class="cs-val">~{{ dashboard.daily_challenge.questions }} min</div><div class="cs-lbl">Est. Time</div></div>
              <div><div class="cs-val" style="text-transform:capitalize">{{ dashboard.daily_challenge.difficulty }}</div><div class="cs-lbl">Difficulty</div></div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-top:14px">
              <button class="btn-grad" style="background:linear-gradient(135deg,#ffb13d,#ff7e5f)" @click="go('/quizzes/' + dashboard.daily_challenge.quiz_id)"><ic-zap :size="16" /> Start Challenge</button>
              <span style="font-size:12px;color:var(--text-muted);display:inline-flex;align-items:center;gap:5px"><ic-hourglass :size="13" /> Resets daily</span>
            </div>
          </div>
        </div>

        <!-- Right column -->
        <div class="col-lg-4">
          <!-- Progress Overview -->
          <div class="content-card">
            <div class="card-head"><h3 style="margin:0">Progress Overview</h3><a href="/skills" class="link-muted">View All</a></div>
            <div v-if="donut.total" class="donut-wrap" style="margin-top:6px">
              <svg viewBox="0 0 120 120" class="donut">
                <circle cx="60" cy="60" r="46" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="12" />
                <circle v-for="(seg, i) in donut.segs" :key="i" cx="60" cy="60" r="46" fill="none"
                        :stroke="seg.color" stroke-width="12" stroke-linecap="round"
                        :stroke-dasharray="`${seg.dash} ${donut.C - seg.dash}`"
                        :transform="`rotate(${seg.rot} 60 60)`" />
              </svg>
              <div class="donut-center" style="flex-direction:column"><span style="font-size:26px;font-weight:800">{{ donut.total }}</span><span style="font-size:11px;color:var(--text-muted)">Total</span></div>
            </div>
            <div v-else class="chart-empty" style="padding:24px">Enroll in skills to track progress.</div>
            <div style="margin-top:12px">
              <div class="legend-row"><span><i style="background:var(--green)"></i> Completed</span><span>{{ dashboard.progress_overview.completed }} lessons</span></div>
              <div class="legend-row"><span><i style="background:var(--purple-bright)"></i> In Progress</span><span>{{ dashboard.progress_overview.in_progress }} lessons</span></div>
              <div class="legend-row"><span><i style="background:rgba(255,255,255,0.2)"></i> Not Started</span><span>{{ dashboard.progress_overview.not_started }} lessons</span></div>
            </div>
          </div>

          <!-- Active Skills -->
          <div class="content-card mt-3" v-if="dashboard.active_skills.length">
            <div class="card-head"><h3 style="margin:0">Active Skills</h3><a href="/skills" class="link-muted">Manage</a></div>
            <div v-for="sk in dashboard.active_skills" :key="sk.skill_id" class="active-skill">
              <div style="display:flex;align-items:center;justify-content:space-between;font-size:13px;margin-bottom:5px">
                <span style="font-weight:600">{{ sk.title }}</span><span style="font-weight:700;color:var(--purple-bright)">{{ sk.progress }}%</span>
              </div>
              <div class="dash-progress-track" style="height:6px"><div class="dash-progress-fill" :style="{ width: sk.progress + '%' }"></div></div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="content-card mt-3">
            <div class="card-head"><h3 style="margin:0">Recent Activity</h3><a href="/analytics" class="link-muted">See All</a></div>
            <div v-if="dashboard.recent_activity.length">
              <div v-for="(act, i) in dashboard.recent_activity" :key="i" class="activity-row">
                <span class="activity-ic"><component :is="activityIcon(act.event)" :size="15" /></span>
                <div style="flex:1;min-width:0">
                  <div style="font-size:13px;font-weight:600">{{ act.description }}</div>
                  <div style="font-size:11px;color:var(--text-dim)">{{ act.created_at }}</div>
                </div>
                <span v-if="act.xp" style="font-size:12px;font-weight:700;color:var(--green)">+{{ act.xp }}</span>
              </div>
            </div>
            <p v-else style="font-size:13px;color:var(--text-muted);margin:8px 0 0">No activity yet — start learning to see it here.</p>
          </div>
        </div>
      </div>
    </template>
  </app-layout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';

export default {
  name: 'DashboardPage',
  setup() {
    const dashboard = ref(null);
    const user = ref(null);
    const loading = ref(true);
    const error = ref(null);

    const greeting = computed(() => {
      const h = new Date().getHours();
      return h < 12 ? 'Good Morning' : h < 18 ? 'Good Afternoon' : 'Good Evening';
    });
    const firstName = computed(() => (user.value?.name || 'there').split(' ')[0]);

    // Level math (200 XP per level)
    const xp = computed(() => dashboard.value?.stats?.xp ?? 0);
    const levelProgress = computed(() => Math.round((xp.value % 200) / 200 * 100));
    const xpToNext = computed(() => 200 - (xp.value % 200));
    const nextLevel = computed(() => Math.floor(xp.value / 200) + 2);

    const donut = computed(() => {
      const po = dashboard.value?.progress_overview ?? {};
      const total = po.total ?? 0;
      const C = 2 * Math.PI * 46;
      if (!total) return { total: 0, C, segs: [] };
      const parts = [
        { key: 'completed', color: 'var(--green)' },
        { key: 'in_progress', color: 'var(--purple-bright)' },
        { key: 'not_started', color: 'rgba(255,255,255,0.2)' },
      ];
      let acc = 0;
      const segs = parts.map(p => {
        const val = po[p.key] ?? 0;
        const seg = { color: p.color, dash: (val / total) * C, rot: (acc / total) * 360 - 90 };
        acc += val;
        return seg;
      });
      return { total, C, segs };
    });

    function activityIcon(event) {
      const e = (event || '').toLowerCase();
      if (e.includes('quiz')) return 'ic-check';
      if (e.includes('badge')) return 'ic-trophy';
      if (e.includes('flash')) return 'ic-layers';
      if (e.includes('lesson') || e.includes('summary')) return 'ic-book';
      return 'ic-star';
    }

    function go(url) { window.location.href = url; }
    function continueLearning() {
      const s = dashboard.value?.continue_learning;
      go(s ? `/materials/${s.summary_id}` : '/upload');
    }

    async function toggleSaveReco(rec) {
      const was = rec.is_saved;
      rec.is_saved = !was; // optimistic
      try {
        if (was) await axios.delete(`/api/library/${rec.upload_id}/save`);
        else await axios.post(`/api/library/${rec.upload_id}/save`);
      } catch (e) {
        rec.is_saved = was; // revert on failure
      }
    }

    onMounted(async () => {
      try {
        const [dashRes, userRes] = await Promise.all([
          axios.get('/api/dashboard'),
          axios.get('/api/user'),
        ]);
        dashboard.value = dashRes.data;
        user.value = userRes.data;
      } catch (e) {
        error.value = 'Could not load content. Please try again.';
      } finally {
        loading.value = false;
      }
    });

    return {
      dashboard, user, loading, error,
      greeting, firstName, levelProgress, xpToNext, nextLevel, donut,
      activityIcon, go, continueLearning, toggleSaveReco,
    };
  },
};
</script>
