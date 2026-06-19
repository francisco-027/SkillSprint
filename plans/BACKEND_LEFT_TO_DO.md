# SkillSprint -- Backend Left To Do

> Generated 2026-06-11 | Cross-referencing `IMPLEMENTATION_REPORT.md` with actual codebase state

---

## What Is Already Complete

| Layer | Status | Details |
|-------|--------|---------|
| Migrations (16 files) | Done | All tables created; 1 migration (`add_columns_to_users_table`) is a no-op |
| Models (13 files) | Done | Fillable, casts, relationships defined for all domain models |
| Seeders (9 files) | Done | Demo user, 18 skills, 32 badges, 1 summary, 12 flashcards, 1 quiz, 8 activities |
| API Controllers (9 files) | Done | 15 endpoints registered in `routes/api.php` |
| Vue Pages (12 + layout) | Done | All pages render; fetch real data from DB-backed API |
| Blade Views (14 files) | Done | One per route, each mounts its Vue component |
| CSS (pages.css) | Done | 410 lines; all component classes defined |
| Web Routes | Done | 14 routes registered; build passes (139 modules) |

---

## 1. Phase A -- AI Integration (NOT STARTED)

### 1.1 Directories to create

- [ ] **`app/Services/`** -- does not exist
- [ ] **`app/Jobs/`** -- does not exist

### 1.2 Missing service: Gemini AI client

- [ ] **`app/Services/GeminiService.php`**
  - Wraps Google Gemini API (`gemini-2.0-flash`)
  - Methods needed:
    - `generateSummary(string $content): array` -- returns structured sections, key terms, timeline
    - `generateFlashcards(string $content, int $count): array` -- returns question/answer pairs
    - `generateQuiz(string $content, int $questionCount, string $difficulty): array` -- returns questions with 4 options + correct answer + explanations
  - API key from `config('services.gemini.api_key')` or `env('GEMINI_API_KEY')`

### 1.3 Missing job: ProcessUploadJob

- [ ] **`app/Jobs/ProcessUploadJob.php`**
  - Should implement `ShouldQueue`
  - Steps:
    1. Update upload status to `processing`
    2. Call `GeminiService::generateSummary()`
    3. Save `Summary` record (with `content_sections`, `key_terms`, `timeline_steps`)
    4. Call `GeminiService::generateFlashcards()`
    5. Save `Flashcard` records linked to the summary
    6. Call `GeminiService::generateQuiz()`
    7. Save `Quiz` + `QuizQuestion` records
    8. Update upload status to `done`
    9. On failure: set upload status to `failed`

### 1.4 Missing route and controller method

- [ ] **`POST /api/uploads`** -- route not defined in `routes/api.php`
- [ ] **`UploadController::store(Request $request)`** -- method does not exist (only `recent()` exists)
  - Handle input types: pasted text, file upload (PDF/DOCX/TXT), URL, sample skill_id
  - For file upload: store to configured disk, extract text
  - Create `Upload` record with status `pending`
  - Dispatch `ProcessUploadJob`
  - Return `{ upload_id, status: 'pending' }`

### 1.5 Status polling endpoint

- [ ] **`GET /api/uploads/{id}/status`** -- needed for frontend to poll after dispatching job
  - Returns `{ id, status, summary_id (if done), error_message (if failed) }`

### 1.6 Configuration

- [ ] **Add `GEMINI_API_KEY` to `.env.example`** -- not present
- [ ] **Add Gemini config to `config/services.php`** -- entry for `gemini` key not present

---

## 2. Phase B -- Gamification (NOT STARTED)

### 2.1 Missing service

- [ ] **`app/Services/GamificationService.php`**
  - `awardXp(User $user, string $event, int $baseXp): void`
    - Create `XpLog` entry
    - Update `user.xp_total`
    - Recalculate `user.level` (e.g. `floor(xp_total / 200) + 1`)
  - `checkBadgeUnlock(User $user): array` -- check all badge criteria, unlock newly earned
  - `updateStreak(User $user): void` -- increment `streak_current` if active today; reset if gap; update `streak_best`
  - `trackActivity(User $user, string $event, string $description, ?int $xp): void` -- insert `ActivityLog`

### 2.2 Controller methods to update

