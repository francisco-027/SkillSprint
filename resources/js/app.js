import './bootstrap';
import { createApp } from 'vue';
import { initGlow } from './glow';
import {
  Star, Flame, BookOpen, Target, Trophy, Award, Medal, Sparkles, Zap, Lock, Bookmark,
  Check, Clock, Brain, FileText, Link as LinkIcon, Image as ImageIcon, Type, ListChecks,
  Network, Bell, Volume2, Accessibility, Palette, ShieldCheck, User, TrendingUp, Activity,
  Calendar, BarChart3, LineChart, PieChart, Radar, GraduationCap, Compass, Globe, Hand,
  Rocket, History, WandSparkles, Play, X, Layers, Cpu, Moon, Sunrise, Crown, Feather,
  Settings as SettingsIcon, Bird, Sword, PenLine, Handshake, Gauge, Hourglass, Library,
  Repeat, FolderUp, Languages, Download, Eye, ChevronDown, PartyPopper,
} from 'lucide-vue-next';

import App         from './App.vue';
import router      from './router';
import AppLayout    from './components/AppLayout.vue';
import FlashcardDeck from './components/FlashcardDeck.vue';
import Onboarding   from './pages/Onboarding.vue';
import QuickStart   from './pages/QuickStart.vue';

// Lucide icons — registered globally as <ic-name> for use in any template.
const icons = {
  'ic-star': Star, 'ic-flame': Flame, 'ic-book': BookOpen, 'ic-target': Target,
  'ic-trophy': Trophy, 'ic-award': Award, 'ic-medal': Medal, 'ic-sparkles': Sparkles,
  'ic-zap': Zap, 'ic-lock': Lock, 'ic-bookmark': Bookmark, 'ic-check': Check,
  'ic-clock': Clock, 'ic-brain': Brain, 'ic-file': FileText, 'ic-link': LinkIcon,
  'ic-image': ImageIcon, 'ic-type': Type, 'ic-list-checks': ListChecks, 'ic-network': Network,
  'ic-bell': Bell, 'ic-volume': Volume2, 'ic-accessibility': Accessibility, 'ic-palette': Palette,
  'ic-shield': ShieldCheck, 'ic-user': User, 'ic-trending-up': TrendingUp, 'ic-activity': Activity,
  'ic-calendar': Calendar, 'ic-bar-chart': BarChart3, 'ic-line-chart': LineChart,
  'ic-pie-chart': PieChart, 'ic-radar': Radar, 'ic-graduation': GraduationCap, 'ic-compass': Compass,
  'ic-globe': Globe, 'ic-hand': Hand, 'ic-rocket': Rocket, 'ic-history': History,
  'ic-wand': WandSparkles, 'ic-play': Play, 'ic-x': X, 'ic-layers': Layers, 'ic-cpu': Cpu,
  'ic-moon': Moon, 'ic-sunrise': Sunrise, 'ic-crown': Crown, 'ic-feather': Feather,
  'ic-settings': SettingsIcon, 'ic-bird': Bird, 'ic-sword': Sword, 'ic-pen': PenLine,
  'ic-handshake': Handshake, 'ic-gauge': Gauge, 'ic-hourglass': Hourglass, 'ic-library': Library,
  'ic-repeat': Repeat, 'ic-folder-up': FolderUp, 'ic-languages': Languages,
  'ic-download': Download, 'ic-eye': Eye, 'ic-chevron-down': ChevronDown, 'ic-party': PartyPopper,
};
// Register globals (icons + shared components) on any app instance.
function registerGlobals(app) {
  Object.entries(icons).forEach(([name, comp]) => app.component(name, comp));
  app.component('app-layout',     AppLayout);
  app.component('flashcard-deck', FlashcardDeck);
}

const mountEl = document.getElementById('app');

if (mountEl) {
  if (mountEl.hasAttribute('data-spa')) {
    // Main authenticated app — single-page app with client-side routing.
    const app = createApp(App);
    registerGlobals(app);
    app.use(router);
    app.mount('#app');
  } else {
    // Standalone full-screen pages (onboarding, quick start) mount their
    // component tag in place — no router.
    const app = createApp({});
    registerGlobals(app);
    app.component('onboarding-page',  Onboarding);
    app.component('quick-start-page', QuickStart);
    app.mount('#app');
  }
}

initGlow();