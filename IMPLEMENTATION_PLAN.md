# SkillSprint — Frontend Implementation Plan

> **Scope:** Generate all 12 Vue 3 SFC page components + shared layout shell + pages CSS,
> backed from day one by real database records seeded before any page is written.
> Pages go in `resources/js/pages/`. Shared components go in `resources/js/components/`.
> Do NOT touch `.env`, anything under `app/Filament/`, or run `php artisan migrate` manually
> outside the steps described below.

---

## Table of Contents

1. [What We're Building](#1-what-were-building)
2. [CSS Strategy](#2-css-strategy)
3. [Data Strategy — Seeders over Mock Data](#3-data-strategy--seeders-over-mock-data)
4. [Seeder Specifications](#4-seeder-specifications)
5. [API Endpoints Required](#5-api-endpoints-required)
6. [Shared Layout — AppLayout.vue](#6-shared-layout--applayoutvue)
7. [Page-by-Page Spec](#7-page-by-page-spec)
8. [File Tree After Generation](#8-file-tree-after-generation)
9. [app.js Changes](#9-appjs-changes)
10. [Blade View Wiring](#10-blade-view-wiring)
11. [Generation Order](#11-generation-order)
12. [Post-Generation Roadmap](#12-post-generation-roadmap)

---

## 1. What We're Building

| # | File | Route | Auth | Ref Image |
|---|------|-------|------|-----------|
| — | `components/AppLayout.vue` | (shared shell) | Yes | All authenticated screens |
| 1 | `pages/Onboarding.vue` | `/onboarding` | Yes | `185609991` |
| 2 | `pages/QuickStart.vue` | `/quick-start` | No | `185614782` |
| 3 | `pages/Skills.vue` | `/skills` | Yes | `185621730` |
| 4 | `pages/Dashboard.vue` | `/home` | Yes | `185627489` |
| 5 | `pages/Upload.vue` | `/upload` | Yes | `185632484` |
| 6 | `pages/Summary.vue` | `/summaries/{id}` | Yes | `185709016` |
| 7 | `pages/Flashcards.vue` | `/flashcards/{deckId}` | Yes | `185640802` |
| 8 | `pages/Quiz.vue` | `/quizzes/{quizId}` | Yes | `185744035` |
| 9 | `pages/QuizResults.vue` | `/quizzes/{quizId}/results` | Yes | `185749194` |
| 10 | `pages/Analytics.vue` | `/analytics` | Yes | `185753784` |
| 11 | `pages/Achievements.vue` | `/achievements` | Yes | `185759335` |
| 12 | `pages/Settings.vue` | `/settings` | Yes | `185804304` |

---

## 2. CSS Strategy

**Do not duplicate variables.** All design tokens live in `resources/css/landing.css`.
Create one new file that builds on top of them:

### `resources/css/pages.css`

```css
/* Pull in all existing tokens — never redefine them here */
@import './landing.css';

/* App shell */
.app-shell { ... }
.app-sidebar { ... }         /* fixed left nav, 210px */
.sidebar-nav-item { ... }
.sidebar-nav-item.active { ... }
.app-main { ... }            /* flex-1 scrollable */
.app-topbar { ... }
.page-header { ... }

/* Reusable content components */
.stat-card { ... }           /* 4-up metric cards */
.content-card { ... }        /* general bordered dark card */
.skill-card { ... }          /* skill library card */
.upload-tab-btn { ... }
.flip-card { ... }           /* 3D flashcard */
.flip-card-inner { ... }
.flip-card-front { ... }
.flip-card-back { ... }
.quiz-option { ... }         /* MCQ answer row — hover/selected/correct/wrong states */
.badge-item { ... }          /* achievement badge — locked/earned/new states */
.settings-category { ... }
.toggle-switch { ... }       /* custom iOS-style toggle */
```

Add `pages.css` to `vite.config.js` input array:

```js
// vite.config.js
input: [
    'resources/sass/app.scss',
    'resources/css/landing.css',
    'resources/css/auth.css',
    'resources/css/pages.css',   // ← add this
    'resources/js/app.js',
],
```

---

## 3. Data Strategy — Seeders over Mock Data

### The rule

**No content arrays ever live inside a Vue component.**

The distinction is strict:

| Belongs in a Vue component `ref` | Belongs in the database + seeded |
|---|---|
| `isFlipped` — is the card showing its back? | Flashcard question and answer text |
| `selectedOption` — which MCQ choice did the user click? | Quiz questions, options, correct answers |
| `activeTab` — which input tab is open? | Skill titles, descriptions, tags, learner counts |
| `search` — current search box value | Badge names, icons, XP rewards, criteria |
| `activeCategory` — which filter tab? | Summary sections, key terms, difficulty |
| `showNewBanner` — is the "new badge" banner visible? | User XP, streak, level, activity log |
| `loading` — is the API call in flight? | Analytics figures, time breakdowns |

The left column is **UI state** — it only exists for the duration of a user's session
and the database has no opinion about it.
The right column is **content** — it must exist in the database, retrieved via API.

### How it works in practice

Every page component fetches its data in `onMounted()`:

```js
// ✅ correct — component holds only UI state
const skills = ref([])
const loading = ref(true)

onMounted(async () => {
    const { data } = await axios.get('/api/skills')
    skills.value = data
    loading.value = false
})
```

The "sample data" that would otherwise be hardcoded in the component
instead lives in a seeder, loaded into the database once before any
page is generated. This way, the component is correct from the first
line written — there is no second pass to "replace mock data with real data."

### The one acceptable exception

Static configuration that will never come from the database and never changes
is fine to keep in the component:

```js
// Fine to hardcode — UI config, not content
const categories = ['All Skills', 'Technology', 'Science', 'Humanities', 'Business', 'Health']
const difficultyLevels = ['Beginner', 'Intermediate', 'Advanced']
const outputTypes = ['AI Summary', 'Flashcards', 'Quiz', 'Mind Map']
const inputModes = ['Paste Text', 'PDF / DOCX', 'Image (OCR)', 'URL / Link']
```

---

## 4. Seeder Specifications

Run migrations and seeders **before generating any Vue file**.

```bash
php artisan migrate
php artisan db:seed
```

Create these seeder files in `database/seeders/`:

---

### `DatabaseSeeder.php` (update existing)

```php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        UserPreferenceSeeder::class,
        SkillSeeder::class,
        BadgeSeeder::class,
        SummarySeeder::class,
        FlashcardSeeder::class,
        QuizSeeder::class,
        ActivitySeeder::class,
    ]);
}
```

---

### `UserSeeder.php`

Creates one demo user matching the reference images exactly.
All pages are built and tested against this user.

```php
User::create([
    'name'               => 'Alex Rivera',
    'email'              => 'alex@skillsprint.dev',
    'password'           => bcrypt('password'),
    'avatar'             => null,
    'bio'                => 'Passionate about AI, data science, and continuous learning. Currently mastering machine learning fundamentals.',
    'xp_total'           => 2545,
    'level'              => 12,
    'daily_goal_minutes' => 30,
    'streak_current'     => 7,
    'streak_best'        => 14,
    'onboarding_completed_at' => now(),
    'last_active_at'     => now(),
]);
```

---

### `UserPreferenceSeeder.php`

Inserts a `user_preferences` row for the demo user with all defaults.
Mirrors every field in PROJECT_ROADMAP.md §5 `user_preferences` table.

```php
UserPreference::create([
    'user_id'             => 1,
    // Accessibility
    'dyslexia_font'       => false,
    'font_size'           => 16,
    'letter_spacing'      => 0,
    'line_height'         => 1.5,
    'word_spacing'        => 0,
    // Visuals
    'high_contrast'       => false,
    'contrast_theme'      => 'default',
    'bold_text'           => false,
    'focus_indicators'    => true,
    // Audio
    'tts_enabled'         => true,
    'tts_speed'           => 1.0,
    'auto_read_cards'     => false,
    'auto_read_questions' => false,
    // Motion
    'reduce_motion'       => false,
    'slow_flip_speed'     => false,
    // Learning
    'learning_goals'      => json_encode(['career_advancement', 'certification_prep']),
    'learning_pace'       => 'regular',
    'difficulty_default'  => 'beginner',
    // Localization
    'output_language'     => 'English',
    'simplify_language'   => true,
    // Extras
    'visual_diagrams'     => true,
    'notifications'       => true,
    'offline_mode'        => true,
]);
```

---

### `SkillSeeder.php`

Seeds all 18 skills visible in the Skill Library reference image.
Each row is a complete record matching the `skills` table schema.

| title | category | level | estimated_minutes | learner_count | is_popular |
|---|---|---|---|---|---|
| Python Programming | Technology | Beginner | 45 | 12400 | true |
| Machine Learning Basics | Technology | Intermediate | 60 | 8900 | true |
| Photosynthesis | Science | Beginner | 25 | 5200 | false |
| Climate Change Essentials | Science | Beginner | 35 | 9700 | true |
| Stoicism Philosophy | Humanities | Beginner | 30 | 4100 | false |
| World War II Overview | Humanities | Intermediate | 50 | 6300 | false |
| Digital Marketing 101 | Business | Beginner | 40 | 11200 | true |
| Personal Finance Basics | Business | Beginner | 35 | 14500 | true |
| Nutrition Fundamentals | Health | Beginner | 30 | 7800 | false |
| React.js Fundamentals | Technology | Intermediate | 55 | 10100 | true |
| Data Visualization | Technology | Intermediate | 45 | 5600 | false |
| Leadership Essentials | Business | Intermediate | 40 | 900 | false |
| Genetics & DNA | Science | Advanced | 60 | 3400 | false |
| Mindfulness & Meditation | Health | Beginner | 20 | 13200 | true |
| SQL for Beginners | Technology | Beginner | 40 | 8100 | false |
| Economics Fundamentals | Business | Intermediate | 50 | 4800 | false |
| Cybersecurity Basics | Technology | Intermediate | 45 | 9200 | true |
| Human Anatomy 101 | Science | Intermediate | 55 | 3100 | false |

Also seed a `tags` JSON column for each skill (2–3 tags per skill matching the ref image).

---

### `BadgeSeeder.php`

Seeds all 32 badges. 14 are marked as earned by the demo user
via a `user_badges` pivot table (with `earned_at` timestamp).
3 of the 14 earned badges are flagged `is_new = true`.

**Earned badges (14):**

| slug | title | description | icon | xp_reward | is_new |
|---|---|---|---|---|---|
| first-step | First Step | Completed your first lesson | 🎯 | 50 | false |
| streak-master | Streak Master | 7-day learning streak | 🔥 | 150 | true |
| quiz-champion | Quiz Champion | Score 90%+ on 5 quizzes | 🏆 | 200 | true |
| night-owl | Night Owl | Study after 10 PM | 🌙 | 75 | true |
| speed-reader | Speed Reader | Read 10 summaries | 📚 | 100 | false |
| flashcard-hero | Flashcard Hero | Reviewed 100 flashcards | 🃏 | 120 | false |
| content-creator | Content Creator | Uploaded 5 materials | 📤 | 80 | false |
| early-bird | Early Bird | Study before 8 AM | 🌅 | 75 | false |
| week-warrior | Week Warrior | 7 consecutive daily goals | ⚡ | 100 | false |
| ml-fundamentals | ML Fundamentals | Complete ML learning path | 🤖 | 200 | false |
| python-basics | Python Basics | Complete Python path | 🐍 | 150 | false |
| quick-learner | Quick Learner | Finish a quiz in under 2 min | ⏱️ | 60 | false |
| social-learner | Social Learner | Share 3 results | 🤝 | 50 | false |
| perfect-score | Perfect Score | 100% on any quiz | 💯 | 250 | false |

**Locked badges (18):** fortnight-warrior, ai-master, perfect-week, polymath,
speed-demon, knowledge-vault, deep-diver, consistency-king, night-scholar,
data-wizard, teaching-assistant, explorer, bookworm, challenger,
multi-linguist, accessibility-advocate, community-builder, legend.
Each has a description, icon, XP reward, and JSON `criteria` field.

---

### `SummarySeeder.php`

Seeds 1 complete summary: **Machine Learning Basics**
(the subject used across all the reference images).

```php
Summary::create([
    'user_id'            => 1,
    'upload_id'          => 1,
    'title'              => 'Machine Learning Basics',
    'difficulty'         => 'beginner',
    'estimated_minutes'  => 25,
    'source_filename'    => 'ML_Chapter3_Notes.pdf',
    'content_sections'   => json_encode([
        [
            'number'      => '01',
            'title'       => 'What is Machine Learning?',
            'tag'         => 'Core Concept',
            'body'        => 'Machine learning is a branch of artificial intelligence that enables systems to learn from data and improve their accuracy over time — without being explicitly programmed for every task.',
            'read_minutes' => 3,
        ],
        [
            'number'      => '02',
            'title'       => 'The Three Learning Types',
            'tag'         => 'Classification',
            'body'        => 'There are three fundamental paradigms in machine learning, each suited to different types of problems and data availability.',
            'subtypes'    => [
                ['label' => 'Supervised',   'desc' => 'Learns from labeled training data to predict outcomes.'],
                ['label' => 'Unsupervised', 'desc' => 'Finds hidden patterns in unlabeled data independently.'],
                ['label' => 'Reinforcement','desc' => 'Learns by trial and reward signals from an environment.'],
            ],
            'read_minutes' => 6,
        ],
        [
            'number'      => '03',
            'title'       => 'Real-World Applications',
            'tag'         => 'Application',
            'body'        => 'Machine learning powers many everyday technologies we use. Key application domains include:',
            'tags'        => ['Chatbots & NLP','Computer Vision','Fraud Detection','Recommendations','Healthcare AI','Self-Driving Cars'],
            'read_minutes' => 4,
        ],
        [
            'number'      => '04',
            'title'       => 'How Algorithms Learn',
            'tag'         => 'Process',
            'body'        => 'Algorithms learn by processing training data, identifying statistical patterns, and iteratively adjusting internal parameters to minimize prediction errors.',
            'analogy'     => 'Think of it like learning to ride a bike — you make mistakes, get feedback, and gradually improve until it becomes natural.',
            'read_minutes' => 6,
        ],
        [
            'number'      => '05',
            'title'       => 'Data: The Fuel of ML',
            'tag'         => 'Foundation',
            'body'        => 'Data quality and quantity are the most critical factors in ML success. Models trained on biased, incomplete, or insufficient data will produce unreliable results — a concept known as "garbage in, garbage out."',
            'read_minutes' => 4,
        ],
    ]),
    'key_terms' => json_encode([
        'Algorithm','Neural Network','Overfitting','Feature',
        'Gradient Descent','Classification','Regression',
        'Training Data','Clustering',
    ]),
    'timeline_steps' => json_encode([
        ['step' => 1, 'title' => 'Data Collection',    'desc' => 'Gather labeled training data'],
        ['step' => 2, 'title' => 'Preprocessing',      'desc' => 'Clean and normalize the dataset'],
        ['step' => 3, 'title' => 'Model Selection',    'desc' => 'Choose the right algorithm'],
        ['step' => 4, 'title' => 'Training',           'desc' => 'Run the optimization loop'],
        ['step' => 5, 'title' => 'Evaluation',         'desc' => 'Measure accuracy on test data'],
        ['step' => 6, 'title' => 'Deployment',         'desc' => 'Serve predictions in production'],
    ]),
]);
```

---

### `FlashcardSeeder.php`

Seeds 12 flashcards tied to the ML Basics summary.
Matches the card list visible in the Flashcards reference image exactly.

| # | question | answer | category |
|---|---|---|---|
| 1 | What is Machine Learning? | A branch of AI that enables systems to learn from data without explicit programming. | Core Concept |
| 2 | AI vs Machine Learning | AI is the broad field; ML is a subset that learns from data patterns automatically. | Core Concept |
| 3 | Training Data Definition | The labeled dataset used to teach an ML model to recognize patterns. | Definition |
| 4 | Types of ML Algorithms | Supervised, Unsupervised, and Reinforcement Learning. | Classification |
| 5 | What is Supervised Learning? | Learning from labeled data where each input is paired with the correct output. | Core Concept |
| 6 | Unsupervised Learning | Finds hidden patterns in unlabeled data without guidance. | Core Concept |
| 7 | Neural Networks Basics | Layers of connected nodes that learn feature representations from raw data. | Architecture |
| 8 | Gradient Descent | An optimization algorithm that minimizes error by adjusting model parameters iteratively. | Algorithm |
| 9 | Overfitting vs Underfitting | Overfitting: too specific to training data. Underfitting: too general, poor accuracy. | Problem |
| 10 | Model Evaluation Metrics | Accuracy, Precision, Recall, F1 Score, AUC-ROC. | Evaluation |
| 11 | Reinforcement Learning | An agent learns by receiving rewards and penalties through interaction with an environment. | Core Concept |
| 12 | Real-World ML Applications | Chatbots, fraud detection, recommendations, self-driving cars, healthcare AI. | Application |

Each card also stores a `status` field: `unseen` by default.
A `user_flashcard_progress` pivot tracks per-user status (`unseen/current/saved/mastered`).

---

### `QuizSeeder.php`

Seeds 1 quiz with 5 questions tied to the ML Basics summary.
Matches the Quiz and QuizResults reference images exactly.

```
Quiz: Machine Learning Quiz
mode: adaptive | difficulty: beginner | question_count: 5
```

**Questions:**

| # | body | correct | difficulty |
|---|---|---|---|
| 1 | What is Machine Learning? | A branch of AI that enables systems to learn from data | Easy |
| 2 | Which of the following best describes Supervised Learning? | The algorithm is trained on labeled data where each input is paired with the correct output. | Medium |
| 3 | How many main types of ML algorithms exist? | Three — Supervised, Unsupervised, and Reinforcement | Easy |
| 4 | What is Gradient Descent primarily used for? | Minimizing the error by adjusting model parameters | Hard |
| 5 | Which term describes a model that overfits training data? | Overfitting | Medium |

Each question row also stores:
- `options` JSON (4 options including distractors)
- `explanation` (shown in QuizResults review)
- `xp_reward` (15–20 XP each)
- `type` (concept label used in results e.g. "Foundation · Easy")

---

### `ActivitySeeder.php`

Seeds recent activity log entries for the demo user.
Used by the Dashboard "Recent Activity" panel and Achievements "XP History" list.

| event | description | xp | created_at |
|---|---|---|---|
| lesson_complete | Completed: Neural Networks Intro | +80 | 2 hours ago |
| quiz_complete | Quiz: SEO Fundamentals — 90% | +46 | Yesterday |
| badge_earned | Badge Earned: Week Warrior 🏆 | +100 | 2 days ago |
| flashcard_session | Flashcards: Python Basics · 82% accuracy | +46 | 3 days ago |
| badge_earned | Streak Master Badge Earned — 7-day milestone | +150 | 1 day ago |
| badge_earned | Quiz Champion Badge Earned — Scored 90%+ on 5 quizzes | +200 | 1 day ago |
| quiz_complete | ML Fundamentals Quiz — 88% · 12 questions | +88 | Yesterday |
| summary_read | AI Summary — Neural Networks · Read & completed | +30 | 4 days ago |

---

## 5. API Endpoints Required

Create stub controllers that return the seeded database records as JSON.
These must exist before the Vue pages are written — the pages call them from `onMounted`.

```
GET  /api/user                          → auth user + xp + streak + level + daily goal
GET  /api/user/preferences              → user_preferences row
PUT  /api/user/preferences              → update preferences

GET  /api/skills                        → all skills (supports ?search=&category=&level=&sort=)
GET  /api/skills/{id}                   → single skill

GET  /api/dashboard                     → stat cards + continue learning + recommendations + daily challenge + recent activity
GET  /api/uploads/recent                → last 3 uploads for the sidebar

GET  /api/summaries/{id}               → full summary with sections + key_terms + timeline
GET  /api/flashcards/{deckId}          → deck meta + all 12 cards + user's per-card status
GET  /api/quizzes/{quizId}             → quiz meta + questions (options only, no correct answer)
POST /api/quizzes/{quizId}/submit       → submit all answers → returns result + XP + badges
GET  /api/quizzes/{quizId}/results      → full result with per-question breakdown

GET  /api/analytics                     → all chart data (supports ?range=week|month|all)
GET  /api/achievements                  → badges (earned + locked) + xp history + leaderboard
```

Each controller method at this stage can simply return the seeded data:

```php
// Example — SkillController.php
public function index(Request $request)
{
    $skills = Skill::query()
        ->when($request->search,   fn($q) => $q->where('title', 'like', "%{$request->search}%"))
        ->when($request->category, fn($q) => $q->where('category', $request->category))
        ->when($request->level,    fn($q) => $q->where('level', $request->level))
        ->orderBy($request->sort === 'newest' ? 'created_at' : 'learner_count', 'desc')
        ->get();

    return response()->json($skills);
}
```

---

## 6. Shared Layout — `components/AppLayout.vue`

The wrapper every authenticated page uses.
Fetches the current user from `/api/user` once on mount
so the sidebar always shows live data (XP, streak, daily goal).

**Structure:**
```
<div class="app-shell">
  <aside class="app-sidebar">
    <div class="sidebar-brand">   ← SkillSprint logo </div>
    <div class="sidebar-user">    ← avatar, name, level badge (from API) </div>
    <nav class="sidebar-nav">
      Dashboard / Learning Path / Upload Materials /
      Flashcards / Quizzes / Analytics / Achievements / Settings
    </nav>
    <div class="sidebar-goal">    ← Daily Goal bar (from API) </div>
  </aside>

  <div class="app-main">
    <header class="app-topbar">
      <div class="topbar-left">  ← breadcrumb slot </div>
      <div class="topbar-right"> ← XP pill, Streak pill, avatar (from API) </div>
    </header>
    <div class="page-content">
      <slot />
    </div>
    <footer class="app-footer"> ← same footer as landing page </footer>
  </div>
</div>
```

**Props:**
```js
props: {
    activePage: String   // 'dashboard' | 'skills' | 'upload' | etc.
}
```

**Script:**
```js
const user = ref(null)
const loading = ref(true)

onMounted(async () => {
    const { data } = await axios.get('/api/user')
    user.value = data
    loading.value = false
})
```

The sidebar goal bar, XP pill, streak pill, and user name/level all bind to `user`.
Show skeleton placeholders while `loading` is true.

---

## 7. Page-by-Page Spec

The script section of every page follows the same pattern:

```js
// Data that comes from the database
const [resource] = ref([])       // populated in onMounted
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/[endpoint]')
        [resource].value = data
    } catch (e) {
        error.value = 'Could not load content. Please try again.'
    } finally {
        loading.value = false
    }
})

// UI state only — no content, no data
const [uiState] = ref(...)
```

Only UI state deviations are noted per page below.

---

### 7.1 `pages/Onboarding.vue`
**Route:** `/onboarding` | **Layout:** guest split (no sidebar)

**Left column:**
- Step badge: "Step 2 of 4 — Personalize Your Experience"
- Headline: "Let's Make Learning Work **For You**" (gradient on last line)
- Vertical step list (4 steps): each a card, active one highlighted
- "Inclusive by Design" info box

**Right column (wizard card):**
- Progress bar + dot indicators
- Step 1: goal selection grid (2×3) — goal labels are static config, not DB content
- Step 2: learning pace cards (Casual 15 min / Regular 30 min / Intensive 60 min)
- Step 3: accessibility toggles
- Step 4: celebration + "Go to Dashboard →"
- "Continue →" and "Skip onboarding →"

**UI state refs:**
```js
const currentStep = ref(1)
const selectedGoals = ref([])
const selectedPace = ref('regular')
const a11yDefaults = reactive({ dyslexia_font: false, high_contrast: false, tts_enabled: false, simplify_language: false })
```

**On complete:** `POST /api/user/preferences` with the collected values, then redirect.

**No API fetch on mount** — this page collects data, it does not display it.

---

### 7.2 `pages/QuickStart.vue`
**Route:** `/quick-start` | **Layout:** guest split (no sidebar)

**Left column:** "Fast Track" badge, headline, ~3 min info card,
"What You'll Get" list (3 items), "Safe & Private" box.

**Right column:** "Quick Start Learning Path" card.
- 3 mode tabs: Upload File | Sample Topics | Paste Text
- Upload File tab: drag-and-drop zone (PDF DOCX TXT, Max 10MB)
- Sample Topics tab: fetches `/api/skills?is_featured=1` for the grid of sample topic cards
- Paste Text tab: textarea
- "Generate →" button → `POST /api/uploads` → redirects to `/summaries/{id}`

**UI state refs:**
```js
const activeTab = ref('upload')
const pastedText = ref('')
const dragOver = ref(false)
const selectedFile = ref(null)
const generating = ref(false)
```

---

### 7.3 `pages/Skills.vue`
**Route:** `/skills` | **Layout:** two-column page layout (no app sidebar)

**Left panel (sticky):** Skill Selection header, Selected Skills box (empty state or up to 5 chips), Quick Tips list.

**Main area:** breadcrumb, headline, search bar, level filter, category tabs, skill grid.

**Data fetch:**
```js
const skills = ref([])
onMounted(async () => {
    const { data } = await axios.get('/api/skills')
    skills.value = data
})
```

**UI state refs:**
```js
const selectedSkills = ref([])   // max 5
const search = ref('')
const activeCategory = ref('All Skills')
const activeLevel = ref('All Levels')
const sortBy = ref('Most Popular')

const filteredSkills = computed(() =>
    skills.value
        .filter(s => !search.value || s.title.toLowerCase().includes(search.value.toLowerCase()))
        .filter(s => activeCategory.value === 'All Skills' || s.category === activeCategory.value)
        .filter(s => activeLevel.value === 'All Levels' || s.level === activeLevel.value)
)
```

Category tab counts are derived from `skills.value` using `computed`,
not hardcoded numbers.

---

### 7.4 `pages/Dashboard.vue`
**Route:** `/home` | **Layout:** `AppLayout` (activePage='dashboard')

**Data fetch:**
```js
const dashboard = ref(null)
onMounted(async () => {
    const { data } = await axios.get('/api/dashboard')
    dashboard.value = data
})
```

The `/api/dashboard` response shape:
```json
{
  "stats": { "xp": 2450, "xp_delta": 120, "streak": 7, "streak_best": 14, "lessons": 24, "lessons_in_progress": 2, "quiz_accuracy": 87 },
  "continue_learning": { "title": "Machine Learning Basics", "chapter": "Chapter 3: Supervised Learning...", "progress": 68, "flashcard_count": 12, "quiz_count": 8, "minutes_left": 22, "summary_id": 1 },
  "recommended": [ { "id": 2, "title": "Python Programming", "level": "Beginner", "minutes": 45, "tags": ["Coding","Automation"] }, ... ],
  "daily_challenge": { "title": "Neural Network Architecture Quiz", "questions": 10, "minutes": 5, "difficulty": "Hard", "xp": 150, "quiz_id": 2, "resets_at": "2024-01-01T08:42:12Z" },
  "recent_activity": [ ... ],
  "progress_overview": { "total": 42, "completed": 24, "in_progress": 8, "not_started": 10 },
  "active_skills": [ { "title": "Machine Learning", "chapter": "Ch. 3 of 8", "progress": 68 }, ... ]
}
```

**UI state refs:**
```js
const countdown = ref('')   // daily challenge timer, driven by setInterval
```

---

### 7.5 `pages/Upload.vue`
**Route:** `/upload` | **Layout:** `AppLayout` (activePage='upload')

**Data fetch:**
```js
const recentUploads = ref([])
const sampleTopics = ref([])
onMounted(async () => {
    const [uploads, topics] = await Promise.all([
        axios.get('/api/uploads/recent'),
        axios.get('/api/skills?is_featured=1'),
    ])
    recentUploads.value = uploads.data
    sampleTopics.value = topics.data
})
```

**UI state refs:**
```js
const activeTab = ref('paste')
const pastedText = ref('')
const dragOver = ref(false)
const selectedFile = ref(null)
const difficulty = ref('beginner')
const outputLanguage = ref('English')
const outputTypes = reactive({ summary: true, flashcards: true, quiz: true, mindmap: false })
const a11yOptions = reactive({ tts: false, simplify: true, diagrams: true, dyslexia: false })
const generating = ref(false)

const charCount = computed(() => pastedText.value.length)
```

On submit: `POST /api/uploads` → poll status → redirect to `/summaries/{id}`.

---

### 7.6 `pages/Summary.vue`
**Route:** `/summaries/:id` | **Layout:** `AppLayout` (activePage='')

**Data fetch:**
```js
const summary = ref(null)
onMounted(async () => {
    const { data } = await axios.get(`/api/summaries/${route.params.id}`)
    summary.value = data
})
```

All section cards, key terms, timeline steps, difficulty indicators, and
estimated time breakdowns come from `summary.value`.

**UI state refs:**
```js
const activeTab = ref('summary')   // 'summary' | 'timeline'
const a11yOptions = reactive({ tts: true, simplify: true, dyslexia: false, high_contrast: false })
const speakingSection = ref(null)  // which section's TTS is playing
```

---

### 7.7 `pages/Flashcards.vue`
**Route:** `/flashcards/:deckId` | **Layout:** `AppLayout` (activePage='flashcards')

**Data fetch:**
```js
const deck = ref(null)
const cards = ref([])
onMounted(async () => {
    const { data } = await axios.get(`/api/flashcards/${route.params.deckId}`)
    deck.value = data.deck
    cards.value = data.cards   // each card has question, answer, category, status
})
```

**UI state refs:**
```js
const currentIndex = ref(0)
const isFlipped = ref(false)
const a11yOptions = reactive({ autoRead: false, largeText: false, dyslexia: false, slowFlip: false })

const currentCard = computed(() => cards.value[currentIndex.value])
const masteredCount = computed(() => cards.value.filter(c => c.status === 'mastered').length)
const savedCount = computed(() => cards.value.filter(c => c.status === 'saved').length)
const remainingCount = computed(() => cards.value.filter(c => c.status !== 'mastered').length)
```

Status mutations (`markMastered`, `saveForLater`, `reviewAgain`) call
`PATCH /api/flashcards/{deckId}/cards/{cardId}` to persist the change.

---

### 7.8 `pages/Quiz.vue`
**Route:** `/quizzes/:quizId` | **Layout:** `AppLayout` (activePage='quizzes')

**Data fetch:**
```js
const quiz = ref(null)
const questions = ref([])
onMounted(async () => {
    const { data } = await axios.get(`/api/quizzes/${route.params.quizId}`)
    quiz.value = data.quiz
    questions.value = data.questions   // options only — correct answer NOT included
})
```

**UI state refs:**
```js
const currentIndex = ref(0)
const selectedOption = ref(null)
const submitted = ref(false)
const timer = ref(30)
const score = reactive({ correct: 0, wrong: 0, skipped: 0 })
const answers = ref([])   // accumulated for final POST
const a11yOptions = reactive({ autoRead: false, disableTimer: false, largeText: false })

const currentQuestion = computed(() => questions.value[currentIndex.value])
const accuracy = computed(() => {
    const answered = score.correct + score.wrong
    return answered === 0 ? 0 : Math.round((score.correct / answered) * 100)
})
```

On submit: `POST /api/quizzes/{quizId}/submit` with `answers` array
→ redirect to `/quizzes/{quizId}/results`.

---

### 7.9 `pages/QuizResults.vue`
**Route:** `/quizzes/:quizId/results` | **Layout:** `AppLayout` (activePage='quizzes')

**Data fetch:**
```js
const results = ref(null)
onMounted(async () => {
    const { data } = await axios.get(`/api/quizzes/${route.params.quizId}/results`)
    results.value = data
})
```

The `/api/quizzes/{id}/results` response shape:
```json
{
  "quiz": { "title": "Machine Learning Quiz", "completed_at": "...", "duration_seconds": 272, "mode": "adaptive" },
  "score": { "correct": 4, "wrong": 1, "skipped": 0, "accuracy": 80, "grade": "A", "passed": true },
  "xp": { "earned": 95, "streak_bonus": 15, "speed_bonus": 10, "first_attempt_bonus": 5 },
  "mastered_skills": ["ML Fundamentals", "Supervised Learning", "Algorithm Types", "Overfitting Concepts"],
  "needs_practice": ["Gradient Descent"],
  "questions": [ { "body": "...", "user_answer": "...", "correct_answer": "...", "is_correct": true, "xp": 15, "tag": "Foundation · Easy" }, ... ],
  "achievements_unlocked": [ { "slug": "streak-master", "title": "Streak Master", "xp": 150 }, ... ],
  "next_steps": [ ... ]
}
```

**UI state refs:**
```js
const activeReviewTab = ref('all')   // 'all' | 'correct' | 'wrong'
const reviewedQuestions = computed(() => {
    if (!results.value) return []
    if (activeReviewTab.value === 'correct') return results.value.questions.filter(q => q.is_correct)
    if (activeReviewTab.value === 'wrong')   return results.value.questions.filter(q => !q.is_correct)
    return results.value.questions
})
```

---

### 7.10 `pages/Analytics.vue`
**Route:** `/analytics` | **Layout:** `AppLayout` (activePage='analytics')

**Data fetch:**
```js
const analytics = ref(null)
const timeRange = ref('week')

onMounted(() => fetchAnalytics())
watch(timeRange, () => fetchAnalytics())

async function fetchAnalytics() {
    const { data } = await axios.get(`/api/analytics?range=${timeRange.value}`)
    analytics.value = data
}
```

The `/api/analytics` response provides all chart data:
```json
{
  "stats": { "total_hours": 14.5, "total_hours_delta": 12, "quiz_accuracy": 82, ... },
  "weekly_progress": [20, 35, 45, 60, 40, 50, 30],
  "streak_calendar": [ { "date": "2024-01-01", "completed": true }, ... ],
  "skill_growth": {
      "weeks": ["Wk 1","Wk 2","Wk 3","Wk 4","Wk 5","Wk 6","Wk 7","Wk 8"],
      "series": [
          { "label": "Machine Learning", "values": [20,30,40,50,60,68,72,80] },
          { "label": "Python",           "values": [10,20,35,50,65,78,85,90] },
          { "label": "Data Analysis",    "values": [5,10,20,30,40,55,65,72]  }
      ]
  },
  "quiz_accuracy_by_subject": [ { "label": "ML Fundamentals", "value": 86 }, ... ],
  "time_breakdown": { "reading": 5.2, "flashcards": 4.1, "quizzes": 3.4, "learning_path": 1.8 },
  "skill_radar": { "labels": ["ML Concepts","Python","Statistics","Data Viz","Deep Learning"], "values": [80,90,65,72,60] },
  "ai_insights": [ { "title": "Peak Performance: Mornings", "body": "..." }, ... ],
  "skill_progress": [ { "title": "Machine Learning", "level": "Advanced", "proficiency": 80, ... }, ... ]
}
```

All charts are inline SVG — no external library. The `weeklyData`,
`skillGrowthData`, and `radarPoints` values are `computed` from `analytics.value`.

---

### 7.11 `pages/Achievements.vue`
**Route:** `/achievements` | **Layout:** `AppLayout` (activePage='achievements')

**Data fetch:**
```js
const data = ref(null)
onMounted(async () => {
    const res = await axios.get('/api/achievements')
    data.value = res.data
})
```

Response shape:
```json
{
  "profile": { "name": "Alex Rivera", "bio": "...", "xp": 2545, "level": 12, "streak": 7, "badges_earned": 14, "rank": 42, "days_active": 48 },
  "badges": [ { "slug": "...", "title": "...", "description": "...", "icon": "...", "xp_reward": 150, "earned": true, "is_new": true, "earned_at": "..." }, ... ],
  "xp_history": [ { "event": "Streak Master Badge Earned", "description": "7-day streak milestone", "xp": 150, "created_at": "..." }, ... ],
  "leaderboard": [ { "rank": 1, "name": "Marcus T.", "level": 15, "xp": 3820, "is_current_user": false }, ... ]
}
```

**UI state refs:**
```js
const activeFilter = ref('all')
const showNewBanner = ref(true)
const filteredBadges = computed(() => {
    if (!data.value) return []
    if (activeFilter.value === 'earned') return data.value.badges.filter(b => b.earned)
    if (activeFilter.value === 'locked') return data.value.badges.filter(b => !b.earned)
    return data.value.badges
})
```

---

### 7.12 `pages/Settings.vue`
**Route:** `/settings` | **Layout:** `AppLayout` (activePage='settings')`

**Data fetch:**
```js
const prefs = ref(null)
onMounted(async () => {
    const { data } = await axios.get('/api/user/preferences')
    prefs.value = data
})
```

Every toggle, slider, and swatch in the UI binds directly to a field in `prefs.value`
using `v-model`. Changes are `watch`ed and debounced before calling
`PUT /api/user/preferences`.

**UI state refs:**
```js
const activeCategory = ref('accessibility')

// These are derived from prefs, not stored separately
const fontSizeLabel = computed(() => `${prefs.value?.font_size ?? 16}px`)
const ttsSpeedLabel = computed(() => `${prefs.value?.tts_speed ?? 1.0}×`)
const previewStyle = computed(() => ({
    fontFamily:    prefs.value?.dyslexia_font ? 'OpenDyslexic, sans-serif' : 'inherit',
    fontSize:      `${prefs.value?.font_size ?? 16}px`,
    letterSpacing: `${prefs.value?.letter_spacing ?? 0}em`,
    lineHeight:    prefs.value?.line_height ?? 1.5,
    wordSpacing:   `${prefs.value?.word_spacing ?? 0}em`,
}))
```

**Save strategy:**
```js
watch(prefs, async (newVal) => {
    await axios.put('/api/user/preferences', newVal)
}, { deep: true })
```

This means saving is automatic and immediate — no "Save" button needed for individual
fields. The "Save All" button at the top is a manual override for users who prefer
explicit confirmation.

---

## 8. File Tree After Generation

```
database/
└── seeders/
    ├── DatabaseSeeder.php           (update)
    ├── UserSeeder.php               (NEW)
    ├── UserPreferenceSeeder.php     (NEW)
    ├── SkillSeeder.php              (NEW)
    ├── BadgeSeeder.php              (NEW)
    ├── SummarySeeder.php            (NEW)
    ├── FlashcardSeeder.php          (NEW)
    ├── QuizSeeder.php               (NEW)
    └── ActivitySeeder.php           (NEW)

resources/
├── css/
│   ├── landing.css                  (exists — do not modify)
│   ├── auth.css                     (exists — do not modify)
│   └── pages.css                    (NEW)
└── js/
    ├── app.js                       (update)
    ├── components/
    │   ├── ExampleComponent.vue     (exists — leave it)
    │   └── AppLayout.vue            (NEW)
    └── pages/
        ├── Onboarding.vue           (NEW)
        ├── QuickStart.vue           (NEW)
        ├── Skills.vue               (NEW)
        ├── Dashboard.vue            (NEW)
        ├── Upload.vue               (NEW)
        ├── Summary.vue              (NEW)
        ├── Flashcards.vue           (NEW)
        ├── Quiz.vue                 (NEW)
        ├── QuizResults.vue          (NEW)
        ├── Analytics.vue            (NEW)
        ├── Achievements.vue         (NEW)
        └── Settings.vue             (NEW)
```

---

## 9. `app.js` Changes

```js
import './bootstrap';
import { createApp } from 'vue';

import AppLayout    from './components/AppLayout.vue';
import Onboarding   from './pages/Onboarding.vue';
import QuickStart   from './pages/QuickStart.vue';
import Skills       from './pages/Skills.vue';
import Dashboard    from './pages/Dashboard.vue';
import Upload       from './pages/Upload.vue';
import Summary      from './pages/Summary.vue';
import Flashcards   from './pages/Flashcards.vue';
import Quiz         from './pages/Quiz.vue';
import QuizResults  from './pages/QuizResults.vue';
import Analytics    from './pages/Analytics.vue';
import Achievements from './pages/Achievements.vue';
import Settings     from './pages/Settings.vue';

const app = createApp({});

app.component('app-layout',          AppLayout);
app.component('onboarding-page',     Onboarding);
app.component('quick-start-page',    QuickStart);
app.component('skills-page',         Skills);
app.component('dashboard-page',      Dashboard);
app.component('upload-page',         Upload);
app.component('summary-page',        Summary);
app.component('flashcards-page',     Flashcards);
app.component('quiz-page',           Quiz);
app.component('quiz-results-page',   QuizResults);
app.component('analytics-page',      Analytics);
app.component('achievements-page',   Achievements);
app.component('settings-page',       Settings);

app.mount('#app');
```

---

## 10. Blade View Wiring

Create one blade view per route under `resources/views/`. Each just mounts the component:

```blade
{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')
@section('content')
  <dashboard-page></dashboard-page>
@endsection
```

Update `layouts/app.blade.php` to include `pages.css`:
```blade
@vite(['resources/sass/app.scss', 'resources/css/pages.css', 'resources/js/app.js'])
```

---

## 11. Generation Order

Follow this sequence exactly. Each step unblocks the next.

```
Step 1  — Write all domain migrations (users add-columns, user_preferences, skills,
           uploads, summaries, flashcards, quizzes, quiz_questions, badges,
           user_badges, user_flashcard_progress, xp_log, activity_log)
Step 2  — php artisan migrate
Step 3  — Write all 8 seeders (§4 above)
Step 4  — php artisan db:seed
Step 5  — Write stub controllers + api.php routes (§5 above) — each returns DB data
Step 6  — Verify every endpoint returns JSON: php artisan serve, then curl or Postman
Step 7  — Write resources/css/pages.css
Step 8  — Write components/AppLayout.vue  (fetches /api/user)
Step 9  — Write pages/Dashboard.vue       (fetches /api/dashboard)
Step 10 — Write pages/Upload.vue          (fetches /api/uploads/recent + skills)
Step 11 — Write pages/Skills.vue          (fetches /api/skills)
Step 12 — Write pages/Summary.vue         (fetches /api/summaries/:id)
Step 13 — Write pages/Flashcards.vue      (fetches /api/flashcards/:deckId)
Step 14 — Write pages/Quiz.vue            (fetches /api/quizzes/:quizId)
Step 15 — Write pages/QuizResults.vue     (fetches /api/quizzes/:quizId/results)
Step 16 — Write pages/Analytics.vue       (fetches /api/analytics)
Step 17 — Write pages/Achievements.vue    (fetches /api/achievements)
Step 18 — Write pages/Settings.vue        (fetches /api/user/preferences)
Step 19 — Write pages/Onboarding.vue      (POSTs to /api/user/preferences)
Step 20 — Write pages/QuickStart.vue      (POSTs to /api/uploads)
Step 21 — npm run dev — visit every route, fix compile errors
Step 22 — Wire routes/web.php + blade views
```

---

## 12. Post-Generation Roadmap

Once all pages compile and render real data from the database, continue in this order:

### Phase A — AI Integration
- Implement `ProcessUploadJob` using Gemini API
- Wire `UploadController::store()` → dispatch job → poll status → redirect to summary
- The seeded summary becomes the fallback demo when no real upload is made

### Phase B — Gamification
- Implement `GamificationService` — XP award on quiz submit, flashcard mastered, lesson complete
- Badge unlock checks after every XP event
- All driven by the seeded badge criteria

### Phase C — Accessibility Persistence
- Read `user_preferences` in `AppLayout.vue` and apply CSS classes to `<body>`
- Settings page `watch` already handles saving — just make sure the classes propagate

### Phase D — QA
- Keyboard navigation audit (every interactive element reachable)
- WCAG 2.2 AA contrast check (use browser DevTools accessibility panel)
- Screen reader pass (NVDA or macOS VoiceOver)
- Mobile responsiveness (Bootstrap grid handles most of it — check sidebar collapse)
- Error state: kill the dev server mid-session and confirm every page shows an error message

---

*Seeders first. API endpoints second. Vue pages third. That order is non-negotiable.*
