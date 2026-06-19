<template>
  <div class="route-view">
    <!-- Header -->
    <div class="page-head-row">
      <div>
        <span class="badge-pill"><ic-bar-chart :size="14" /> Learning Analytics</span>
        <h1 class="skills-hero" style="font-size:32px;margin:8px 0 4px">Your Progress <span class="grad-text">Overview</span></h1>
        <p style="color:var(--text-muted);margin:0">Track your learning journey, skill growth, and performance trends.</p>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <div class="range-toggle">
          <button :class="{ active: timeRange === 'week' }" @click="timeRange = 'week'">Week</button>
          <button :class="{ active: timeRange === 'month' }" @click="timeRange = 'month'">Month</button>
          <button :class="{ active: timeRange === 'all' }" @click="timeRange = 'all'">All Time</button>
        </div>
        <button class="btn-ghost" @click="exportData"><ic-download :size="15" /> Export</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-4">
      <div class="skel" style="width:100%;height:120px;margin-bottom:12px"></div>
      <div class="skel" style="width:100%;height:240px;margin-bottom:12px"></div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

    <template v-if="analytics && !loading">
      <!-- Stat Cards -->
      <div class="row g-3 mb-1 mt-1">
        <div class="col-md-3" v-for="s in statCards" :key="s.label">
          <div class="stat-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
              <span class="stat-ic" :style="{ background: s.bg }"><component :is="s.icon" :size="18" :color="s.fg" /></span>
              <span class="stat-delta" :class="s.deltaClass">{{ s.delta }}</span>
            </div>
            <div class="stat-value">{{ s.value }}</div>
            <div class="stat-label">{{ s.label }}</div>
            <div class="dash-progress-track" style="margin-top:10px;height:5px">
              <div class="dash-progress-fill" :style="{ width: s.fill + '%' }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Weekly Progress + Streak Tracker -->
      <div class="row g-3 mt-1">
        <div class="col-lg-8">
          <div class="content-card">
            <div class="card-head">
              <div><h3 style="margin:0"><ic-bar-chart :size="17" /> Weekly Progress</h3><span class="card-sub">Minutes studied per day</span></div>
            </div>
            <div class="bar-chart">
              <div class="bar-goal" :style="{ bottom: goalPct + '%' }"><span>Goal (30 min)</span></div>
              <div v-for="(val, i) in analytics.weekly_progress" :key="i" class="bar-col">
                <div class="bar-val">{{ val }}</div>
                <div class="bar-fill" :style="{ height: barHeight(val) + '%' }"></div>
                <div class="bar-label">{{ dayLabels[i] }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="content-card">
            <div class="card-head"><h3 style="margin:0"><ic-flame :size="17" /> Streak Tracker</h3></div>
            <span class="card-sub">Last 14 days</span>
            <div class="streak-grid">
              <div v-for="(d, i) in analytics.streak_calendar" :key="i"
                   class="streak-cell" :class="{ done: d.completed }" :title="d.date">{{ d.label }}</div>
            </div>
            <div style="display:flex;gap:14px;margin-top:10px;font-size:11px;color:var(--text-muted)">
              <span><i class="dot-done"></i> Completed</span>
              <span><i class="dot-missed"></i> Missed</span>
            </div>
            <div class="streak-active">
              <div style="font-size:24px;display:flex;align-items:center;justify-content:center;gap:6px"><ic-flame :size="22" color="#ffb13d" /> {{ analytics.stats.streak_days }}</div>
              <div style="font-weight:700;font-size:14px">Day Streak Active!</div>
              <div style="font-size:12px;color:var(--text-muted)">Keep going to beat your best of {{ analytics.stats.streak_best }} days.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Skill Growth + Quiz Accuracy -->
      <div class="row g-3 mt-1">
        <div class="col-lg-8">
          <div class="content-card">
            <div class="card-head">
              <div><h3 style="margin:0"><ic-trending-up :size="17" /> Skill Growth</h3><span class="card-sub">Proficiency over 8 weeks</span></div>
            </div>
            <div v-if="hasGrowth">
              <svg viewBox="0 0 420 200" class="line-chart">
                <line v-for="g in [0,0.25,0.5,0.75,1]" :key="g" x1="0" :y1="g*180+10" x2="420" :y2="g*180+10" stroke="rgba(255,255,255,0.05)" />
                <polyline v-for="(series, si) in analytics.skill_growth.series" :key="si"
                  :stroke="seriesColor(si)" stroke-width="2.5" fill="none" stroke-linejoin="round"
                  :points="getPolyline(series.values)" />
              </svg>
              <div class="legend">
                <span v-for="(series, si) in analytics.skill_growth.series" :key="si">
                  <i :style="{ background: seriesColor(si) }"></i> {{ series.label }}
                </span>
              </div>
            </div>
            <div v-else class="chart-empty">Study a few materials to see your skill growth over time.</div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="content-card">
            <div class="card-head"><div><h3 style="margin:0"><ic-target :size="17" /> Quiz Accuracy</h3><span class="card-sub">By subject area</span></div></div>
            <div class="donut-wrap">
              <svg viewBox="0 0 120 120" class="donut">
                <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="12" />
                <circle cx="60" cy="60" r="50" fill="none" stroke="url(#acc)" stroke-width="12" stroke-linecap="round"
                        :stroke-dasharray="`${accDash} ${circ}`" transform="rotate(-90 60 60)" />
                <defs><linearGradient id="acc" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7c5cfc"/><stop offset="1" stop-color="#d56bff"/></linearGradient></defs>
              </svg>
              <div class="donut-center">{{ analytics.stats.quiz_accuracy }}%</div>
            </div>
            <div v-if="analytics.quiz_accuracy_by_subject.length" style="margin-top:12px">
              <div v-for="(q, i) in analytics.quiz_accuracy_by_subject" :key="i" class="subj-row">
                <span class="subj-dot" :style="{ background: seriesColor(i) }"></span>
                <span class="subj-name">{{ q.label }}</span>
                <span class="subj-val">{{ q.value }}%</span>
              </div>
            </div>
            <div v-else class="chart-empty" style="margin-top:10px">Take a quiz to see accuracy by subject.</div>
          </div>
        </div>
      </div>

      <!-- Time Invested + Skill Radar -->
      <div class="row g-3 mt-1">
        <div class="col-lg-8">
          <div class="content-card">
            <div class="card-head">
              <div><h3 style="margin:0"><ic-clock :size="17" /> Total Time Invested</h3><span class="card-sub">Hours per learning activity</span></div>
              <span class="chip-total">{{ analytics.stats.total_hours }}h Total</span>
            </div>
            <div v-for="b in breakdownRows" :key="b.key" class="breakdown-row">
              <span class="breakdown-ic" :style="{ background: b.bg }"><component :is="b.icon" :size="15" :color="b.bar" /></span>
              <div style="flex:1">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                  <span>{{ b.label }}</span><span style="font-weight:700">{{ b.hours }}h</span>
                </div>
                <div class="dash-progress-track" style="height:6px">
                  <div class="dash-progress-fill" :style="{ width: b.pct + '%', background: b.bar }"></div>
                </div>
              </div>
            </div>
            <div class="time-totals">
              <div><div class="tt-val">{{ analytics.time_totals.today }}h</div><div class="tt-lbl">Today</div></div>
              <div><div class="tt-val">{{ analytics.time_totals.week }}h</div><div class="tt-lbl">This Week</div></div>
              <div><div class="tt-val">{{ analytics.time_totals.month }}h</div><div class="tt-lbl">This Month</div></div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="content-card">
            <div class="card-head"><div><h3 style="margin:0"><ic-radar :size="17" /> Skill Radar</h3><span class="card-sub">Current proficiency</span></div></div>
            <div v-if="radarPts">
              <svg viewBox="0 0 200 200" class="radar">
                <polygon v-for="ring in [1,0.66,0.33]" :key="ring" :points="radarGrid(ring)" fill="none" stroke="rgba(255,255,255,0.06)" />
                <line v-for="(p, i) in radarAxes" :key="i" x1="100" y1="100" :x2="p.x" :y2="p.y" stroke="rgba(255,255,255,0.06)" />
                <polygon :points="radarPts" fill="rgba(124,92,252,0.25)" stroke="var(--purple-bright)" stroke-width="2" />
              </svg>
              <div v-for="(label, i) in analytics.skill_radar.labels" :key="i" class="subj-row">
                <span class="subj-dot" style="background:var(--purple-bright)"></span>
                <span class="subj-name">{{ label }}</span>
                <span class="subj-val">{{ analytics.skill_radar.values[i] }}%</span>
              </div>
            </div>
            <div v-else class="chart-empty">Enroll in skills to map your proficiency.</div>
          </div>
        </div>
      </div>

      <!-- AI Insights -->
      <div class="content-card mt-3">
        <div class="card-head"><h3 style="margin:0"><ic-sparkles :size="17" /> AI-Powered Insights <span class="badge-pill" style="margin-left:8px">Personalized</span></h3></div>
        <div class="row g-3 mt-1">
          <div class="col-md-4" v-for="(insight, i) in analytics.ai_insights" :key="i">
            <div class="insight-card">
              <div style="font-size:14px;font-weight:700;margin-bottom:6px">{{ insight.title }}</div>
              <div style="font-size:13px;color:var(--text-muted);line-height:1.5">{{ insight.body }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Skill Progress Breakdown -->
      <div class="content-card mt-3" v-if="analytics.skill_progress.length">
        <div class="card-head"><h3 style="margin:0"><ic-graduation :size="17" /> Skill Progress Breakdown</h3><span class="card-sub">{{ analytics.skill_progress.length }} active skills</span></div>
        <div class="row g-3 mt-1">
          <div class="col-md-6" v-for="(sk, i) in analytics.skill_progress" :key="i">
            <div class="skill-prog-card">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-weight:700">{{ sk.title }}</span>
                <span class="skill-level-badge" :class="'lvl-' + (sk.level || '').toLowerCase()">{{ sk.level }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-muted);margin-bottom:5px">
                <span>Proficiency</span><span style="font-weight:700;color:var(--text)">{{ sk.proficiency }}%</span>
              </div>
              <div class="dash-progress-track" style="height:7px">
                <div class="dash-progress-fill" :style="{ width: sk.proficiency + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';

export default {
  name: 'AnalyticsPage',
  setup() {
    const analytics = ref(null);
    const loading = ref(true);
    const error = ref(null);
    const timeRange = ref('week');
    const dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const colors = ['#8b6dff', '#38d98a', '#f5a623', '#d56bff', '#ff6b6b', '#4dd0e1'];
    const circ = 2 * Math.PI * 50;

    function seriesColor(i) { return colors[i % colors.length]; }

    async function fetchAnalytics() {
      loading.value = true;
      try {
        const { data } = await axios.get(`/api/analytics?range=${timeRange.value}`);
        analytics.value = data;
      } catch (e) {
        error.value = 'Could not load analytics. Please try again.';
      } finally {
        loading.value = false;
      }
    }

    onMounted(fetchAnalytics);
    watch(timeRange, fetchAnalytics);

    function fmtDelta(n, suffix = '%') {
      if (n === null || n === undefined) return '';
      return (n >= 0 ? '+' : '') + n + suffix;
    }

    const statCards = computed(() => {
      const s = analytics.value?.stats ?? {};
      return [
        { icon: 'ic-clock', fg: '#9d7bff', bg: 'rgba(124,92,252,0.15)', value: (s.total_hours ?? 0) + 'h', label: 'Total Time This Week', delta: fmtDelta(s.total_hours_delta), deltaClass: (s.total_hours_delta ?? 0) >= 0 ? 'positive' : '', fill: Math.min(100, (s.total_hours ?? 0) / 20 * 100) },
        { icon: 'ic-target', fg: '#2fe39a', bg: 'rgba(56,217,138,0.15)', value: (s.quiz_accuracy ?? 0) + '%', label: 'Avg. Quiz Accuracy', delta: fmtDelta(s.quiz_accuracy_delta), deltaClass: 'positive', fill: s.quiz_accuracy ?? 0 },
        { icon: 'ic-flame', fg: '#ffb13d', bg: 'rgba(245,166,35,0.15)', value: (s.streak_days ?? 0) + ' Days', label: 'Current Streak', delta: 'Best: ' + (s.streak_best ?? 0), deltaClass: 'neutral', fill: Math.min(100, (s.streak_days ?? 0) / 14 * 100) },
        { icon: 'ic-star', fg: '#e06bff', bg: 'rgba(213,107,255,0.15)', value: (s.xp_earned ?? 0).toLocaleString(), label: 'Total XP Earned', delta: fmtDelta(s.xp_delta, ''), deltaClass: (s.xp_delta ?? 0) >= 0 ? 'positive' : '', fill: Math.min(100, (s.xp_earned ?? 0) / 3000 * 100) },
      ];
    });

    const weeklyMax = computed(() => Math.max(60, ...(analytics.value?.weekly_progress ?? [0])));
    function barHeight(v) { return Math.max(2, (v / weeklyMax.value) * 100); }
    const goalPct = computed(() => Math.min(100, (30 / weeklyMax.value) * 100));

    const hasGrowth = computed(() => (analytics.value?.skill_growth?.series ?? []).some(s => s.values?.length));

    function getPolyline(values) {
      if (!values || values.length < 2) return '';
      const allMax = Math.max(1, ...analytics.value.skill_growth.series.flatMap(s => s.values));
      const stepX = 420 / (values.length - 1);
      return values.map((v, i) => `${(i * stepX).toFixed(1)},${(190 - (v / allMax) * 180 + 10).toFixed(1)}`).join(' ');
    }

    const accDash = computed(() => ((analytics.value?.stats?.quiz_accuracy ?? 0) / 100) * circ);

    const breakdownMeta = {
      reading:       { label: 'Reading & Summaries', icon: 'ic-book',        bg: 'rgba(124,92,252,0.15)', bar: '#8b6dff' },
      flashcards:    { label: 'Flashcard Practice',  icon: 'ic-layers',      bg: 'rgba(56,217,138,0.15)',  bar: '#38d98a' },
      quizzes:       { label: 'Quiz Sessions',       icon: 'ic-check',       bg: 'rgba(245,166,35,0.15)',  bar: '#f5a623' },
      learning_path: { label: 'Learning Path',       icon: 'ic-compass',     bg: 'rgba(213,107,255,0.15)', bar: '#d56bff' },
    };
    const breakdownRows = computed(() => {
      const b = analytics.value?.time_breakdown ?? {};
      const max = Math.max(0.1, ...Object.values(b));
      return Object.keys(breakdownMeta).map(key => ({
        key, ...breakdownMeta[key], hours: b[key] ?? 0, pct: ((b[key] ?? 0) / max) * 100,
      }));
    });

    // Radar
    const radarAxes = computed(() => {
      const labels = analytics.value?.skill_radar?.labels ?? [];
      const n = labels.length;
      return labels.map((_, i) => {
        const ang = (-90 + i * 360 / n) * Math.PI / 180;
        return { x: 100 + 80 * Math.cos(ang), y: 100 + 80 * Math.sin(ang) };
      });
    });
    function radarGrid(scale) {
      const labels = analytics.value?.skill_radar?.labels ?? [];
      const n = labels.length;
      if (n < 3) return '';
      return labels.map((_, i) => {
        const ang = (-90 + i * 360 / n) * Math.PI / 180;
        return `${(100 + 80 * scale * Math.cos(ang)).toFixed(1)},${(100 + 80 * scale * Math.sin(ang)).toFixed(1)}`;
      }).join(' ');
    }
    const radarPts = computed(() => {
      const vals = analytics.value?.skill_radar?.values ?? [];
      const n = vals.length;
      if (n < 3) return '';
      return vals.map((v, i) => {
        const ang = (-90 + i * 360 / n) * Math.PI / 180;
        const r = Math.max(0, Math.min(100, v)) / 100 * 80;
        return `${(100 + r * Math.cos(ang)).toFixed(1)},${(100 + r * Math.sin(ang)).toFixed(1)}`;
      }).join(' ');
    });

    function exportData() {
      const blob = new Blob([JSON.stringify(analytics.value, null, 2)], { type: 'application/json' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'skillsprint-analytics.json';
      a.click();
    }

    return {
      analytics, loading, error, timeRange, dayLabels, circ,
      statCards, weeklyMax, barHeight, goalPct, hasGrowth, getPolyline, accDash,
      breakdownRows, radarAxes, radarGrid, radarPts, seriesColor, exportData,
    };
  },
};
</script>
