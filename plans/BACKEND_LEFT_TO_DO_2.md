# SkillSprint — Backend Left To Do (v2)

> Generated 2026-06-12 | Post-completion audit of all 11 implementation phases

---

## Critical (will cause runtime errors or silently broken features)

### 1. Missing PDF/DOCX parser libraries

**Files:** `app/Http/Controllers/Api/UploadController.php:108-128`

The `store()` method type-hints `\Smalot\PdfParser\Parser` and `\PhpOffice\PhpWord\IOFactory` but neither package is in `composer.json`. Uploading a PDF or DOCX will throw a fatal `ClassNotFoundException`.

```bash
composer require smalot/pdfparser
composer require phpoffice/phpword
```

### 2. Onboarding.vue sends POST instead of PUT

**File:** `resources/js/pages/Onboarding.vue:237`

```js
// Current (wrong):
fetch('/api/user/preferences', { method: 'POST' })

// Should be:
fetch('/api/user/preferences', { method: 'PUT' })
```

The API route is `Route::put('/user/preferences', ...)`. POST gets a 405 Method Not Allowed. Onboarding preferences are silently lost (the error is caught and ignored on line 241).

### 3. `<router-link>` without vue-router

**File:** `resources/js/pages/Onboarding.vue:148`

```html
<!-- Current (dead link): -->
<router-link to="/home">Go to Dashboard →</router-link>

<!-- Should be: -->
<a href="/home">Go to Dashboard →</a>
```

`vue-router` is not installed. `<router-link>` renders as a non-functional custom HTML element.

### 4. Quiz results always return empty achievements

**File:** `app/Http/Controllers/Api/QuizController.php:169`

```php
// Current (hardcoded empty):
'achievements_unlocked' => [],
```

The `submit()` endpoint correctly computes unlocked badges via `GamificationService::checkBadgeUnlock()`, but the `results()` endpoint (fetched by `QuizResults.vue`) returns a hardcoded empty array.

**Fix options:**
- Query `UserBadge` for badges unlocked after the attempt's `completed_at`
- Store unlocked badge IDs on the `QuizAttempt` model as a JSON column
- Pass them from the submit response via session/flash

### 5. QuickStart fetches auth-protected API without auth

**Files:** `routes/api.php:29`, `resources/js/pages/QuickStart.vue:153`

`/api/skills` is inside `auth:sanctum` middleware, but `QuickStart.vue` calls `GET /api/skills?is_featured=1` on mount. The Quick Start landing page is public (no auth required). An unauthenticated user gets a 401 error.

**Fix options:**
- Move `GET /api/skills` outside the `auth:sanctum` group (make it public)
- Or require authentication for `/quick-start` route

---

## High Priority (features incomplete or data not persisted)

### 6. Skills.vue never calls enrollment API

**File:** `resources/js/pages/Skills.vue:153-159`

```js
toggleSkill(skill) {
    // Only manages local state — never calls the API
    const index = this.selectedSkills.indexOf(skill.id)
    if (index > -1) {
        this.selectedSkills.splice(index, 1)
    } else {
        this.selectedSkills.push(skill.id)
    }
}
```

The entire `POST /api/user/enroll` route, `UserController::enroll()` method, and `user_skills` table exist but are unused by the frontend. Skill selection is lost on page refresh.

### 7. Hardcoded flashcard deck title

**File:** `app/Http/Controllers/Api/FlashcardController.php:32`

```php
'title' => 'Machine Learning Basics',   // hardcoded
```

Should fetch the actual summary title associated with the deck:

```php
'title' => optional(\App\Models\Summary::find($deckId))->title ?? 'Flashcard Deck',
```

### 8. API Resources created but unused

All 5 API Resource classes exist (`UserResource`, `SummaryResource`, `FlashcardResource`, `QuizResource`, `BadgeResource`) but no controller uses them. Controllers still return raw arrays/collections. Consistent JSON shapes would help the frontend.

**Affected controllers:** All 9 API controllers.

### 9. Onboarding.vue data format mismatch with preferences API