- [ ] **`QuizController::submit()`** (lines 37-65) -- currently hardcoded return
  - Current state: calculates correct/wrong/skipped correctly but returns hardcoded `xp`, hardcoded `redirect`, empty `achievements_unlocked`
  - Needs to:
    - Call `GamificationService::awardXp()` with computed XP
    - Call `GamificationService::checkBadgeUnlock()` for badges like `quiz-champion`, `perfect-score`, `quick-learner`
    - Call `GamificationService::updateStreak()`
    - Return real XP earned + actual unlocked badges
    - Persist quiz attempt result (consider a `quiz_attempts` table)

- [ ] **`FlashcardController::updateCard()`** (line ~25-35) -- status update works but no gamification
  - When status changes to `mastered`: award XP via `GamificationService`
  - When enough cards mastered: check `flashcard-hero` badge (100 flashcards)

- [ ] **`QuizController::results()`** (lines 67-113) -- heavily hardcoded
  - Current state: returns fake `score` (always 4/5), hardcoded `xp`, hardcoded `mastered_skills`, faked question review
  - Needs to pull real quiz attempt data and compute real results

### 2.3 Streak tracking

- [ ] **Daily streak update logic** -- currently no mechanism exists
  - Check `user.last_active_at` on each authenticated API call
  - If today: do nothing
  - If yesterday: increment `streak_current`, update `streak_best` if needed
  - If >1 day gap: reset `streak_current` to 1
  - Update `last_active_at` to now

---

## 3. Phase C -- Accessibility Persistence (NOT STARTED)

- [ ] **Apply preferences to DOM in `AppLayout.vue`**
  - Read `user_preferences` from `/api/user/preferences` on mount
  - Apply CSS classes to `<body>`:
    - `dyslexia_font` -> toggle `OpenDyslexic` font-family
    - `font_size` -> set `--font-size-base` CSS variable
    - `high_contrast` -> add `.high-contrast` class to body
    - `reduce_motion` -> apply `prefers-reduced-motion: reduce`
    - `line_height` / `letter_spacing` / `word_spacing` -> set CSS variables
  - Currently `Settings.vue` auto-saves prefs but they don't propagate to the layout

- [ ] **Wire Summary page accessibility toggles** to read from user prefs with live override

---

## 4. Authentication & Authorization (CRITICAL GAP)

### 4.1 Hardcoded user_id everywhere

Every API controller hardcodes `user_id = 1` instead of using the authenticated user:

| File | Lines | Hardcoded Reference |
|------|-------|---------------------|
| `DashboardController.php` | 14, 16 | `where('user_id', 1)` |
| `UploadController.php` | (likely) | `where('user_id', 1)` |
| `AchievementController.php` | 15, 17, 22, 38 | Multiple `user_id = 1` references |
| `UserController.php` | (likely) | Hardcoded user_id in show/preferences |
| `FlashcardController.php` | (likely) | Hardcoded user_id for progress lookup |
| `QuizController.php` | (indirectly) | Quiz lookup doesn't scope to user |

### 4.2 No API authentication middleware

- [ ] **`routes/api.php`** has no `auth:sanctum` or `auth:api` middleware on any route
- [ ] All 15 API routes are publicly accessible without any authentication
- [ ] CSRF token check only applies via `web.php` middleware group; SPA API calls need Sanctum

### 4.3 Sanctum not configured

- [ ] **Install Sanctum**: `composer require laravel/sanctum` (not in `composer.json`)
- [ ] Publish Sanctum config
- [ ] Add `HasApiTokens` trait to `User` model
- [ ] Add Sanctum middleware to `bootstrap/app.php` via `->withMiddleware()`
- [ ] Apply `auth:sanctum` middleware group to `routes/api.php`

### 4.4 Replace hardcoded user_id

- [ ] **All controllers**: Replace `user_id = 1` with `auth()->id()` or `$request->user()->id`
- [ ] **User model**: Update `#[Fillable]` attribute to include new columns

---

## 5. Model Fixes

### 5.1 `User` model is incomplete

**File:** `app/Models/User.php` (32 lines)

- [ ] **Missing fillable columns** -- only `name`, `email`, `password` are fillable. Missing:
  - `avatar`, `bio`, `xp_total`, `level`, `daily_goal_minutes`, `streak_current`, `streak_best`, `onboarding_completed_at`, `last_active_at`
- [ ] **Missing cast definitions** -- no casts for:
  - `xp_total` -> integer, `level` -> integer, `daily_goal_minutes` -> integer
  - `streak_current` -> integer, `streak_best` -> integer
  - `onboarding_completed_at` -> datetime, `last_active_at` -> datetime
