<template>
  <div class="route-view">
    <!-- Header -->
    <div class="page-header">
      <span class="badge-pill"><ic-trophy :size="14" /> Achievements & Profile</span>
      <h1 class="skills-hero" style="font-size:30px;margin:8px 0 4px">Your <span class="grad-text">Badge Showcase</span></h1>
      <p style="color:var(--text-muted);margin:0">Track your milestones, level up, and celebrate every learning win.</p>
    </div>

    <div v-if="loading" class="text-center py-4">
      <div class="skel" style="width:100%;height:140px;margin-bottom:12px"></div>
      <div class="row g-3"><div class="col-md-3" v-for="n in 8" :key="n"><div class="skel" style="height:130px"></div></div></div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

    <template v-if="data">
      <!-- Profile card -->
      <div class="content-card ach-profile mt-1">
        <div class="profile-avatar">
          {{ initial }}
          <span class="profile-level">{{ data.profile.level }}</span>
        </div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <span style="font-size:20px;font-weight:800">{{ data.profile.name }}</span>
            <span class="chip-soft"><ic-zap :size="12" /> Pro Learner</span>
            <span class="chip-soft amber"><ic-flame :size="12" /> {{ data.profile.streak }}-Day Streak</span>
          </div>
          <p style="font-size:13px;color:var(--text-muted);margin:8px 0 12px;max-width:560px">{{ data.profile.bio || 'Passionate about learning and leveling up one lesson at a time.' }}</p>
          <div class="profile-chips">
            <div class="stat-chip"><ic-star :size="15" color="#9d7bff" /> {{ data.profile.xp.toLocaleString() }} <small>Total XP</small></div>
            <div class="stat-chip"><ic-medal :size="15" color="#ffb13d" /> {{ data.profile.badges_earned }} <small>Badges Earned</small></div>
            <div class="stat-chip"><ic-bar-chart :size="15" color="#4db5ff" /> #{{ data.profile.rank }} <small>Global Rank</small></div>
            <div class="stat-chip"><ic-calendar :size="15" color="#2fe39a" /> {{ data.profile.days_active }} <small>Days Active</small></div>
          </div>
        </div>
        <div class="level-ring-card">
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px">Current Level</div>
          <div class="donut-wrap" style="width:96px;margin:0 auto">
            <svg viewBox="0 0 120 120" class="donut" style="width:96px;height:96px">
              <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="10" />
              <circle cx="60" cy="60" r="50" fill="none" stroke="url(#lvl)" stroke-width="10" stroke-linecap="round"
                      :stroke-dasharray="`${ringDash} ${ringC}`" transform="rotate(-90 60 60)" />
              <defs><linearGradient id="lvl" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7c5cfc"/><stop offset="1" stop-color="#e06bff"/></linearGradient></defs>
            </svg>
            <div class="donut-center" style="flex-direction:column"><span style="font-size:22px;font-weight:800">{{ data.profile.level }}</span><span style="font-size:10px;color:var(--text-muted)">LVL</span></div>
          </div>
          <div style="font-size:12px;text-align:center;margin-top:8px">{{ data.profile.xp.toLocaleString() }} / {{ nextThreshold.toLocaleString() }} XP</div>
          <div style="font-size:11px;color:var(--text-muted);text-align:center">{{ xpToNext }} XP to Level {{ data.profile.level + 1 }}</div>
        </div>
      </div>

      <!-- New badges banner -->
      <div v-if="showNewBanner && newBadges.length" class="content-card mt-3" style="border-color:rgba(124,92,252,0.3);background:rgba(124,92,252,0.06);display:flex;align-items:center;gap:12px">
        <span style="font-size:20px"><ic-bell :size="20" color="#9d7bff" /></span>
        <div style="flex:1">
          <div style="font-weight:700;font-size:14px;display:flex;align-items:center;gap:6px"><ic-sparkles :size="15" color="#ffb13d" /> You earned {{ newBadges.length }} new badge{{ newBadges.length > 1 ? 's' : '' }} this week!</div>
          <div style="font-size:12px;color:var(--text-muted)">{{ newBadges.map(b => b.title).join(', ') }} {{ newBadges.length > 1 ? 'are' : 'is' }} waiting for you below.</div>
        </div>
        <button class="modal-close" style="position:static" @click="showNewBanner = false"><ic-x :size="15" /></button>
      </div>

      <!-- Badge Collection -->
      <div class="content-card mt-3">
        <div class="card-head">
          <h3 style="margin:0"><ic-medal :size="17" /> Badge Collection <span class="link-muted" style="margin-left:6px">{{ data.profile.badges_earned }} / {{ data.profile.total_badges }} Earned</span></h3>
          <div class="filter-pills">
            <button :class="{ active: activeFilter === 'all' }" @click="activeFilter = 'all'">All</button>
            <button :class="{ active: activeFilter === 'earned' }" @click="activeFilter = 'earned'">Earned</button>
            <button :class="{ active: activeFilter === 'locked' }" @click="activeFilter = 'locked'">Locked</button>
          </div>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-6 col-md-3 col-lg-2-4" v-for="badge in filteredBadges" :key="badge.slug">
            <div class="badge-item" :class="{ earned: badge.earned, locked: !badge.earned }">
              <div class="badge-new" v-if="badge.is_new">NEW</div>
              <div class="badge-ic" :style="badge.earned ? { background: badgeColor(badge.slug).bg, color: badgeColor(badge.slug).fg } : {}">
                <component v-if="badge.earned" :is="badgeIcon(badge.slug)" :size="26" />
                <ic-lock v-else :size="22" />
              </div>
              <div class="badge-title">{{ badge.title }}</div>
              <div class="badge-desc">{{ badge.description }}</div>
              <div class="badge-xp" :style="badge.earned ? { color: badgeColor(badge.slug).fg } : {}">+{{ badge.xp_reward }} XP</div>
            </div>
          </div>
        </div>
      </div>

      <!-- XP History + Leaderboard -->
      <div class="row g-3 mt-1">
        <div class="col-lg-7">
          <div class="content-card">
            <div class="card-head"><div><h3 style="margin:0"><ic-history :size="17" /> XP History</h3><span class="card-sub">Recent XP earned</span></div></div>
            <div v-if="data.xp_history.length">
              <div v-for="(item, i) in data.xp_history" :key="i" class="activity-row">
                <span class="activity-ic"><component :is="activityIcon(item.event)" :size="15" /></span>
                <div style="flex:1;min-width:0">
                  <div style="font-size:13px;font-weight:600">{{ item.description }}</div>
                  <div style="font-size:11px;color:var(--text-dim)">{{ item.created_at }}</div>
                </div>
                <span style="font-size:13px;font-weight:700;color:var(--green)">+{{ item.xp }} XP</span>
              </div>
            </div>
            <p v-else style="font-size:13px;color:var(--text-muted)">No XP earned yet — start learning to build your history.</p>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="content-card">
            <div class="card-head"><div><h3 style="margin:0"><ic-trophy :size="17" /> Leaderboard</h3><span class="card-sub">Top learners this week</span></div></div>
            <div v-for="entry in data.leaderboard" :key="entry.rank" class="lb-row" :class="{ me: entry.is_current_user }">
              <span class="lb-rank" :class="{ top: entry.rank <= 3 }">{{ entry.rank }}</span>
              <span class="owner-avatar" style="width:32px;height:32px">{{ entry.name.charAt(0) }}</span>
              <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600">{{ entry.name }}</div>
                <div style="font-size:11px;color:var(--text-muted)">Level {{ entry.level }}</div>
              </div>
              <span style="font-size:13px;font-weight:700;color:var(--purple-bright)">{{ entry.xp.toLocaleString() }} XP</span>
            </div>

            <!-- current user if outside top 10 -->
            <div v-if="!currentUserInTop" class="lb-row me" style="margin-top:8px">
              <span class="lb-rank">{{ data.current_user_entry.rank }}</span>
              <span class="owner-avatar" style="width:32px;height:32px">{{ data.current_user_entry.name.charAt(0) }}</span>
              <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600">You ({{ data.current_user_entry.name.split(' ')[0] }})</div>
                <div style="font-size:11px;color:var(--text-muted)">Level {{ data.current_user_entry.level }}</div>
              </div>
              <span style="font-size:13px;font-weight:700;color:var(--purple-bright)">{{ data.current_user_entry.xp.toLocaleString() }} XP</span>
            </div>
            <div v-if="data.xp_to_next_rank > 0" class="lb-hint">You need {{ data.xp_to_next_rank.toLocaleString() }} XP to reach rank #{{ data.current_user_entry.rank - 1 }}</div>
          </div>
        </div>
      </div>

      <!-- Up Next -->
      <div class="content-card mt-3" v-if="upNext.length">
        <div class="card-head"><h3 style="margin:0"><ic-target :size="17" /> Up Next — Badges to Unlock <span class="link-muted" style="margin-left:6px">{{ lockedCount }} remaining</span></h3></div>
        <div class="row g-3 mt-1">
          <div class="col-md-4" v-for="badge in upNext" :key="badge.slug">
            <div class="upnext-card">
              <div style="display:flex;align-items:center;justify-content:space-between">
                <component :is="badgeIcon(badge.slug)" :size="20" color="#9d7bff" />
                <span style="font-size:12px;font-weight:700;color:var(--amber)">+{{ badge.xp_reward }} XP</span>
              </div>
              <div style="font-weight:700;margin:8px 0 2px">{{ badge.title }}</div>
              <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px">{{ badge.description }}</div>
              <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                <span style="color:var(--text-muted)">Progress</span>
                <span style="font-weight:700">{{ badge.progress.current }} / {{ badge.progress.target }}</span>
              </div>
              <div class="dash-progress-track" style="height:6px"><div class="dash-progress-fill" :style="{ width: (badge.progress.current / badge.progress.target * 100) + '%' }"></div></div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'AchievementsPage',
  setup() {
    const router = useRouter();
    const data = ref(null);
    const loading = ref(true);
    const error = ref(null);
    const activeFilter = ref('all');
    const showNewBanner = ref(true);
    const shared = ref(false);

    const palette = [
      { bg: 'rgba(47,227,154,0.15)',  fg: '#2fe39a' },
      { bg: 'rgba(255,177,61,0.15)',  fg: '#ffb13d' },
      { bg: 'rgba(124,92,252,0.15)',  fg: '#9d7bff' },
      { bg: 'rgba(77,181,255,0.15)',  fg: '#4db5ff' },
      { bg: 'rgba(224,107,255,0.15)', fg: '#e06bff' },
    ];
    function badgeColor(slug) {
      let h = 0;
      for (let i = 0; i < slug.length; i++) h = (h * 31 + slug.charCodeAt(i)) >>> 0;
      return palette[h % palette.length];
    }

    const initial = computed(() => (data.value?.profile?.name || '?').charAt(0));
    const earnedCount = computed(() => data.value?.badges.filter(b => b.earned).length || 0);
    const lockedCount = computed(() => data.value?.badges.filter(b => !b.earned).length || 0);
    const newBadges = computed(() => data.value?.badges.filter(b => b.is_new) || []);

    const filteredBadges = computed(() => {
      if (!data.value) return [];
      if (activeFilter.value === 'earned') return data.value.badges.filter(b => b.earned);
      if (activeFilter.value === 'locked') return data.value.badges.filter(b => !b.earned);
      return data.value.badges;
    });

    // Level ring (250 XP per level)
    const nextThreshold = computed(() => (data.value?.profile?.level ?? 1) * 250);
    const xpToNext = computed(() => Math.max(0, nextThreshold.value - (data.value?.profile?.xp ?? 0)));
    const ringC = 2 * Math.PI * 50;
    const ringDash = computed(() => {
      const xp = data.value?.profile?.xp ?? 0;
      return Math.min(1, xp / nextThreshold.value) * ringC;
    });

    const currentUserInTop = computed(() => data.value?.leaderboard.some(e => e.is_current_user) ?? false);

    const upNext = computed(() => {
      if (!data.value) return [];
      return data.value.badges
        .filter(b => !b.earned && b.progress)
        .sort((a, b) => (b.progress.current / b.progress.target) - (a.progress.current / a.progress.target))
        .slice(0, 3);
    });

    function activityIcon(event) {
      const e = (event || '').toLowerCase();
      if (e.includes('quiz')) return 'ic-check';
      if (e.includes('badge')) return 'ic-trophy';
      if (e.includes('flash')) return 'ic-layers';
      if (e.includes('lesson') || e.includes('summary')) return 'ic-book';
      return 'ic-star';
    }

    // Map each badge slug to a Lucide icon (fallback: award).
    const BADGE_ICONS = {
      'first-step': 'ic-target', 'streak-master': 'ic-flame', 'quiz-champion': 'ic-trophy',
      'night-owl': 'ic-moon', 'speed-reader': 'ic-book', 'flashcard-hero': 'ic-layers',
      'content-creator': 'ic-folder-up', 'early-bird': 'ic-sunrise', 'week-warrior': 'ic-zap',
      'ml-fundamentals': 'ic-cpu', 'python-basics': 'ic-cpu', 'quick-learner': 'ic-clock',
      'social-learner': 'ic-handshake', 'perfect-score': 'ic-target', 'fortnight-warrior': 'ic-sword',
      'ai-master': 'ic-brain', 'perfect-week': 'ic-star', 'polymath': 'ic-graduation',
      'speed-demon': 'ic-gauge', 'knowledge-vault': 'ic-library', 'deep-diver': 'ic-compass',
      'consistency-king': 'ic-crown', 'night-scholar': 'ic-bird', 'data-wizard': 'ic-bar-chart',
      'teaching-assistant': 'ic-pen', 'explorer': 'ic-compass', 'bookworm': 'ic-book',
      'challenger': 'ic-target', 'multi-linguist': 'ic-languages', 'accessibility-advocate': 'ic-accessibility',
    };
    function badgeIcon(slug) { return BADGE_ICONS[slug] || 'ic-award'; }

    function go(url) { router.push(url); }
    function shareProfile() {
      navigator.clipboard?.writeText(window.location.href).catch(() => {});
      shared.value = true;
      setTimeout(() => (shared.value = false), 2000);
    }

    onMounted(async () => {
      try {
        const res = await axios.get('/api/achievements');
        data.value = res.data;
      } catch (e) {
        error.value = 'Could not load content. Please try again.';
      } finally {
        loading.value = false;
      }
    });

    return {
      data, loading, error, activeFilter, showNewBanner, shared,
      initial, earnedCount, lockedCount, newBadges, filteredBadges,
      nextThreshold, xpToNext, ringC, ringDash, currentUserInTop, upNext,
      badgeColor, badgeIcon, activityIcon, go, shareProfile,
    };
  },
};
</script>
