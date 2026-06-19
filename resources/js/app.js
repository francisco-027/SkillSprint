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

import AppLayout    from './components/AppLayout.vue';
import FlashcardDeck from './components/FlashcardDeck.vue';
import Onboarding   from './pages/Onboarding.vue';
import QuickStart   from './pages/QuickStart.vue';
import Skills       from './pages/Skills.vue';
import Dashboard    from './pages/Dashboard.vue';
import Upload       from './pages/Upload.vue';
import MaterialDetail from './pages/MaterialDetail.vue';
import Summary      from './pages/Summary.vue';
import Flashcards   from './pages/Flashcards.vue';
import Quiz         from './pages/Quiz.vue';
import QuizResults  from './pages/QuizResults.vue';
import Analytics    from './pages/Analytics.vue';
import Achievements from './pages/Achievements.vue';
import Settings     from './pages/Settings.vue';

const app = createApp({});

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
Object.entries(icons).forEach(([name, comp]) => app.component(name, comp));

app.component('app-layout',          AppLayout);
app.component('flashcard-deck',      FlashcardDeck);
app.component('onboarding-page',     Onboarding);
app.component('quick-start-page',    QuickStart);
app.component('skills-page',         Skills);
app.component('dashboard-page',      Dashboard);
app.component('upload-page',         Upload);
app.component('material-detail-page', MaterialDetail);
app.component('summary-page',        Summary);
app.component('flashcards-page',     Flashcards);
app.component('quiz-page',           Quiz);
app.component('quiz-results-page',   QuizResults);
app.component('analytics-page',      Analytics);
app.component('achievements-page',   Achievements);
app.component('settings-page',       Settings);

app.mount('#app');

initGlow();