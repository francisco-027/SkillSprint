# SkillSprint -- System Documentation

> AI-Powered Micro-Learning Web App for Lifelong Skills
> SDG 4 (Quality Education) & SDG 10 (Reduced Inequalities)

---

## Table of Contents

1. [What Is SkillSprint?](#1-what-is-skillsprint)
2. [Prerequisites](#2-prerequisites)
3. [Quick Start -- First Time Setup](#3-quick-start--first-time-setup)
4. [Daily Development Workflow](#4-daily-development-workflow)
5. [Project Structure](#5-project-structure)
6. [Database Overview](#6-database-overview)
7. [API Reference](#7-api-reference)
8. [Frontend Architecture](#8-frontend-architecture)
9. [Page Reference](#9-page-reference)
10. [Configuration](#10-configuration)
11. [Common Tasks](#11-common-tasks)
12. [Troubleshooting](#12-troubleshooting)

---

## 1. What Is SkillSprint?

SkillSprint transforms complex topics (textbook chapters, PDFs, pasted notes) into bite-sized interactive lessons using AI. A student pastes material and receives:

- A structured AI summary with 5 study sections
- A visual learning timeline
- Extracted key terminology
- 12 interactive flashcards
- A 5-question adaptive MCQ quiz with results and review

All wrapped in a gamified experience with XP, streaks, badges, leaderboards, and full accessibility controls.

---

## 2. Prerequisites

| Tool | Minimum Version | Check With |
|------|----------------|------------|
| PHP | 8.3+ | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18+ | `node -v` |
| npm | 9+ | `npm -v` |
| SQLite | (bundled with PHP) | `php -m | grep sqlite` |

---

## 3. Quick Start -- First Time Setup

### 3.1 Clone and install dependencies

```bash
git clone <repo-url>
cd Microlearn-AI
composer install
npm install
```

### 3.2 Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

The default `.env` uses SQLite. No database server needed. If you want MySQL/PostgreSQL, edit `.env` and update `DB_*` values.

### 3.3 Build the database and seed demo data

```bash
php artisan migrate:fresh
php artisan db:seed
```

This creates all 16 tables and populates them with:
- **Demo user:** Alex Rivera (email: `alex@skillsprint.dev`, password: `password`)
- **18 skills** across 5 categories
- **32 achievement badges**
- **1 complete ML Basics lesson** (summary sections, 12 flashcards, 5-question quiz)
- **Activity log entries** spanning the last 4 days

### 3.4 Build frontend assets

```bash
npm run build
```

### 3.5 Start the development server

```bash
php artisan serve
```

The app is now running at `http://127.0.0.1:8000`.

---

## 4. Daily Development Workflow

### 4.1 Start the Vite dev server (hot reload)

```bash
npm run dev
```

This watches your Vue components, CSS, and JS for changes and hot-reloads the browser. Run this alongside `php artisan serve` in a separate terminal.

### 4.2 Reset the database

```bash
php artisan migrate:fresh --seed
```

Drops all tables, re-runs all migrations, and re-seeds. Useful when you need a clean slate.

### 4.3 Build for production

```bash
npm run build
```

Generates minified, hashed assets in `public/build/`. Run this before deployment.

### 4.4 Run database seeds without resetting

```bash
php artisan db:seed
```

Re-runs the seeder suite on the existing database. Note: may cause duplicate key errors if run repeatedly without a fresh migrate.

---

## 5. Project Structure

```
Microlearn-AI/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/                        # All 9 API controllers
│   │   │   ├── AchievementController.php
│   │   │   ├── AnalyticsController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── FlashcardController.php
│   │   │   ├── QuizController.php
│   │   │   ├── SkillController.php
│   │   │   ├── SummaryController.php
│   │   │   ├── UploadController.php
│   │   │   └── UserController.php
│   │   ├── Auth/                       # Laravel UI auth controllers
│   │   ├── Controller.php              # Base controller
│   │   └── HomeController.php
│   └── Models/                         # 13 Eloquent models
│       ├── User.php, UserPreference.php
│       ├── Skill.php, Upload.php, Summary.php
│       ├── Flashcard.php, UserFlashcardProgress.php
│       ├── Quiz.php, QuizQuestion.php
│       ├── Badge.php, UserBadge.php
│       └── XpLog.php, ActivityLog.php
├── bootstrap/
│   └── app.php                         # Registers api.php routing
├── database/
│   ├── migrations/                     # 16 migration files
│   └── seeders/                        # 9 seeder files
│       ├── DatabaseSeeder.php          # Orchestrator
│       ├── UserSeeder.php
│       ├── UserPreferenceSeeder.php
│       ├── SkillSeeder.php             # 18 skills
│       ├── BadgeSeeder.php             # 32 badges
│       ├── SummarySeeder.php           # ML Basics lesson
│       ├── FlashcardSeeder.php         # 12 flashcards
│       ├── QuizSeeder.php              # 5-question quiz
│       └── ActivitySeeder.php          # Activity feed entries
├── resources/
│   ├── css/
│   │   ├── landing.css                 # Design tokens + landing page
│   │   ├── auth.css                    # Login/register page styles
│   │   └── pages.css                   # App shell + all page components
│   ├── js/
│   │   ├── app.js                      # Vue 3 entry point (13 components)
│   │   ├── bootstrap.js                # Axios + Bootstrap JS setup
│   │   ├── components/
│   │   │   └── AppLayout.vue           # Authenticated app shell
│   │   └── pages/                      # 12 page components
│   │       ├── Dashboard.vue
│   │       ├── Upload.vue
│   │       ├── Skills.vue
│   │       ├── Summary.vue
│   │       ├── Flashcards.vue
│   │       ├── Quiz.vue
│   │       ├── QuizResults.vue
│   │       ├── Analytics.vue
│   │       ├── Achievements.vue
│   │       ├── Settings.vue
│   │       ├── Onboarding.vue
│   │       └── QuickStart.vue
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php           # Base layout (clean shell)
│       ├── landing.blade.php           # Marketing landing page
│       ├── home.blade.php              # Dashboard (mounts <dashboard-page>)
│       ├── auth/                       # Login/register views
│       ├── dashboard.blade.php
│       ├── upload.blade.php
│       ├── skills.blade.php
│       ├── summary.blade.php
│       ├── flashcards.blade.php
│       ├── quiz.blade.php
│       ├── quiz-results.blade.php
│       ├── analytics.blade.php
│       ├── achievements.blade.php
│       ├── settings.blade.php
│       ├── onboarding.blade.php
│       └── quickstart.blade.php
├── routes/
│   ├── web.php                         # 12 page routes + auth routes
│   └── api.php                         # 15 API endpoints
├── vite.config.js                      # Vite input: sass, landing, auth, pages, app.js
├── IMPLEMENTATION_PLAN.md              # The 22-step blueprint
├── IMPLEMENTATION_REPORT.md            # What was built and how
├── PROJECT_ROADMAP.md                  # Full project bible + schema
└── READ_THIS.md                        # This file
```

---

## 6. Database Overview

### 6.1 Tables (16 total)

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `users` | User accounts + gamification stats | xp_total, level, streak_current, streak_best, daily_goal_minutes |
| `user_preferences` | Accessibility + learning settings | 22 columns covering a11y, visual, audio, motion, learning, localization |
| `skills` | Skill library catalog | title, category, level, tags (JSON), learner_count, is_popular |
| `uploads` | File/user content uploads | type (text/pdf/image/url), status (pending/processing/done/failed), word_count |
| `summaries` | AI-generated lesson summaries | content_sections (JSON), key_terms (JSON), timeline_steps (JSON) |
| `flashcards` | Study flashcards | question, answer, category, status, sort_order |
| `quizzes` | Quiz metadata | title, mode (adaptive/standard), question_count, difficulty |
| `quiz_questions` | Individual quiz questions | body, correct_option, options (JSON), explanation, difficulty, xp_reward |
| `badges` | Achievement definitions | slug, title, description, icon, xp_reward, criteria (JSON) |
| `user_badges` | User earned badges (pivot) | earned_at, is_new, unique(user_id, badge_id) |
| `user_flashcard_progress` | Per-user flashcard status (pivot) | status (unseen/current/saved/mastered), unique(user_id, flashcard_id) |
| `xp_log` | XP event history | event, description, xp |
| `activity_log` | Activity feed | event, description, xp |
| `cache`, `cache_locks` | Laravel cache (default) | -- |
| `jobs`, `job_batches`, `failed_jobs` | Laravel queue (default) | -- |
| `sessions`, `password_reset_tokens` | Laravel auth (default) | -- |

### 6.2 Demo Data Summary

| Entity | Count | Details |
|--------|-------|---------|
| Demo User | 1 | Alex Rivera, XP 2,545, Level 12 |
| Skills | 18 | 5 categories, 3 difficulty levels |
| Badges | 32 | 14 earned, 3 new, 18 locked |
| Summaries | 1 | ML Basics (5 sections) |
| Flashcards | 12 | 1 deck, all categories |
| Quizzes | 1 | 5 questions, adaptive mode |
| Activity Entries | 8 | Spread across 4 days |

---

## 7. API Reference

Base URL: `http://127.0.0.1:8000/api`

### 7.1 User

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user` | Current user profile (xp, level, streak, etc.) |
| GET | `/api/user/preferences` | User's accessibility/learning preferences |
| PUT | `/api/user/preferences` | Update preferences (all 22 fields accepted) |

### 7.2 Skills

| Method | Endpoint | Params | Description |
|--------|----------|--------|-------------|
| GET | `/api/skills` | `?search=&category=&level=&is_featured=&sort=` | List all skills with filtering |
| GET | `/api/skills/{id}` | -- | Single skill detail |

Filter examples:
- `?category=Technology` -- filter by category
- `?level=Beginner` -- filter by difficulty
- `?is_featured=1` -- only featured skills
- `?sort=newest` -- sort by creation date (default: by learner_count desc)

### 7.3 Dashboard

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/dashboard` | Stats, continue learning, recommendations, daily challenge, activity, progress |

Response includes: `stats`, `continue_learning`, `recommended[]`, `daily_challenge`, `recent_activity[]`, `progress_overview`, `active_skills[]`

### 7.4 Uploads

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/uploads/recent` | Last 3 uploads for the current user |

### 7.5 Summaries

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/summaries/{id}` | Full summary with content sections, key terms, timeline, flashcards |

### 7.6 Flashcards

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/flashcards/{deckId}` | Deck metadata + all cards with per-user status |
| PATCH | `/api/flashcards/{deckId}/cards/{cardId}` | Update card status (`{ status: "mastered" | "saved" | "unseen" }`) |

### 7.7 Quizzes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/quizzes/{quizId}` | Quiz metadata + questions (options only, no correct answer) |
| POST | `/api/quizzes/{quizId}/submit` | Submit answers (`{ answers: [{ question_id, selected }] }`), returns score + redirect |
| GET | `/api/quizzes/{quizId}/results` | Full results with score, XP breakdown, per-question review, achievements |

### 7.8 Analytics

| Method | Endpoint | Params | Description |
|--------|----------|--------|-------------|
| GET | `/api/analytics` | `?range=week|month|all` | Stats, weekly progress, skill growth, quiz accuracy, time breakdown, skill radar, AI insights |

### 7.9 Achievements

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/achievements` | User profile, all badges (earned/locked/new), XP history, leaderboard |

---

## 8. Frontend Architecture

### 8.1 Tech Stack

- **Vue 3** (Composition API, `<script setup>` equivalent via `setup()`)
- **Vite 8** as build tool with `laravel-vite-plugin` v3
- **CSS:** Custom design system (`landing.css` tokens) + `pages.css` (app shell + components)
- **No UI framework** for pages -- all components use custom CSS on top of design tokens
- **Bootstrap 5 Sass** compiled separately for the landing page and auth forms
- **Axios** for all HTTP requests (configured in `bootstrap.js`)

### 8.2 Component Hierarchy

```
app.js (Vue 3 root)
├── <app-layout> (AppLayout.vue)        -- Shared shell for 10 pages
│   ├── <dashboard-page>  (Dashboard.vue)
│   ├── <upload-page>     (Upload.vue)
│   ├── <skills-page>     (Skills.vue)    -- Own layout, no sidebar
│   ├── <summary-page>    (Summary.vue)
│   ├── <flashcards-page> (Flashcards.vue)
│   ├── <quiz-page>       (Quiz.vue)
│   ├── <quiz-results-page> (QuizResults.vue)
│   ├── <analytics-page>  (Analytics.vue)
│   ├── <achievements-page> (Achievements.vue)
│   └── <settings-page>   (Settings.vue)
├── <onboarding-page>     (Onboarding.vue) -- Standalone, no sidebar
└── <quick-start-page>    (QuickStart.vue) -- Standalone, no sidebar
```

### 8.3 Data Flow Pattern

Every page follows this pattern:

```
onMounted() → axios.get('/api/...') → populate ref → template renders
                                       ↓
                                  loading = false
                                       ↓
                               error handling (if catch)
```

**UI state** (toggles, selections, timers, form inputs) lives in `ref`/`reactive`.
**Content** (skill names, quiz questions, badge descriptions, activity text) comes from the database via API.

No mock data arrays exist inside any component.

### 8.4 Design Tokens (from `landing.css`)

```css
--bg: #0a0a0f           /* near-black background */
--surface: #12121a       /* elevated surface */
--surface-2: #16161f     /* higher elevation */
--card-bg: rgba(22,22,32,0.6)  /* glass-morphism cards */
--card-border: rgba(255,255,255,0.07)
--text: #f5f5f7          /* primary text */
--text-muted: #8b8b9a   /* secondary text */
--purple: #7c5cfc        /* primary accent */
--purple-bright: #8b6dff /* hover/special accent */
--pink: #d56bff
--green: #38d98a         /* success/positive */
--grad: linear-gradient(135deg, #7c5cfc, #b15cff, #d56bff)
--grad-text: linear-gradient(100deg, #8b6dff, #d56bff, #ff9bd0)
```

---

## 9. Page Reference

| # | Page | Route | Auth | Layout | Key API Calls |
|---|------|-------|------|--------|---------------|
| 1 | Landing | `/` | No | Custom Blade | None (static) |
| 2 | Login/Register | `/login`, `/register` | No | `auth.css` | Auth routes |
| 3 | Dashboard | `/home` | Yes | AppLayout | `/api/dashboard`, `/api/user` |
| 4 | Upload | `/upload` | Yes | AppLayout | `/api/uploads/recent`, `/api/skills?is_featured=1` |
| 5 | Skill Library | `/skills` | Yes | Standalone | `/api/skills` |
| 6 | AI Summary | `/summaries/{id}` | Yes | AppLayout | `/api/summaries/{id}` |
| 7 | Flashcards | `/flashcards/{deckId}` | Yes | AppLayout | `/api/flashcards/{deckId}` |
| 8 | Quiz | `/quizzes/{quizId}` | Yes | AppLayout | `/api/quizzes/{quizId}`, POST submit |
| 9 | Quiz Results | `/quizzes/{quizId}/results` | Yes | AppLayout | `/api/quizzes/{quizId}/results` |
| 10 | Analytics | `/analytics` | Yes | AppLayout | `/api/analytics?range=` |
| 11 | Achievements | `/achievements` | Yes | AppLayout | `/api/achievements` |
| 12 | Settings | `/settings` | Yes | AppLayout | `/api/user/preferences` (get + auto-put) |
| 13 | Onboarding | `/onboarding` | Yes | Standalone | POST `/api/user/preferences` |
| 14 | Quick Start | `/quick-start` | No | Standalone | `/api/skills?is_featured=1` |

---

## 10. Configuration

### 10.1 Environment Variables

| Variable | Default | Purpose |
|----------|---------|---------|
| `DB_CONNECTION` | `sqlite` | Database driver |
| `DB_DATABASE` | `database/database.sqlite` | SQLite file path |
| `APP_URL` | `http://localhost` | Base URL |
| `APP_NAME` | `SkillSprint` | App name in titles |
| `GEMINI_API_KEY` | (not set) | AI integration key (Phase A) |

### 10.2 Vite Configuration (`vite.config.js`)

Entry points built by Vite:
1. `resources/sass/app.scss` → Bootstrap Sass
2. `resources/css/landing.css` → Design tokens + landing
3. `resources/css/auth.css` → Login/register styles
4. `resources/css/pages.css` → App shell + page components
5. `resources/js/app.js` → Vue 3 app + all 13 components

### 10.3 Laravel Configuration

- Auth: `laravel/ui` v4 (Bootstrap-based auth scaffolding)
- Queue: Database driver (configured but queue worker not required for current feature set)
- Session: Database driver (via `sessions` table)
- API: No Sanctum/Passport yet (Phase A will add token auth)

---

## 11. Common Tasks

### 11.1 Add a new skill

Edit `database/seeders/SkillSeeder.php`, add a row to the `$skills` array:

```php
['title' => 'New Skill Name', 'category' => 'Technology', 'level' => 'Beginner',
 'estimated_minutes' => 30, 'learner_count' => 1000, 'is_popular' => false,
 'tags' => ['Tag1','Tag2']],
```

Then: `php artisan migrate:fresh --seed`

### 11.2 Add a new badge

Edit `database/seeders/BadgeSeeder.php`, add to `$earnedBadges` or `$lockedBadges` array. Then re-seed.

### 11.3 Add a new API endpoint

1. Create or update a controller in `app/Http/Controllers/Api/`
2. Add the route to `routes/api.php`
3. Rebuild: `npm run build`

### 11.4 Modify a Vue page

1. Edit the `.vue` file in `resources/js/pages/`
2. If you change the `name` or add a new component, update `resources/js/app.js` to register it
3. Run `npm run dev` for hot reload, or `npm run build` for production

### 11.5 Change the CSS

- **Shared design tokens:** Edit `resources/css/landing.css` (colors, spacing, typography)
- **Page components:** Edit `resources/css/pages.css` (shell, cards, badges, toggles)
- **Auth forms:** Edit `resources/css/auth.css`
- **Bootstrap overrides:** Edit `resources/sass/_variables.scss`

### 11.6 Reset to factory defaults

```bash
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

### 11.7 Check all registered routes

```bash
php artisan route:list
```

---

## 12. Troubleshooting

### 12.1 "Could not load content" on every page

**Cause:** Database not seeded or API server not running.

**Fix:**
```bash
php artisan migrate:fresh --seed
php artisan serve
```

### 12.2 Layout broken / wrong background color

**Cause:** `pages.css` not compiled or missing from Vite build.

**Fix:**
```bash
npm run build
php artisan serve
```

### 12.3 "Target class [controller] does not exist"

**Cause:** Controller namespace mismatch or file not found.

**Fix:**
```bash
composer dump-autoload
php artisan route:clear
```

### 12.4 Vue component not rendering (blank page)

**Cause:** Component not registered in `app.js` or blade view uses wrong tag name.

**Fix:** Check:
- `resources/js/app.js` has `app.component('component-name', Component)`
- Blade file uses matching kebab-case tag: `<component-name>`

### 12.5 Vite build fails with "failed to resolve import"

**Cause:** A `.vue` file imports a package not listed in `package.json` (e.g., `vue-router`).

**Fix:** Either install the missing package (`npm install <name>`) or remove the unused import from the `.vue` file.

### 12.6 "php artisan migrate" error

**Cause:** SQLite database file permissions or migration dependency order.

**Fix:**
```bash
php artisan migrate:fresh
```
Use `migrate:fresh` instead of `migrate` to avoid dependency conflicts.

### 12.7 Hot reload not working (npm run dev)

**Cause:** Vite dev server not running, or `APP_URL` mismatch.

**Fix:**
1. Ensure `npm run dev` is running in a terminal
2. Check `.env` `APP_URL` is correct
3. Clear browser cache
4. Run `npm run build` if dev mode continues to fail

---

*Last updated: June 10, 2026*