- [ ] **Missing relationship methods** -- no `hasOne` for `UserPreference`, no `hasMany` for `Upload`, `Summary`, `XpLog`, `ActivityLog`, `UserBadge`, `UserFlashcardProgress`

### 5.2 Migration no-op

- [ ] **`2025_01_01_000001_add_columns_to_users_table.php`** -- `up()` and `down()` are both empty
  - Columns were consolidated into `0001_01_01_000000_create_users_table` for PostgreSQL compatibility
  - The unused migration should either be deleted or documented as intentional

### 5.3 Missing database table (quiz attempts)

- [ ] **`quiz_attempts` table** -- no table exists to persist quiz results
  - The `QuizController::submit()` method computes results but never saves them
  - Need columns: `user_id`, `quiz_id`, `answers` (JSON), `score`, `accuracy`, `xp_earned`, `completed_at`

---

## 6. Hardcoded / Fake Data Elimination

### 6.1 DashboardController -- partially hardcoded

**File:** `app/Http/Controllers/Api/DashboardController.php`

- [ ] `stats` (lines 19-27): xp, xp_delta, streak, streak_best, lessons, quiz_accuracy all hardcoded
  - **Fix:** Query `User` model for xp/streak; compute lessons from `ActivityLog`; compute quiz_accuracy from `quiz_attempts`
- [ ] `continue_learning` (lines 28-36): all values hardcoded including `quiz_count: 8`, `progress: 68`
  - **Fix:** Compute from actual `Summary` + `Flashcard` counts; derive progress from completed flashcards