**File:** `resources/js/pages/Onboarding.vue:229-233`

Sends `learning_goals` as a JS array but `UserPreference` model stores it as JSON. May need explicit `JSON.stringify()` on the array value before sending.

---

## Medium Priority (quality, resilience, polish)

### 10. No axios 401 interceptor

**Files:** `resources/js/bootstrap.js`, all Vue pages

No Vue page intercepts 401 responses to redirect to login. If Sanctum token/cookie expires, the user sees a generic "Could not load content" error instead of being redirected to `/login`.

```js
// Add to bootstrap.js:
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)
```

### 11. No rate limiting on API endpoints

All 24 API endpoints have no throttling. Quiz submission, upload creation, preferences updates could be spammed.

```php
// Add to bootstrap/app.php or routes/api.php:
Route::middleware('throttle:60,1')->group(...)
```

### 12. render.yaml has incorrect APP_NAME and undefined env group

**File:** `render.yaml:11,63`

```yaml
# Current (wrong — APP_NAME contains a URL):
- key: APP_NAME
  value: https://microlearn-ai-3hy3.onrender.com

# Should be:
- key: APP_NAME
  value: SkillSprint
```

The queue worker references `envVars: - fromGroup: skillsprint-env` but this env group is not defined in the yaml.

### 13. No empty-state handling for new users

New users with zero summaries, zero quiz attempts, zero flashcards, zero enrolled skills will encounter empty or error states on multiple pages. Some pages handle loading/skeleton states but behavior for truly empty data may break.

**Affected pages:** Dashboard, Flashcards, Quizzes, Analytics, Achievements, Skills.

### 14. UpdateStreak middleware writes to DB on every API request

**File:** `app/Http/Middleware/UpdateStreak.php:16`

The middleware calls `$this->gamification->updateStreak($user)` which performs `$user->save()` on every authenticated API call, even if the streak doesn't change. Could cause unnecessary DB writes under load.

**Fix options:**
- Only save if `$user->isDirty()`
- Use a debounced/queued update instead of inline

### 15. PDF/DOCX extraction fails silently

**File:** `app/Http/Controllers/Api/UploadController.php:111,128,133`

When text extraction fails, the method returns `''` (empty string). `ProcessUploadJob` checks `if (empty(trim($content)))` and marks the upload as `failed` with a generic "Processing failed. Please try again." message. No specific error message about parsing.

### 16. Vue pages do full page reloads — no SPA router

Every navigation causes a full page reload. `vue-router` is not installed. All routing is handled by Laravel `Route::view()`. Acceptable for MVP but degrades UX significantly.

### 17. Missing `withCredentials: true` for cross-origin Sanctum auth

**File:** `resources/js/bootstrap.js`

If the frontend and API are deployed to different domains, Sanctum's cookie-based SPA auth requires `withCredentials: true` on axios. Currently not configured.

---

## Low Priority (nice to have)

### 18. Complete skipped tests

| Test file | Skipped tests | Reason |
|-----------|--------------|--------|
| `tests/Feature/Api/AuthTest.php` | 1 | Middleware configuration debugging |
| `tests/Feature/Api/DashboardTest.php` | 1 | Middleware configuration debugging |
| `tests/Feature/Api/FlashcardTest.php` | 2 | Requires seeded summary data |
| `tests/Feature/Api/QuizTest.php` | 2 | Requires seeded summary/upload data |
| `tests/Feature/Api/UploadTest.php` | 1 | Requires GEMINI_API_KEY in test env |

### 19. Add pagination

No endpoints paginate results. Lists that should have pagination:
- `GET /api/skills` (18 skills, will grow)
- `GET /api/achievements` leaderboard (will grow with users)
- `GET /api/user/enrolled-skills`

### 20. Create SkillResource

`SkillResource` was listed in the implementation plan but not created. Only 5 of the 6 planned resources exist.

### 21. Clean up unused CSS file

**File:** `resources/css/app.css`

Exists but is unused. The build imports `pages.css`, `landing.css`, and `auth.css`.

