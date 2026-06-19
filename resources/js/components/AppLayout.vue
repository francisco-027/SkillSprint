<template>
  <div class="app-shell">
    <aside class="app-sidebar">
      <div class="sidebar-brand">
        <div class="sidebar-brand-icon">S</div>
        <div class="sidebar-brand-name">Skill<span>Sprint</span></div>
      </div>

      <div class="sidebar-user">
        <div class="sidebar-user-avatar">
          {{ loading ? '...' : (user?.name?.charAt(0) ?? '?') }}
        </div>
        <div class="sidebar-user-info">
          <div class="name">{{ loading ? 'Loading...' : user?.name }}</div>
          <div class="level">Lvl {{ loading ? '?' : user?.level }}</div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <router-link v-for="item in navItems" :key="item.key"
           :to="item.href"
           class="sidebar-nav-item"
           :class="{ active: activeKey === item.key }"
           v-html="item.icon + '<span>' + item.label + '</span>'">
        </router-link>
      </nav>

      <div class="sidebar-goal">
        <div class="sidebar-goal-label">Daily Goal</div>
        <div class="sidebar-goal-track">
          <div class="sidebar-goal-fill"
               :style="{ width: loading ? '0%' : Math.min(100, (user?.streak_current / 7) * 100) + '%' }">
          </div>
        </div>
        <div class="sidebar-goal-text">
          {{ loading ? '...' : user?.streak_current + '/' + user?.daily_goal_minutes + ' min' }}
        </div>
      </div>
    </aside>

    <div class="app-main">
      <header class="app-topbar">
        <div class="topbar-left">
          <!-- Mobile: brand (sidebar is hidden on phones) -->
          <router-link to="/home" class="topbar-brand">
            <span class="topbar-brand-icon">S</span>
            <span class="topbar-brand-name">Skill<span>Sprint</span></span>
          </router-link>
          <!-- Desktop: page-provided back button -->
          <router-link v-if="backHref" :to="backHref" class="topbar-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>{{ backLabel }}</span>
          </router-link>
        </div>
        <div class="topbar-right">
          <span class="xp-pill" v-if="!loading">
            XP {{ user?.xp_total }}
          </span>
          <span class="streak-pill" v-if="!loading">
            {{ user?.streak_current }} days
          </span>
          <div class="topbar-avatar-wrap" ref="avatarWrap">
            <button
              type="button"
              class="topbar-avatar"
              aria-label="Account menu"
              :aria-expanded="menuOpen"
              @click="menuOpen = !menuOpen"
            >
              {{ user?.name?.charAt(0) ?? '?' }}
            </button>

            <div v-if="menuOpen" class="avatar-menu">
              <div class="avatar-menu-header">
                <div class="avatar-menu-name">{{ user?.name }}</div>
                <div class="avatar-menu-email">{{ user?.email }}</div>
              </div>
              <router-link to="/settings" class="avatar-menu-item" @click="menuOpen = false">Settings</router-link>
              <button type="button" class="avatar-menu-item logout" @click="logout">Log out</button>
            </div>
          </div>
        </div>
      </header>

      <div class="page-content">
        <slot />
      </div>

      <footer class="app-footer">
        <span>2026 SkillSprint. All rights reserved.</span>
        <span>WCAG 2.2 AA Compliant</span>
      </footer>
    </div>

    <!-- Mobile bottom tab bar (shown only on small screens via CSS) -->
    <nav class="app-bottom-nav">
      <router-link v-for="item in bottomNavItems" :key="item.key"
         :to="item.href"
         class="bottom-nav-item"
         :class="{ active: activeKey === item.key }">
        <span class="bottom-nav-ic" v-html="item.icon"></span>
        <span class="bottom-nav-label">{{ item.shortLabel }}</span>
      </router-link>
    </nav>
  </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { applyPreferences } from '../applyPreferences';

export default {
  name: 'AppLayout',
  setup() {
    const route = useRoute();
    const activeKey = computed(() => route.meta.active ?? '');
    const backHref = computed(() => route.meta.backHref ?? '');
    const backLabel = computed(() => route.meta.backLabel ?? 'Back');

    const user = ref(null);
    const loading = ref(true);
    const menuOpen = ref(false);
    const avatarWrap = ref(null);

    const closeOnOutside = (e) => {
      if (menuOpen.value && avatarWrap.value && !avatarWrap.value.contains(e.target)) {
        menuOpen.value = false;
      }
    };

    const logout = async () => {
      try {
        await axios.post('/logout');
      } catch (e) {
        // ignore — redirect to landing regardless
      }
      window.location.href = '/';
    };

    const navItems = [
      { key: 'dashboard',  label: 'Dashboard',        shortLabel: 'Home',      href: '/home',            icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>' },
      { key: 'skills',     label: 'Skills Library',   shortLabel: 'Skills',    href: '/skills',          icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>' },
      { key: 'upload',     label: 'My Materials',      shortLabel: 'Materials', href: '/upload',          icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>' },
      { key: 'analytics',  label: 'Analytics',         shortLabel: 'Stats',     href: '/analytics',       icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>' },
      { key: 'achievements',label: 'Achievements',     shortLabel: 'Badges',    href: '/achievements',    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>' },
      { key: 'settings',   label: 'Settings',          shortLabel: 'Settings',  href: '/settings',        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>' },
    ];

    // The five primary destinations for the mobile bottom bar (Settings lives in the avatar menu).
    const bottomNavItems = navItems.filter((i) => i.key !== 'settings');

    onMounted(async () => {
      try {
        const { data } = await axios.get('/api/user');
        user.value = data;
      } catch (e) {
        console.error('Failed to load user', e);
      } finally {
        loading.value = false;
      }

      try {
        const { data } = await axios.get('/api/user/preferences');
        applyPreferences(data);
      } catch (e) {
        // Preferences are cosmetic; silently fail
      }

      document.addEventListener('click', closeOnOutside);
    });

    onBeforeUnmount(() => {
      document.removeEventListener('click', closeOnOutside);
    });

    return { user, loading, navItems, bottomNavItems, menuOpen, avatarWrap, logout, activeKey, backHref, backLabel };
  },
};
</script>