- [ ] `daily_challenge` (lines 44-52): quiz_id=2 (doesn't exist), all values hardcoded
  - **Fix:** Either seed a second quiz or make this dynamic from a daily rotation
- [ ] `progress_overview` (lines 59-64): hardcoded 42/24/8/10
  - **Fix:** Derive from enrolled skills progress
- [ ] `active_skills` (lines 65-71): hardcoded single entry
  - **Fix:** Query from DB

### 6.2 QuizController -- heavily hardcoded

**File:** `app/Http/Controllers/Api/QuizController.php`

- [ ] `submit()` (line 61): XP return is hardcoded (`earned: 95`, `streak_bonus: 15`, `speed_bonus: 10`)
  - **Fix:** Call `GamificationService` to compute real XP
- [ ] `results()` (lines 85-112): entire response is fake -- score 4/5, fake question review, fake achievements
  - **Fix:** Pull real attempt from `quiz_attempts` table; match answers to correct options

### 6.3 AnalyticsController -- 100% hardcoded

**File:** `app/Http/Controllers/Api/AnalyticsController.php`

- [ ] Every single data point is a hardcoded value or array (lines 16-65)
  - `stats`, `weekly_progress`, `skill_growth`, `quiz_accuracy_by_subject`, `time_breakdown`, `skill_radar`, `ai_insights`, `skill_progress`
  - **Fix:** Compute from `ActivityLog`, `XpLog`, `user_flashcard_progress`, and `quiz_attempts` tables
  - AI insights can remain static for now (genuine AI analysis is Phase A scope)

### 6.4 AchievementController -- partially hardcoded

**File:** `app/Http/Controllers/Api/AchievementController.php`

- [ ] `leaderboard` (lines 50-55): entirely hardcoded 4 entries
  - **Fix:** Query top users by XP from `users` table; order by `xp_total DESC`
- [ ] `profile.rank` (line 65): hardcoded `42`
  - **Fix:** Compute rank by counting users with higher XP + 1
- [ ] `profile.days_active` (line 66): hardcoded `48`
  - **Fix:** Count distinct days from `ActivityLog` or `XpLog`

---

## 7. Missing Backend Infrastructure

### 7.1 Directories that don't exist

| Directory | Needed For |
|-----------|-----------|
| `app/Services/` | GeminiService, GamificationService, StreakService |
| `app/Jobs/` | ProcessUploadJob, future async tasks |
| `app/Http/Middleware/` | Custom middleware (e.g. `CheckOnboarding`, `ApplyPreferences`) |
| `app/Http/Requests/` | Form request validation (e.g. `StoreUploadRequest`, `SubmitQuizRequest`) |
| `app/Http/Resources/` | API resource transformers for consistent JSON shape |
| `app/Enums/` | Status enums (upload status, flashcard status, skill level, etc.) |

### 7.2 Missing route patterns

- [ ] **`POST /api/uploads`** -- upload store (see section 1.4)
- [ ] **`GET /api/uploads/{id}/status`** -- upload status polling (see section 1.5)
- [ ] **`POST /api/user/enroll`** -- skill enrollment (Skills page select has no backend persistence)
- [ ] **`GET /api/user/enrolled-skills`** -- list user's enrolled skills
- [ ] **`DELETE /api/user/enrolled-skills/{id}`** -- unenroll from a skill

### 7.3 Missing FormRequest validation

- [ ] **`StoreUploadRequest`** -- validate file types, sizes, text length
- [ ] **`SubmitQuizRequest`** -- validate answers array shape
- [ ] **`UpdatePreferencesRequest`** -- validate preference field types and ranges
- [ ] **`UpdateFlashcardProgressRequest`** -- validate status is one of `unseen|current|saved|mastered`

### 7.4 Missing API Resources

All controllers return raw arrays/collections. Consistent JSON structure via API resources:
- [ ] **`UserResource`**, **`SkillResource`**, **`SummaryResource`**, **`FlashcardResource`**
- [ ] **`QuizResource`**, **`QuizQuestionResource`**, **`BadgeResource`**, **`ActivityLogResource`**

### 7.5 Missing policies

- [ ] No authorization policies for any model (e.g. user should only see own uploads, own progress)
- [ ] `php artisan make:policy` for Upload, Summary, Flashcard, Quiz models

---

## 8. Database & Seeder Gaps

### 8.1 Missing pivot/enrollment table

- [ ] **`user_skills` table** (or `enrollments`) -- tracks which skills a user has enrolled in
  - Columns: `user_id`, `skill_id`, `progress_percent`, `enrolled_at`, `completed_at`
  - Migration + Model + Seeder needed

### 8.2 Missing quiz_attempts table

- [ ] **`quiz_attempts` table** (see section 5.3)
  - Migration: `2025_01_01_000014_create_quiz_attempts_table.php`
  - Model: `QuizAttempt.php`

### 8.3 Seeder improvements

- [ ] **Second demo quiz needed** -- Dashboard references `quiz_id: 2` but only 1 quiz is seeded
- [ ] **More diverse activity data** -- current 8 activity entries are all tied to summary_id=1
- [ ] **Second demo user** -- useful for testing leaderboard and multi-user features

---

## 9. QA & Testing (Phase D)

### 9.1 Testing infrastructure

- [ ] **No test files exist** -- `tests/` directory has only default Laravel tests
- [ ] **Feature tests needed**:
  - [ ] API endpoint tests (all 15 endpoints)
  - [ ] Auth flow tests (login, register, password reset)
  - [ ] Quiz submission and scoring
  - [ ] Flashcard progress CRUD
- [ ] **Unit tests needed**:
  - [ ] GamificationService XP calculation
  - [ ] Badge unlock criteria logic
  - [ ] Streak update logic

### 9.2 Accessibility audit (WCAG 2.2 AA)

- [ ] Keyboard navigation through all interactive elements
- [ ] Focus indicator visibility on all focusable elements
- [ ] Color contrast ratios meet AA minimums
- [ ] Screen reader testing (NVDA / VoiceOver)
- [ ] Skip-to-content link functional
- [ ] ARIA labels on all interactive elements

### 9.3 Cross-browser & mobile

- [ ] Test on Chrome, Firefox, Edge, Safari
- [ ] Mobile responsive breakpoints at 991px and below
- [ ] Touch interaction on flashcards (tap to flip)
- [ ] Drag-and-drop upload on mobile

### 9.4 Error state testing

- [ ] Kill dev server mid-session -- confirm every page shows error message
- [ ] 404 handling for invalid summary/quiz/flashcard IDs
- [ ] 500 error handling for server failures
- [ ] Empty state handling (no skills, no badges, no uploads)

---

## 10. Deployment Readiness

- [ ] **Docker build test** -- `Dockerfile` exists at root but hasn't been tested
- [ ] **`render.yaml` validation** -- exists for Render.com deployment but configuration needs verification
- [ ] **Production `.env` values** -- database connection, app URL, Gemini key
- [ ] **Queue worker configuration** -- database queue configured but no supervisor/worker process defined
- [ ] **Scheduler configuration** -- no scheduled tasks defined (daily streak reset, daily challenge rotation)
- [ ] **Asset build for production** -- `npm run build` passes but needs re-run before deploy

---

## Summary -- Priority Ranking

### Immediate / Blocking (must fix before real use)

| # | Item | Section |
|---|------|---------|
| 1 | Replace hardcoded `user_id=1` with `auth()->id()` in all controllers | 4.4 |
| 2 | Install & configure Sanctum for API authentication | 4.3 |
| 3 | Add `auth:sanctum` middleware to `routes/api.php` | 4.2 |
| 4 | Fix `User` model: fillable, casts, relationships | 5.1 |
| 5 | Create `quiz_attempts` table + migration | 5.3 |

### High Priority (core features incomplete)

| # | Item | Section |
|---|------|---------|
| 6 | Create `GeminiService.php` | 1.2 |
| 7 | Create `ProcessUploadJob.php` | 1.3 |
| 8 | Create `UploadController::store()` + `POST /api/uploads` route | 1.4 |
| 9 | De-hardcode `DashboardController` | 6.1 |
| 10 | De-hardcode `QuizController::results()` | 6.2 |
| 11 | De-hardcode `AnalyticsController` | 6.3 |
| 12 | Create `GamificationService.php` | 2.1 |

### Medium Priority (polish & completeness)

| # | Item | Section |
|---|------|---------|
| 13 | Create `app/Services/`, `app/Jobs/`, `app/Enums/` directories | 7.1 |
| 14 | De-hardcode `AchievementController` leaderboard/rank/days_active | 6.4 |
| 15 | Create `user_skills` enrollment table | 8.1 |
| 16 | Apply accessibility preferences to DOM in AppLayout | 3 |
| 17 | Add status polling endpoint `GET /api/uploads/{id}/status` | 1.5 |
| 18 | Create FormRequest validators | 7.3 |
| 19 | Create API Resources for consistent JSON | 7.4 |
| 20 | Add Gemini config to `config/services.php` + `.env.example` | 1.6 |

### Low Priority (nice to have)

| # | Item | Section |
|---|------|---------|
| 21 | Write feature + unit tests | 9.1 |
| 22 | Add a second demo quiz | 8.3 |
| 23 | Add pagination to list endpoints | -- |
| 24 | Add rate limiting to API routes | -- |
| 25 | Clean up no-op migration | 5.2 |
| 26 | Add model policies for authorization | 7.5 |
| 27 | QA: keyboard, contrast, screen reader audits | 9.2 |
| 28 | Docker deployment test | 10 |
| 29 | Streak daily reset scheduler | 10 |

---

### File Summary -- what needs to be created

```
app/
├── Services/
│   ├── GeminiService.php           [NEW]
│   ├── GamificationService.php     [NEW]
│   └── StreakService.php           [NEW]
├── Jobs/
│   └── ProcessUploadJob.php        [NEW]
├── Http/
│   ├── Middleware/                  [NEW directory]
│   │   └── CheckOnboarding.php     [NEW, optional]
│   ├── Requests/                    [NEW directory]
│   │   ├── StoreUploadRequest.php  [NEW]
│   │   ├── SubmitQuizRequest.php   [NEW]
│   │   └── UpdatePreferencesRequest.php [NEW]
│   └── Resources/                   [NEW directory]
│       ├── UserResource.php        [NEW]
│       ├── SkillResource.php       [NEW]
│       ├── SummaryResource.php     [NEW]
│       └── ...                     [NEW]
├── Enums/                           [NEW directory]
│   ├── UploadStatus.php            [NEW, optional]
│   └── FlashcardStatus.php         [NEW, optional]
├── Policies/                        [NEW directory]
│   ├── UploadPolicy.php            [NEW]
│   └── SummaryPolicy.php           [NEW]
└── Models/
    ├── User.php                     [MODIFY -- add fillable, casts, relationships]
    └── QuizAttempt.php              [NEW]

database/
└── migrations/
    ├── 2025_01_01_000014_create_quiz_attempts_table.php  [NEW]
    └── 2025_01_01_000015_create_user_skills_table.php    [NEW, optional]

routes/
└── api.php                          [MODIFY -- add POST /uploads, auth middleware]

tests/
├── Feature/
│   ├── Api/AuthTest.php            [NEW]
│   ├── Api/QuizTest.php            [NEW]
│   └── Api/FlashcardTest.php       [NEW]
└── Unit/
    └── Services/GamificationServiceTest.php [NEW]
```

**Total new files to create: ~22 | Files to modify: ~10**