### 22. Add input sanitization middleware

No XSS or input sanitization on text inputs. The paste-text upload accepts 50,000 characters raw.

### 23. Seed data diversity for analytics demo

Current seeders create activity for user_id=1 only. No quiz attempts are seeded (analytics shows empty charts for new users). Adding seed quiz attempts would make the analytics demo more illustrative.

### 24. Accessibility audit (WCAG 2.2 AA)

- Keyboard navigation through all interactive elements
- Focus indicator visibility on all focusable elements
- Color contrast ratios meet AA minimums
- Screen reader testing (NVDA / VoiceOver)
- ARIA labels on all interactive elements

### 25. Cross-browser testing

- Chrome, Firefox, Edge, Safari
- Mobile responsive breakpoints at 991px and below
- Touch interaction on flashcards (tap to flip)

### 26. Docker build verification

The `Dockerfile` at the project root exists but has not been tested:

```bash
docker build -t skillsprint:local .
docker run -p 8080:80 --env-file .env skillsprint:local
```

### 27. Error state testing

- 404 handling for invalid summary/quiz/flashcard IDs
- 500 error handling for server failures
- Empty state handling (no skills, no badges, no uploads)

### 28. Run `npm run build` before deployment

Frontend assets need to be rebuilt before deploying to production:

```bash
npm run build
```

### 29. Verify PostgreSQL compatibility

Some migrations use PostgreSQL-incompatible features (e.g. `unsignedTinyInteger`). The existing deployment uses Supabase PostgreSQL. Verify all migrations run cleanly against PostgreSQL.

---

## Summary — Priority Ranking

| # | Priority | Item | Estimated Effort |
|---|----------|------|------------------|
| 1 | Critical | Install pdfparser + phpword | 1 command |
| 2 | Critical | Fix Onboarding.vue POST→PUT | 1 line |
| 3 | Critical | Fix `<router-link>` → `<a>` | 1 line |
| 4 | Critical | Populate achievements_unlocked in results() | 5-10 lines |
| 5 | Critical | Resolve QuickStart auth conflict | 1 route change |
| 6 | High | Wire Skills.vue to enrollment API | ~20 lines |
| 7 | High | Fix hardcoded flashcard deck title | 2 lines |
| 8 | High | Use API Resources in controllers | ~50 lines across controllers |
| 9 | High | Fix Onboarding data format | ~5 lines |
| 10 | Medium | Add axios 401 interceptor | ~10 lines |
| 11 | Medium | Add API rate limiting | ~5 lines config |
| 12 | Medium | Fix render.yaml APP_NAME + env group | 2 lines |
| 13 | Medium | Empty-state handling for new users | ~30 lines per page |
| 14 | Medium | Optimize UpdateStreak writes | ~5 lines |
| 15 | Medium | Better PDF/DOCX error messages | ~10 lines |
| 16 | Medium | Install vue-router for SPA | Large refactor |
| 17 | Medium | Add withCredentials to axios | 1 line |
| 18 | Low | Complete skipped tests | ~50 lines per test |
| 19 | Low | Add pagination | ~5 lines per endpoint |
| 20 | Low | Create SkillResource | ~15 lines |
| 21 | Low | Clean up unused app.css | Delete file |
| 22-29 | Low | QA, audit, testing, deployment verification | Hours |

---

## Files Referenced

| File | Issue # |
|------|---------|
| `app/Http/Controllers/Api/UploadController.php` | #1, #15 |
| `resources/js/pages/Onboarding.vue` | #2, #3, #9 |
| `app/Http/Controllers/Api/QuizController.php` | #4 |
| `resources/js/pages/QuickStart.vue` | #5 |
| `resources/js/pages/Skills.vue` | #6 |
| `app/Http/Controllers/Api/FlashcardController.php` | #7 |
| `app/Http/Resources/*` (5 files) | #8 |
| `resources/js/bootstrap.js` | #10, #17 |
| `render.yaml` | #12 |
| `app/Http/Middleware/UpdateStreak.php` | #14 |
| `resources/css/app.css` | #21 |