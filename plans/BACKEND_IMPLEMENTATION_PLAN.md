# SkillSprint — Backend Implementation Plan

> Generated 2026-06-11 | Translates `BACKEND_LEFT_TO_DO.md` into step-by-step implementation instructions.
> Work through sections in order — each section builds on the previous one.

---

## Table of Contents

1. [Phase 1 — Authentication Foundation (BLOCKING)](#phase-1--authentication-foundation-blocking)
2. [Phase 2 — Model & Database Fixes](#phase-2--model--database-fixes)
3. [Phase 3 — De-hardcode All Controllers](#phase-3--de-hardcode-all-controllers)
4. [Phase 4 — Infrastructure Scaffolding](#phase-4--infrastructure-scaffolding)
5. [Phase 5 — Gamification Service](#phase-5--gamification-service)
6. [Phase 6 — Upload Pipeline & Job Processing](#phase-6--upload-pipeline--job-processing)
7. [Phase 7 — Accessibility Persistence](#phase-7--accessibility-persistence)
8. [Phase 8 — Seeder & Data Improvements](#phase-8--seeder--data-improvements)
9. [Phase 9 — FormRequest Validation & API Resources](#phase-9--formrequest-validation--api-resources)
10. [Phase 10 — Testing](#phase-10--testing)
11. [Phase 11 — Deployment Readiness](#phase-11--deployment-readiness)

---

## Phase 1 — Authentication Foundation (BLOCKING)

> Every other phase depends on this. Do not skip ahead. Every controller currently hardcodes `user_id = 1`; that cannot be replaced until real authentication exists.

### Step 1.1 — Install Laravel Sanctum

Run the following in the project root:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

The publish command creates `config/sanctum.php` and adds the `personal_access_tokens` migration. Running `migrate` creates that table.

Verify that `composer.json` now lists `laravel/sanctum` under `require`.

---

### Step 1.2 — Add `HasApiTokens` to the User model

Open `app/Models/User.php`. Add the `HasApiTokens` trait to the `use` statement:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```

---

### Step 1.3 — Register Sanctum middleware in `bootstrap/app.php`

Open `bootstrap/app.php`. Inside the `->withMiddleware()` closure, add Sanctum's stateful middleware so that cookie-based SPA authentication works:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();
})
```

If the file already has a `withMiddleware` call, add `$middleware->statefulApi();` inside the existing closure rather than creating a second one.

---

### Step 1.4 — Create Auth controllers

Create a new file `app/Http/Controllers/Api/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user  = Auth::user();
        $token = $user->createToken('spa')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
```

---

### Step 1.5 — Add auth routes and protect all existing routes in `routes/api.php`

Replace the entire contents of `routes/api.php` with the following. The structure wraps all data routes inside `auth:sanctum` middleware so unauthenticated requests receive a 401 instead of real data:

```php
<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FlashcardController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public auth routes — no middleware
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// All other routes require a valid Sanctum token
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::get('/user',              [UserController::class, 'show']);
    Route::get('/user/preferences',  [UserController::class, 'preferences']);
    Route::put('/user/preferences',  [UserController::class, 'updatePreferences']);

    Route::get('/skills',            [SkillController::class, 'index']);
    Route::get('/skills/{id}',       [SkillController::class, 'show']);
    Route::post('/user/enroll',      [UserController::class, 'enroll']);
    Route::get('/user/enrolled-skills',        [UserController::class, 'enrolledSkills']);
    Route::delete('/user/enrolled-skills/{id}', [UserController::class, 'unenroll']);

    Route::get('/dashboard',         [DashboardController::class, 'index']);

    Route::get('/uploads/recent',    [UploadController::class, 'recent']);
    Route::post('/uploads',          [UploadController::class, 'store']);
    Route::get('/uploads/{id}/status', [UploadController::class, 'status']);

    Route::get('/summaries/{summary}', [SummaryController::class, 'show']);

    Route::get('/flashcards/{deckId}',                  [FlashcardController::class, 'show']);
    Route::patch('/flashcards/{deckId}/cards/{cardId}', [FlashcardController::class, 'updateCard']);

    Route::get('/quizzes/{quizId}',          [QuizController::class, 'show']);
    Route::post('/quizzes/{quizId}/submit',  [QuizController::class, 'submit']);
    Route::get('/quizzes/{quizId}/results',  [QuizController::class, 'results']);

    Route::get('/analytics',         [AnalyticsController::class, 'index']);
    Route::get('/achievements',      [AchievementController::class, 'index']);
});
```

---

### Step 1.6 — Replace `user_id = 1` in every controller

Now that `auth:sanctum` middleware is on every protected route, `$request->user()` and `auth()->id()` return real data. Do a global search across `app/Http/Controllers/Api/` for the string `user_id', 1` and `find(1)` and replace each occurrence.

The complete list of replacements:

**`DashboardController.php`**
- `Summary::with('flashcards')->where('user_id', 1)` → `Summary::with('flashcards')->where('user_id', auth()->id())`
- `ActivityLog::where('user_id', 1)` → `ActivityLog::where('user_id', auth()->id())`

**`UploadController.php`**
- `Upload::where('user_id', 1)` → `Upload::where('user_id', auth()->id())`

**`FlashcardController.php`** (two occurrences)
- `UserFlashcardProgress::where('user_id', 1)` → `UserFlashcardProgress::where('user_id', auth()->id())`
- `UserFlashcardProgress::firstOrCreate(['user_id' => 1, ...]` → `UserFlashcardProgress::firstOrCreate(['user_id' => auth()->id(), ...]`

**`AchievementController.php`** (four occurrences)
- `User::find(1)` → `$request->user()` (add `Request $request` to the method signature)
- `UserBadge::where('user_id', 1)` (two times) → `UserBadge::where('user_id', auth()->id())`
- `ActivityLog::where('user_id', 1)` → `ActivityLog::where('user_id', auth()->id())`

**`UserController.php`** (three occurrences)
- `User::find(1)` → `$request->user()`
- `UserPreference::where('user_id', 1)` (two times) → `UserPreference::where('user_id', auth()->id())`

Note: For controllers that don't yet inject `Request $request`, add it to the method signature and add the `use Illuminate\Http\Request;` import at the top of the file.

---

## Phase 2 — Model & Database Fixes

### Step 2.1 — Fix the `User` model

The current `User` model only has `name`, `email`, `password` as fillable. Replace `app/Models/User.php` with the following complete version:

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'xp_total',
        'level',
        'daily_goal_minutes',
        'streak_current',
        'streak_best',
        'onboarding_completed_at',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'last_active_at'          => 'datetime',
            'password'                => 'hashed',
            'xp_total'                => 'integer',
            'level'                   => 'integer',
            'daily_goal_minutes'      => 'integer',
            'streak_current'          => 'integer',
            'streak_best'             => 'integer',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function preference()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }

    public function summaries()
    {
        return $this->hasMany(Summary::class);
    }

    public function xpLogs()
    {
        return $this->hasMany(XpLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    public function flashcardProgress()
    {
        return $this->hasMany(UserFlashcardProgress::class);
    }

    public function enrolledSkills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->withPivot('progress_percent', 'enrolled_at', 'completed_at')
            ->withTimestamps();
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
```

---

### Step 2.2 — Create the `quiz_attempts` migration and model

Create the migration file `database/migrations/2025_01_01_000014_create_quiz_attempts_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->json('answers');         // array of { question_id, selected }
            $table->unsignedSmallInteger('correct');
            $table->unsignedSmallInteger('wrong');
            $table->unsignedSmallInteger('skipped');
            $table->unsignedTinyInteger('accuracy');  // 0-100
            $table->string('grade', 2);               // A, B, C, D
            $table->boolean('passed')->default(false);
            $table->unsignedInteger('xp_earned')->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
```

Create the model `app/Models/QuizAttempt.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id', 'quiz_id', 'answers', 'correct', 'wrong', 'skipped',
        'accuracy', 'grade', 'passed', 'xp_earned', 'duration_seconds', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers'      => 'array',
            'passed'       => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
```

---

### Step 2.3 — Create the `user_skills` migration and model

Create `database/migrations/2025_01_01_000015_create_user_skills_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->unique(['user_id', 'skill_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_skills');
    }
};
```

Run the new migrations:

```bash
php artisan migrate
```

---

### Step 2.4 — Document the no-op migration

Open `database/migrations/2025_01_01_000001_add_columns_to_users_table.php`. Add a comment at the top of `up()` so future developers understand why it is empty:

```php
public function up(): void
{
    // Intentionally empty. All user columns were consolidated into
    // 0001_01_01_000000_create_users_table.php for PostgreSQL compatibility.
    // This file is retained to keep the migration history sequential.
}
```

---

## Phase 3 — De-hardcode All Controllers

### Step 3.1 — Fix `DashboardController`

The entire `index()` method currently returns hardcoded numbers. Replace it with real queries. Open `app/Http/Controllers/Api/DashboardController.php` and rewrite it fully:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\QuizAttempt;
use App\Models\Skill;
use App\Models\Summary;
use App\Models\UserFlashcardProgress;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ── Stats ─────────────────────────────────────────────────────────────
        // XP delta: sum of XP earned in the last 24 hours from activity log
        $xpDelta = ActivityLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->sum('xp');

        // Lessons completed: count of distinct summaries the user has started
        $lessonsCompleted = Summary::where('user_id', $user->id)->count();

        // Quiz accuracy: average accuracy across all quiz attempts
        $quizAccuracy = QuizAttempt::where('user_id', $user->id)->avg('accuracy') ?? 0;

        // ── Continue learning ──────────────────────────────────────────────────
        // Most recently created summary for this user
        $latestSummary = Summary::with('flashcards')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $continueLearning = null;
        if ($latestSummary) {
            $totalCards   = $latestSummary->flashcards->count();
            $masteredCards = UserFlashcardProgress::where('user_id', $user->id)
                ->whereIn('flashcard_id', $latestSummary->flashcards->pluck('id'))
                ->where('status', 'mastered')
                ->count();
            $progress = $totalCards > 0 ? round(($masteredCards / $totalCards) * 100) : 0;

            $continueLearning = [
                'title'           => $latestSummary->title,
                'progress'        => $progress,
                'flashcard_count' => $totalCards,
                'minutes_left'    => max(1, $latestSummary->estimated_minutes - (int) ($progress / 100 * $latestSummary->estimated_minutes)),
                'summary_id'      => $latestSummary->id,
            ];
        }

        // ── Recommended skills ────────────────────────────────────────────────
        // Popular skills the user has not yet enrolled in
        $enrolledSkillIds = $user->enrolledSkills()->pluck('skills.id');
        $recommended = Skill::where('is_popular', true)
            ->whereNotIn('id', $enrolledSkillIds)
            ->take(4)
            ->get()
            ->map(fn($s) => [
                'id'      => $s->id,
                'title'   => $s->title,
                'level'   => $s->level,
                'minutes' => $s->estimated_minutes,
                'tags'    => $s->tags,
            ]);

        // ── Daily challenge ────────────────────────────────────────────────────
        // Use the most recent quiz the user has NOT yet attempted today
        $attemptedToday = QuizAttempt::where('user_id', $user->id)
            ->whereDate('completed_at', today())
            ->pluck('quiz_id');

        $dailyQuiz = \App\Models\Quiz::whereNotIn('id', $attemptedToday)->latest()->first();
        $dailyChallenge = null;
        if ($dailyQuiz) {
            $dailyChallenge = [
                'title'      => $dailyQuiz->title,
                'questions'  => $dailyQuiz->question_count,
                'difficulty' => $dailyQuiz->difficulty,
                'quiz_id'    => $dailyQuiz->id,
                'resets_at'  => now()->endOfDay()->toISOString(),
            ];
        }

        // ── Recent activity ────────────────────────────────────────────────────
        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'event'       => $a->event,
                'description' => $a->description,
                'xp'          => $a->xp,
                'created_at'  => $a->created_at->diffForHumans(),
            ]);

        // ── Progress overview ──────────────────────────────────────────────────
        $enrolledSkills = $user->enrolledSkills()->withPivot('progress_percent')->get();
        $totalEnrolled  = $enrolledSkills->count();
        $completed      = $enrolledSkills->where('pivot.progress_percent', 100)->count();
        $inProgress     = $enrolledSkills->whereBetween('pivot.progress_percent', [1, 99])->count();
        $notStarted     = $enrolledSkills->where('pivot.progress_percent', 0)->count();

        // ── Active skills ──────────────────────────────────────────────────────
        $activeSkills = $enrolledSkills
            ->whereBetween('pivot.progress_percent', [1, 99])
            ->take(5)
            ->map(fn($s) => [
                'skill_id' => $s->id,
                'title'    => $s->title,
                'progress' => $s->pivot->progress_percent,
            ])
            ->values();

        return response()->json([
            'stats' => [
                'xp'                  => $user->xp_total ?? 0,
                'xp_delta'            => $xpDelta,
                'streak'              => $user->streak_current ?? 0,
                'streak_best'         => $user->streak_best ?? 0,
                'lessons'             => $lessonsCompleted,
                'quiz_accuracy'       => round($quizAccuracy),
            ],
            'continue_learning'  => $continueLearning,
            'recommended'        => $recommended,
            'daily_challenge'    => $dailyChallenge,
            'recent_activity'    => $recentActivity,
            'progress_overview'  => [
                'total'       => $totalEnrolled,
                'completed'   => $completed,
                'in_progress' => $inProgress,
                'not_started' => $notStarted,
            ],
            'active_skills' => $activeSkills,
        ]);
    }
}
```

---

### Step 3.2 — Fix `QuizController::submit()` and `results()`

Open `app/Http/Controllers/Api/QuizController.php`. The `submit()` method calculates correct/wrong/skipped accurately but returns hardcoded XP and never saves the attempt. The `results()` method returns entirely fake data.

Replace the full file:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private GamificationService $gamification) {}

    public function show($quizId)
    {
        $quiz = Quiz::with('questions')->findOrFail($quizId);

        return response()->json([
            'quiz' => [
                'id'             => $quiz->id,
                'title'          => $quiz->title,
                'mode'           => $quiz->mode,
                'difficulty'     => $quiz->difficulty,
                'question_count' => $quiz->question_count,
            ],
            'questions' => $quiz->questions->map(fn($q) => [
                'id'         => $q->id,
                'body'       => $q->body,
                'options'    => $q->options,
                'difficulty' => $q->difficulty,
                'xp_reward'  => $q->xp_reward,
            ]),
        ]);
    }

    public function submit(Request $request, $quizId)
    {
        $quiz    = Quiz::with('questions')->findOrFail($quizId);
        $user    = $request->user();
        $answers = $request->input('answers', []);
        $startedAt = $request->input('started_at');

        $correct = 0;
        $wrong   = 0;
        $skipped = 0;

        foreach ($answers as $answer) {
            $question = $quiz->questions->firstWhere('id', $answer['question_id']);
            if (empty($answer['selected'])) {
                $skipped++;
            } elseif ($question && $answer['selected'] === $question->correct_option) {
                $correct++;
            } else {
                $wrong++;
            }
        }

        $total    = $correct + $wrong + $skipped;
        $accuracy = ($correct + $wrong) > 0
            ? round(($correct / ($correct + $wrong)) * 100)
            : 0;
        $grade    = $accuracy >= 90 ? 'A' : ($accuracy >= 80 ? 'B' : ($accuracy >= 70 ? 'C' : 'D'));
        $passed   = $accuracy >= 70;

        // ── Duration ────────────────────────────────────────────────────────────
        $durationSeconds = $startedAt
            ? now()->diffInSeconds(\Carbon\Carbon::parse($startedAt))
            : null;

        // ── XP Calculation ───────────────────────────────────────────────────────
        // Base XP: sum of xp_reward for every correctly answered question
        $baseXp = $quiz->questions
            ->whereIn('id', collect($answers)->where(fn($a) => !empty($a['selected']))->pluck('question_id'))
            ->sum('xp_reward');

        // Bonuses
        $streakBonus       = ($user->streak_current >= 3) ? 15 : 0;
        $perfectBonus      = ($accuracy === 100) ? 25 : 0;
        $speedBonus        = ($durationSeconds && $durationSeconds < 180) ? 10 : 0;
        $firstAttemptBonus = QuizAttempt::where('user_id', $user->id)->where('quiz_id', $quizId)->count() === 0 ? 5 : 0;
        $totalXp           = $baseXp + $streakBonus + $perfectBonus + $speedBonus + $firstAttemptBonus;

        // ── Persist attempt ──────────────────────────────────────────────────────
        $attempt = QuizAttempt::create([
            'user_id'          => $user->id,
            'quiz_id'          => $quiz->id,
            'answers'          => $answers,
            'correct'          => $correct,
            'wrong'            => $wrong,
            'skipped'          => $skipped,
            'accuracy'         => $accuracy,
            'grade'            => $grade,
            'passed'           => $passed,
            'xp_earned'        => $totalXp,
            'duration_seconds' => $durationSeconds,
            'completed_at'     => now(),
        ]);

        // ── Award XP & check badges ──────────────────────────────────────────────
        $this->gamification->awardXp($user, 'quiz_completed', $totalXp);
        $this->gamification->updateStreak($user);
        $unlockedBadges = $this->gamification->checkBadgeUnlock($user);

        return response()->json([
            'attempt_id' => $attempt->id,
            'quiz'       => ['title' => $quiz->title, 'completed_at' => now()->toISOString(), 'mode' => $quiz->mode],
            'score'      => compact('correct', 'wrong', 'skipped', 'accuracy', 'grade', 'passed'),
            'xp'         => [
                'earned'              => $baseXp,
                'streak_bonus'        => $streakBonus,
                'speed_bonus'         => $speedBonus,
                'first_attempt_bonus' => $firstAttemptBonus,
                'perfect_bonus'       => $perfectBonus,
                'total'               => $totalXp,
            ],
            'achievements_unlocked' => $unlockedBadges,
            'redirect'              => "/quizzes/{$quizId}/results",
        ]);
    }

    public function results(Request $request, $quizId)
    {
        $quiz = Quiz::with('questions')->findOrFail($quizId);
        $user = $request->user();

        // Get the most recent attempt for this user+quiz
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->latest('completed_at')
            ->firstOrFail();

        // Map submitted answers against correct answers
        $answerMap = collect($attempt->answers)->keyBy('question_id');

        $questions = $quiz->questions->map(function ($q) use ($answerMap) {
            $submitted = $answerMap->get($q->id);
            $selected  = $submitted['selected'] ?? null;
            $isCorrect = $selected === $q->correct_option;

            return [
                'body'           => $q->body,
                'options'        => $q->options,
                'user_answer'    => $selected,
                'correct_answer' => $q->correct_option,
                'explanation'    => $q->explanation,
                'is_correct'     => $isCorrect,
                'xp'             => $q->xp_reward,
                'tag'            => ($q->type ?? 'Foundation') . ' · ' . $q->difficulty,
            ];
        });

        // Derive mastered vs needs-practice from correct/incorrect answers
        $masteredSkills  = $questions->where('is_correct', true)->pluck('tag')->unique()->values();
        $needsPractice   = $questions->where('is_correct', false)->pluck('tag')->unique()->values();

        return response()->json([
            'quiz' => [
                'title'            => $quiz->title,
                'completed_at'     => $attempt->completed_at?->toISOString(),
                'duration_seconds' => $attempt->duration_seconds,
                'mode'             => $quiz->mode,
            ],
            'score' => [
                'correct'  => $attempt->correct,
                'wrong'    => $attempt->wrong,
                'skipped'  => $attempt->skipped,
                'accuracy' => $attempt->accuracy,
                'grade'    => $attempt->grade,
                'passed'   => $attempt->passed,
            ],
            'xp' => [
                'earned' => $attempt->xp_earned,
            ],
            'mastered_skills'       => $masteredSkills,
            'needs_practice'        => $needsPractice,
            'questions'             => $questions,
            'achievements_unlocked' => [],
        ]);
    }
}
```

---

### Step 3.3 — Fix `AnalyticsController`

Every single data point in this controller is hardcoded. Replace the full file:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\QuizAttempt;
use App\Models\Summary;
use App\Models\UserFlashcardProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $range = $request->input('range', 'week');

        $since = match ($range) {
            'month' => now()->subMonth(),
            'all'   => now()->subYear(),
            default => now()->subWeek(),
        };

        // ── Top-level stats ─────────────────────────────────────────────────────
        $totalMinutes   = ActivityLog::where('user_id', $user->id)->sum(DB::raw('COALESCE(xp, 0)')) / 10; // rough proxy
        $quizAccuracy   = QuizAttempt::where('user_id', $user->id)->avg('accuracy') ?? 0;
        $xpEarned       = ActivityLog::where('user_id', $user->id)->where('created_at', '>=', $since)->sum('xp');
        $lessonsCompleted = Summary::where('user_id', $user->id)->count();

        // ── Weekly progress (XP per day for last 7 days) ─────────────────────────
        $weeklyProgress = collect(range(6, 0))->map(function ($daysAgo) use ($user) {
            return (int) ActivityLog::where('user_id', $user->id)
                ->whereDate('created_at', now()->subDays($daysAgo)->toDateString())
                ->sum('xp');
        })->values()->toArray();

        // ── Quiz accuracy by subject ────────────────────────────────────────────
        // Group quiz attempts by quiz title, average accuracy per quiz
        $quizAccuracyBySubject = QuizAttempt::where('user_id', $user->id)
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->select('quizzes.title as label', DB::raw('ROUND(AVG(quiz_attempts.accuracy)) as value'))
            ->groupBy('quizzes.id', 'quizzes.title')
            ->get()
            ->toArray();

        // ── Skill progress from enrolled skills ──────────────────────────────────
        $skillProgress = $user->enrolledSkills()
            ->withPivot('progress_percent')
            ->get()
            ->map(fn($s) => [
                'title'       => $s->title,
                'level'       => $s->level,
                'proficiency' => $s->pivot->progress_percent,
            ])
            ->toArray();

        // ── Skill growth over weeks (flashcard mastery progression) ───────────────
        // For each enrolled skill, derive approximate weekly mastery from flashcard progress timestamps
        // This is computed at a summary level since flashcards belong to summaries
        $skillGrowthLabels = collect(range(7, 1))->map(fn($w) => "Wk {$w}")->values();
        $skillGrowthSeries = [];
        $summaries = Summary::where('user_id', $user->id)->with('flashcards')->get();
        foreach ($summaries->take(3) as $summary) {
            $flashcardIds = $summary->flashcards->pluck('id');
            $series = collect(range(7, 1))->map(function ($weeksAgo) use ($user, $flashcardIds) {
                $weeksAgoDate = now()->subWeeks($weeksAgo)->endOfWeek();
                return (int) UserFlashcardProgress::where('user_id', $user->id)
                    ->whereIn('flashcard_id', $flashcardIds)
                    ->where('status', 'mastered')
                    ->where('updated_at', '<=', $weeksAgoDate)
                    ->count();
            })->values()->toArray();
            $skillGrowthSeries[] = ['label' => $summary->title, 'values' => $series];
        }

        // ── AI insights remain static until Phase 6 is complete ─────────────────
        $aiInsights = [
            [
                'title' => 'Consistent Practice',
                'body'  => 'Keep up your daily learning habit to build long-term retention.',
            ],
        ];

        return response()->json([
            'stats' => [
                'total_hours'       => round($totalMinutes / 60, 1),
                'quiz_accuracy'     => round($quizAccuracy),
                'streak_days'       => $user->streak_current ?? 0,
                'lessons_completed' => $lessonsCompleted,
                'xp_earned'         => $xpEarned,
            ],
            'weekly_progress'          => $weeklyProgress,
            'skill_growth'             => [
                'weeks'  => $skillGrowthLabels,
                'series' => $skillGrowthSeries,
            ],
            'quiz_accuracy_by_subject' => $quizAccuracyBySubject,
            'skill_progress'           => $skillProgress,
            'ai_insights'              => $aiInsights,
        ]);
    }
}
```

---

### Step 3.4 — Fix `AchievementController`

Three things are hardcoded: the leaderboard (fake names), the user's rank (hardcoded 42), and days_active (hardcoded 48). Replace the relevant sections in `app/Http/Controllers/Api/AchievementController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementController extends Controller
{
    public function index(Request $request)
    {
        $user           = $request->user();
        $badges         = Badge::all();
        $earnedBadgeIds = UserBadge::where('user_id', $user->id)->pluck('badge_id')->toArray();

        $badgeData = $badges->map(function ($badge) use ($earnedBadgeIds, $user) {
            $earned    = in_array($badge->id, $earnedBadgeIds);
            $userBadge = $earned
                ? UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->first()
                : null;

            return [
                'slug'        => $badge->slug,
                'title'       => $badge->title,
                'description' => $badge->description,
                'icon'        => $badge->icon,
                'xp_reward'   => $badge->xp_reward,
                'earned'      => $earned,
                'is_new'      => $userBadge?->is_new ?? false,
                'earned_at'   => $userBadge?->earned_at?->toISOString(),
            ];
        });

        $xpHistory = ActivityLog::where('user_id', $user->id)
            ->where('xp', '>', 0)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'event'       => $a->event,
                'description' => $a->description,
                'xp'          => $a->xp,
                'created_at'  => $a->created_at->diffForHumans(),
            ]);

        // Real leaderboard: top 10 users by XP, mark current user
        $leaderboard = User::orderBy('xp_total', 'desc')
            ->take(10)
            ->get()
            ->map(fn($u, $index) => [
                'rank'            => $index + 1,
                'name'            => $u->name,
                'level'           => $u->level ?? 1,
                'xp'              => $u->xp_total ?? 0,
                'is_current_user' => $u->id === $user->id,
            ]);

        // Compute actual rank: count users with strictly higher XP + 1
        $rank = User::where('xp_total', '>', $user->xp_total ?? 0)->count() + 1;

        // Days active: count distinct calendar days with activity
        $daysActive = ActivityLog::where('user_id', $user->id)
            ->select(DB::raw('DATE(created_at) as day'))
            ->distinct()
            ->count('day');

        return response()->json([
            'profile' => [
                'name'          => $user->name,
                'bio'           => $user->bio,
                'xp'            => $user->xp_total ?? 0,
                'level'         => $user->level ?? 1,
                'streak'        => $user->streak_current ?? 0,
                'badges_earned' => count($earnedBadgeIds),
                'rank'          => $rank,
                'days_active'   => $daysActive,
            ],
            'badges'      => $badgeData,
            'xp_history'  => $xpHistory,
            'leaderboard' => $leaderboard,
        ]);
    }
}
```

---

### Step 3.5 — Fix `UserController` enrollment methods

The `UserController` needs three new methods. Also fix `show()` and `preferences()` to use the authenticated user. Replace `app/Http/Controllers/Api/UserController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\UserPreference;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id'                     => $user->id,
            'name'                   => $user->name,
            'email'                  => $user->email,
            'avatar'                 => $user->avatar,
            'bio'                    => $user->bio,
            'xp_total'               => $user->xp_total,
            'level'                  => $user->level,
            'daily_goal_minutes'     => $user->daily_goal_minutes,
            'streak_current'         => $user->streak_current,
            'streak_best'            => $user->streak_best,
            'onboarding_completed_at'=> $user->onboarding_completed_at,
            'last_active_at'         => $user->last_active_at,
        ]);
    }

    public function preferences(Request $request)
    {
        $prefs = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);

        return response()->json($prefs);
    }

    public function updatePreferences(Request $request)
    {
        $prefs = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $prefs->update($request->all());

        return response()->json($prefs);
    }

    public function enroll(Request $request)
    {
        $request->validate(['skill_id' => 'required|exists:skills,id']);
        $user = $request->user();

        // Attach if not already enrolled; silently ignore duplicates
        if (!$user->enrolledSkills()->where('skill_id', $request->skill_id)->exists()) {
            $user->enrolledSkills()->attach($request->skill_id, [
                'progress_percent' => 0,
                'enrolled_at'      => now(),
            ]);
        }

        return response()->json(['enrolled' => true, 'skill_id' => $request->skill_id]);
    }

    public function enrolledSkills(Request $request)
    {
        $skills = $request->user()->enrolledSkills()->withPivot('progress_percent', 'enrolled_at', 'completed_at')->get();

        return response()->json($skills);
    }

    public function unenroll(Request $request, $skillId)
    {
        $request->user()->enrolledSkills()->detach($skillId);

        return response()->json(['unenrolled' => true, 'skill_id' => $skillId]);
    }
}
```

---

## Phase 4 — Infrastructure Scaffolding

### Step 4.1 — Create required directories

These directories do not exist. Create them:

```bash
mkdir -p app/Services
mkdir -p app/Jobs
mkdir -p app/Http/Middleware
mkdir -p app/Http/Requests
mkdir -p app/Http/Resources
mkdir -p app/Enums
mkdir -p app/Policies
```

Or via artisan (which also creates stub files):

```bash
php artisan make:enum UploadStatus
php artisan make:enum FlashcardStatus
```

---

### Step 4.2 — Create `UploadStatus` and `FlashcardStatus` enums

**`app/Enums/UploadStatus.php`**:

```php
<?php

namespace App\Enums;

enum UploadStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Done       = 'done';
    case Failed     = 'failed';
}
```

**`app/Enums/FlashcardStatus.php`**:

```php
<?php

namespace App\Enums;

enum FlashcardStatus: string
{
    case Unseen  = 'unseen';
    case Current = 'current';
    case Saved   = 'saved';
    case Mastered = 'mastered';
}
```

---

### Step 4.3 — Create Authorization Policies

Generate stub policies:

```bash
php artisan make:policy UploadPolicy --model=Upload
php artisan make:policy SummaryPolicy --model=Summary
php artisan make:policy QuizAttemptPolicy --model=QuizAttempt
```

For each policy, implement the `view` method to ensure users can only see their own data. Example for `UploadPolicy`:

```php
public function view(User $user, Upload $upload): bool
{
    return $user->id === $upload->user_id;
}
```

Register the policies in `app/Providers/AppServiceProvider.php` by adding the `Gate::policy()` calls to the `boot()` method:

```php
use App\Models\Upload;
use App\Policies\UploadPolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(Upload::class, UploadPolicy::class);
    // repeat for Summary, QuizAttempt
}
```

In controllers that deal with individual resources, call `$this->authorize('view', $upload)` to enforce ownership.

---

## Phase 5 — Gamification Service

### Step 5.1 — Create `GamificationService`

Create `app/Services/GamificationService.php`. This service is the single source of truth for XP, levels, streaks, and badge unlocks.

```php
<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Badge;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserFlashcardProgress;
use App\Models\XpLog;
use Carbon\Carbon;

class GamificationService
{
    /**
     * Award XP to a user for a given event.
     * Creates an XpLog entry and updates user.xp_total and user.level.
     */
    public function awardXp(User $user, string $event, int $xp): void
    {
        if ($xp <= 0) return;

        XpLog::create([
            'user_id'     => $user->id,
            'event'       => $event,
            'description' => $this->descriptionFor($event),
            'xp'          => $xp,
        ]);

        ActivityLog::create([
            'user_id'     => $user->id,
            'event'       => $event,
            'description' => $this->descriptionFor($event),
            'xp'          => $xp,
        ]);

        $user->xp_total = ($user->xp_total ?? 0) + $xp;
        $user->level    = $this->calculateLevel($user->xp_total);
        $user->save();
    }

    /**
     * Recalculate level from total XP.
     * Formula: level = floor(xp_total / 200) + 1, capped at 100.
     */
    public function calculateLevel(int $xpTotal): int
    {
        return min(100, (int) floor($xpTotal / 200) + 1);
    }

    /**
     * Update streak based on last_active_at.
     * - Same day: no change.
     * - Yesterday: increment streak_current, update streak_best.
     * - Older: reset streak_current to 1.
     * Always updates last_active_at.
     */
    public function updateStreak(User $user): void
    {
        $now          = Carbon::now();
        $lastActive   = $user->last_active_at ? Carbon::parse($user->last_active_at) : null;

        if (!$lastActive) {
            // First ever activity
            $user->streak_current = 1;
            $user->streak_best    = 1;
        } elseif ($lastActive->isToday()) {
            // Already active today — streak unchanged
        } elseif ($lastActive->isYesterday()) {
            $user->streak_current = ($user->streak_current ?? 0) + 1;
            if ($user->streak_current > ($user->streak_best ?? 0)) {
                $user->streak_best = $user->streak_current;
            }
        } else {
            // Gap of more than 1 day — reset
            $user->streak_current = 1;
        }

        $user->last_active_at = $now;
        $user->save();
    }

    /**
     * Check all badge criteria for a user and unlock any newly earned badges.
     * Returns an array of newly unlocked badge data for the response.
     */
    public function checkBadgeUnlock(User $user): array
    {
        $allBadges      = Badge::all();
        $earnedIds      = UserBadge::where('user_id', $user->id)->pluck('badge_id')->toArray();
        $newlyUnlocked  = [];

        foreach ($allBadges as $badge) {
            if (in_array($badge->id, $earnedIds)) continue;

            if ($this->criteriaIsMet($user, $badge->slug)) {
                UserBadge::create([
                    'user_id'   => $user->id,
                    'badge_id'  => $badge->id,
                    'earned_at' => now(),
                    'is_new'    => true,
                ]);

                // Award the badge's XP reward
                $this->awardXp($user, 'badge_unlocked', $badge->xp_reward);

                $newlyUnlocked[] = [
                    'slug'  => $badge->slug,
                    'title' => $badge->title,
                    'xp'    => $badge->xp_reward,
                ];
            }
        }

        return $newlyUnlocked;
    }

    /**
     * Evaluate badge unlock criteria by badge slug.
     * Add new cases here as new badges are introduced.
     */
    private function criteriaIsMet(User $user, string $slug): bool
    {
        return match ($slug) {
            // Completed first quiz
            'quiz-champion' => QuizAttempt::where('user_id', $user->id)->where('passed', true)->count() >= 1,

            // Scored 100% on any quiz
            'perfect-score' => QuizAttempt::where('user_id', $user->id)->where('accuracy', 100)->count() >= 1,

            // Completed a quiz in under 3 minutes
            'quick-learner' => QuizAttempt::where('user_id', $user->id)
                ->whereNotNull('duration_seconds')
                ->where('duration_seconds', '<', 180)
                ->where('passed', true)
                ->count() >= 1,

            // Mastered 100 flashcards
            'flashcard-hero' => UserFlashcardProgress::where('user_id', $user->id)
                ->where('status', 'mastered')
                ->count() >= 100,

            // 7-day streak
            'streak-master' => ($user->streak_current ?? 0) >= 7,

            // 30-day streak
            'streak-legend' => ($user->streak_current ?? 0) >= 30,

            // Reached level 10
            'level-10' => ($user->level ?? 1) >= 10,

            default => false,
        };
    }

    /**
     * Map event slugs to human-readable descriptions.
     */
    private function descriptionFor(string $event): string
    {
        return match ($event) {
            'quiz_completed'  => 'Completed a quiz',
            'badge_unlocked'  => 'Unlocked a badge',
            'flashcard_mastered' => 'Mastered a flashcard',
            'upload_processed' => 'Processed an upload',
            default           => ucfirst(str_replace('_', ' ', $event)),
        };
    }
}
```

Register `GamificationService` as a singleton in `app/Providers/AppServiceProvider.php` inside `register()`:

```php
use App\Services\GamificationService;

public function register(): void
{
    $this->app->singleton(GamificationService::class);
}
```

---

### Step 5.2 — Wire gamification into `FlashcardController::updateCard()`

When a card status changes to `mastered`, award XP and check badges. Update `updateCard()` in `app/Http/Controllers/Api/FlashcardController.php`:

```php
use App\Services\GamificationService;
use Illuminate\Http\Request;

public function __construct(private GamificationService $gamification) {}

public function updateCard(Request $request, $deckId, $cardId)
{
    $user     = $request->user();
    $newStatus = $request->input('status');

    $progress = UserFlashcardProgress::firstOrCreate(
        ['user_id' => $user->id, 'flashcard_id' => $cardId],
        ['status' => 'unseen']
    );

    $wasMastered = $progress->status === 'mastered';
    $progress->status = $newStatus;
    $progress->save();

    // Award XP only on the transition TO mastered (not if already was mastered)
    if ($newStatus === 'mastered' && !$wasMastered) {
        $this->gamification->awardXp($user, 'flashcard_mastered', 10);
        $this->gamification->checkBadgeUnlock($user);
    }

    return response()->json($progress);
}
```

---

### Step 5.3 — Add daily streak update middleware

Create `app/Http/Middleware/UpdateStreak.php`. This middleware runs on every authenticated request and keeps the streak current without needing a separate scheduler:

```php
<?php

namespace App\Http\Middleware;

use App\Services\GamificationService;
use Closure;
use Illuminate\Http\Request;

class UpdateStreak
{
    public function __construct(private GamificationService $gamification) {}

    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $this->gamification->updateStreak($request->user());
        }

        return $next($request);
    }
}
```

Register it in `bootstrap/app.php` inside `->withMiddleware()`:

```php
$middleware->appendToGroup('auth:sanctum', \App\Http\Middleware\UpdateStreak::class);
```

This appends the streak middleware to run on all sanctum-authenticated routes after authentication resolves.

---

## Phase 6 — Upload Pipeline & Job Processing

### Step 6.1 — Add Gemini config to `config/services.php`

Open `config/services.php` and add the following entry at the end of the returned array:

```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'model'   => env('GEMINI_MODEL', 'gemini-2.0-flash'),
],
```

Add `GEMINI_API_KEY=` and `GEMINI_MODEL=gemini-2.0-flash` to `.env.example`. Add your actual key to `.env`.

---

### Step 6.2 — Create `GeminiService`

Create `app/Services/GeminiService.php`. This service wraps all HTTP calls to the Google Gemini REST API.

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key')
            ?? throw new RuntimeException('GEMINI_API_KEY is not set in .env');
        $this->model  = config('services.gemini.model', 'gemini-2.0-flash');
    }

    /**
     * Generate a structured summary from raw text content.
     *
     * Returns an array with keys:
     *   - title: string
     *   - difficulty: 'Beginner'|'Intermediate'|'Advanced'
     *   - estimated_minutes: int
     *   - content_sections: array of { heading, body }
     *   - key_terms: array of { term, definition }
     *   - timeline_steps: array of { step, description }
     */
    public function generateSummary(string $content): array
    {
        $prompt = <<<PROMPT
You are an expert learning content creator. Analyze the following text and return a structured JSON summary.

Return ONLY valid JSON (no markdown, no code fences) with this exact shape:
{
  "title": "string — concise topic title",
  "difficulty": "Beginner | Intermediate | Advanced",
  "estimated_minutes": integer,
  "content_sections": [
    { "heading": "string", "body": "string" }
  ],
  "key_terms": [
    { "term": "string", "definition": "string" }
  ],
  "timeline_steps": [
    { "step": "string", "description": "string" }
  ]
}

TEXT TO ANALYZE:
{$content}
PROMPT;

        return $this->callApi($prompt);
    }

    /**
     * Generate flashcard question/answer pairs from content.
     *
     * Returns an array of { question: string, answer: string, category: string }
     */
    public function generateFlashcards(string $content, int $count = 12): array
    {
        $prompt = <<<PROMPT
You are a flashcard generator. Create exactly {$count} flashcards from the following text.

Return ONLY valid JSON (no markdown, no code fences) as an array:
[
  { "question": "string", "answer": "string", "category": "string" }
]

TEXT:
{$content}
PROMPT;

        $result = $this->callApi($prompt);
        // The response should be an array, not an object
        return is_array($result) && isset($result[0]) ? $result : ($result['flashcards'] ?? []);
    }

    /**
     * Generate a quiz from content.
     *
     * Returns an array of questions, each shaped as:
     * {
     *   body: string,
     *   options: string[4],
     *   correct_option: string (must match one of options exactly),
     *   explanation: string,
     *   difficulty: 'Easy'|'Medium'|'Hard',
     *   type: string,
     *   xp_reward: int
     * }
     */
    public function generateQuiz(string $content, int $questionCount = 10, string $difficulty = 'Medium'): array
    {
        $prompt = <<<PROMPT
You are a quiz generator. Create exactly {$questionCount} multiple-choice questions from the text below.
Difficulty level: {$difficulty}.

Return ONLY valid JSON (no markdown, no code fences) as an array:
[
  {
    "body": "string — the question text",
    "options": ["Option A", "Option B", "Option C", "Option D"],
    "correct_option": "string — must exactly match one of the options",
    "explanation": "string — why the correct option is right",
    "difficulty": "{$difficulty}",
    "type": "string — e.g. Conceptual, Applied, Recall",
    "xp_reward": integer between 10 and 30
  }
]

TEXT:
{$content}
PROMPT;

        $result = $this->callApi($prompt);
        return is_array($result) && isset($result[0]) ? $result : ($result['questions'] ?? []);
    }

    /**
     * Make a POST request to the Gemini generateContent endpoint.
     * Parses the JSON response and returns a decoded PHP array/object.
     */
    private function callApi(string $prompt): array
    {
        $url = "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(60)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 8192,
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException("Gemini API returned HTTP {$response->status()}");
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (!$text) {
            throw new RuntimeException('Gemini API returned an empty response');
        }

        // Strip markdown code fences if the model wrapped JSON in them anyway
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);
        $text = trim($text);

        $decoded = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gemini JSON parse error', ['text' => $text]);
            throw new RuntimeException('Failed to parse Gemini response as JSON: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
```

Register `GeminiService` as a singleton in `AppServiceProvider::register()`:

```php
use App\Services\GeminiService;

$this->app->singleton(GeminiService::class);
```

---

### Step 6.3 — Create `ProcessUploadJob`

Create `app/Jobs/ProcessUploadJob.php`. This queued job coordinates all AI generation steps for a single upload.

```php
<?php

namespace App\Jobs;

use App\Models\Flashcard;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Summary;
use App\Models\Upload;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public Upload $upload) {}

    public function handle(GeminiService $gemini): void
    {
        // ── Step 1: Mark as processing ────────────────────────────────────────────
        $this->upload->update(['status' => 'processing']);

        $content = $this->upload->raw_content;

        if (empty(trim($content))) {
            $this->upload->update(['status' => 'failed']);
            return;
        }

        // ── Step 2: Generate summary ──────────────────────────────────────────────
        $summaryData = $gemini->generateSummary($content);

        $summary = Summary::create([
            'user_id'            => $this->upload->user_id,
            'upload_id'          => $this->upload->id,
            'title'              => $summaryData['title'] ?? 'Untitled Summary',
            'difficulty'         => $summaryData['difficulty'] ?? 'Intermediate',
            'estimated_minutes'  => $summaryData['estimated_minutes'] ?? 10,
            'source_filename'    => $this->upload->original_filename,
            'content_sections'   => $summaryData['content_sections'] ?? [],
            'key_terms'          => $summaryData['key_terms'] ?? [],
            'timeline_steps'     => $summaryData['timeline_steps'] ?? [],
        ]);

        // ── Step 3: Generate flashcards ───────────────────────────────────────────
        $flashcardsData = $gemini->generateFlashcards($content, 12);

        foreach ($flashcardsData as $index => $card) {
            Flashcard::create([
                'summary_id' => $summary->id,
                'question'   => $card['question'] ?? 'Question unavailable',
                'answer'     => $card['answer'] ?? 'Answer unavailable',
                'category'   => $card['category'] ?? 'General',
                'sort_order' => $index + 1,
            ]);
        }

        // ── Step 4: Generate quiz ─────────────────────────────────────────────────
        $quizData = $gemini->generateQuiz($content, 10, 'Medium');

        $quiz = Quiz::create([
            'user_id'        => $this->upload->user_id,
            'summary_id'     => $summary->id,
            'title'          => $summary->title . ' — Quiz',
            'mode'           => 'practice',
            'question_count' => count($quizData),
            'difficulty'     => 'Medium',
        ]);

        foreach ($quizData as $index => $q) {
            QuizQuestion::create([
                'quiz_id'        => $quiz->id,
                'body'           => $q['body'] ?? 'Question unavailable',
                'options'        => $q['options'] ?? [],
                'correct_option' => $q['correct_option'] ?? '',
                'explanation'    => $q['explanation'] ?? '',
                'difficulty'     => $q['difficulty'] ?? 'Medium',
                'type'           => $q['type'] ?? 'Conceptual',
                'xp_reward'      => $q['xp_reward'] ?? 15,
                'sort_order'     => $index + 1,
            ]);
        }

        // ── Step 5: Mark done ─────────────────────────────────────────────────────
        $this->upload->update([
            'status'       => 'done',
            'processed_at' => now(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error("ProcessUploadJob failed for upload {$this->upload->id}: {$e->getMessage()}");

        $this->upload->update(['status' => 'failed']);
    }
}
```

---

### Step 6.4 — Create `UploadController::store()` and `status()`

Update `app/Http/Controllers/Api/UploadController.php` to add the two new methods:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessUploadJob;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function recent(Request $request)
    {
        $uploads = Upload::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return response()->json($uploads);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'       => 'required|in:text,file,url,sample',
            'text'       => 'required_if:type,text|nullable|string|min:50|max:50000',
            'file'       => 'required_if:type,file|nullable|file|mimes:pdf,docx,txt|max:10240',
            'url'        => 'required_if:type,url|nullable|url',
            'skill_id'   => 'required_if:type,sample|nullable|exists:skills,id',
        ]);

        $rawContent      = '';
        $originalFilename = null;
        $filePath        = null;

        switch ($request->type) {
            case 'text':
                $rawContent       = $request->text;
                $originalFilename = 'pasted-text.txt';
                break;

            case 'file':
                $file             = $request->file('file');
                $originalFilename = $file->getClientOriginalName();
                $filePath         = $file->store('uploads/' . $request->user()->id, 'local');
                $rawContent       = $this->extractTextFromFile($file);
                break;

            case 'url':
                $rawContent       = $this->fetchTextFromUrl($request->url);
                $originalFilename = parse_url($request->url, PHP_URL_HOST) . '.txt';
                break;

            case 'sample':
                $skill      = \App\Models\Skill::findOrFail($request->skill_id);
                $rawContent = $skill->description ?? $skill->title;
                $originalFilename = 'sample-' . $skill->slug . '.txt';
                break;
        }

        $upload = Upload::create([
            'user_id'           => $request->user()->id,
            'original_filename' => $originalFilename,
            'type'              => $request->type,
            'raw_content'       => $rawContent,
            'file_path'         => $filePath,
            'word_count'        => str_word_count($rawContent),
            'status'            => 'pending',
        ]);

        ProcessUploadJob::dispatch($upload);

        return response()->json(['upload_id' => $upload->id, 'status' => 'pending'], 202);
    }

    public function status(Request $request, $id)
    {
        $upload = Upload::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $summaryId = null;
        if ($upload->status === 'done') {
            $summary   = \App\Models\Summary::where('upload_id', $upload->id)->first();
            $summaryId = $summary?->id;
        }

        return response()->json([
            'id'            => $upload->id,
            'status'        => $upload->status,
            'summary_id'    => $summaryId,
            'error_message' => $upload->status === 'failed' ? 'Processing failed. Please try again.' : null,
        ]);
    }

    /**
     * Extract plain text from an uploaded file (txt, pdf, docx).
     * For now, txt is read directly. PDF/DOCX require additional libraries.
     */
    private function extractTextFromFile(\Illuminate\Http\UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'txt') {
            return file_get_contents($file->getRealPath());
        }

        // For PDF extraction, install: composer require smalot/pdfparser
        if ($ext === 'pdf') {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf    = $parser->parseFile($file->getRealPath());
                return $pdf->getText();
            } catch (\Throwable $e) {
                return '';
            }
        }

        // For DOCX extraction, install: composer require phpoffice/phpword
        if ($ext === 'docx') {
            try {
                $phpWord  = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath());
                $text     = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $text .= $element->getText() . "\n";
                        }
                    }
                }
                return $text;
            } catch (\Throwable $e) {
                return '';
            }
        }

        return '';
    }

    /**
     * Fetch plain text from a URL using Laravel's HTTP client.
     * Strips HTML tags to get readable content.
     */
    private function fetchTextFromUrl(string $url): string
    {
        $response = \Illuminate\Support\Facades\Http::timeout(15)->get($url);

        if (!$response->successful()) {
            return '';
        }

        return strip_tags($response->body());
    }
}
```

Install the optional text-extraction libraries for PDF and DOCX support:

```bash
composer require smalot/pdfparser
composer require phpoffice/phpword
```

---

### Step 6.5 — Configure the queue driver

The queue driver must be set to `database` (already configured per the audit report). Verify `.env` contains:

```
QUEUE_CONNECTION=database
```

Run the queue migrations if they don't already exist:

```bash
php artisan queue:table
php artisan migrate
```

To run the queue worker in development:

```bash
php artisan queue:work --tries=2 --timeout=120
```

For production, this should be managed by a process supervisor (see Phase 11).

---

## Phase 7 — Accessibility Persistence

### Step 7.1 — Apply preferences to the DOM in `AppLayout.vue`

The `Settings.vue` page already saves user preferences via `PUT /api/user/preferences`, but these preferences are never applied to the layout. The changes are entirely on the frontend.

Open `resources/js/layouts/AppLayout.vue`. Add the following logic to the `<script setup>` block:

```javascript
import { onMounted, watch } from 'vue'
import axios from 'axios'

const applyPreferences = (prefs) => {
  if (!prefs) return

  const body = document.body
  const root = document.documentElement

  // Dyslexia font
  body.classList.toggle('dyslexia-font', !!prefs.dyslexia_font)

  // High contrast
  body.classList.toggle('high-contrast', !!prefs.high_contrast)

  // Reduce motion
  body.classList.toggle('reduce-motion', !!prefs.reduce_motion)

  // Font size: store as CSS variable so all text scales
  if (prefs.font_size) {
    const sizeMap = { small: '14px', medium: '16px', large: '18px', xlarge: '20px' }
    root.style.setProperty('--font-size-base', sizeMap[prefs.font_size] ?? '16px')
  }

  // Line height
  if (prefs.line_height) {
    root.style.setProperty('--line-height-base', String(prefs.line_height))
  }

  // Letter spacing
  if (prefs.letter_spacing) {
    root.style.setProperty('--letter-spacing-base', `${prefs.letter_spacing}px`)
  }

  // Word spacing
  if (prefs.word_spacing) {
    root.style.setProperty('--word-spacing-base', `${prefs.word_spacing}px`)
  }
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/user/preferences')
    applyPreferences(data)
  } catch (e) {
    // Preferences are cosmetic; silently fail
  }
})
```

---

### Step 7.2 — Add CSS variable hooks to `pages.css`

In `resources/css/pages.css` (or the global stylesheet), add the following defaults at the `:root` level so the JS variables have fallbacks:

```css
:root {
  --font-size-base:       16px;
  --line-height-base:     1.5;
  --letter-spacing-base:  0px;
  --word-spacing-base:    0px;
}

body {
  font-size:      var(--font-size-base);
  line-height:    var(--line-height-base);
  letter-spacing: var(--letter-spacing-base);
  word-spacing:   var(--word-spacing-base);
}

body.dyslexia-font {
  font-family: 'OpenDyslexic', sans-serif;
}

body.high-contrast {
  --color-bg:   #000000;
  --color-text: #ffffff;
  background-color: var(--color-bg);
  color:            var(--color-text);
}

body.reduce-motion * {
  animation-duration:   0ms !important;
  transition-duration:  0ms !important;
}
```

Add OpenDyslexic as a font source. In the `<head>` of your Blade layout (`resources/views/app.blade.php`):

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/open-dyslexic@1.0.3/open-dyslexic-regular.min.css">
```

---

## Phase 8 — Seeder & Data Improvements

### Step 8.1 — Seed a second demo quiz

Open `database/seeders/QuizSeeder.php` (or whichever seeder handles quiz creation). Add a second quiz with 10 questions to satisfy the dashboard's `quiz_id: 2` reference. The quiz should be linked to the existing `summary_id: 1` and should use the same pattern as the existing quiz seeder but with different question content.

Alternatively, update `DashboardController` (already done in Step 3.1) to query dynamically so that the hardcoded `quiz_id: 2` reference no longer exists — which is the preferred approach.

---

### Step 8.2 — Seed a second demo user for leaderboard testing

Open `database/seeders/UserSeeder.php` (or create one). Add a second user with higher XP so that the leaderboard shows at least two real entries:

```php
User::create([
    'name'        => 'Alex Rivera',
    'email'       => 'alex@example.com',
    'password'    => Hash::make('password'),
    'xp_total'    => 3820,
    'level'       => 15,
    'streak_current' => 12,
    'streak_best'    => 21,
]);
```

After adding, re-run seeders:

```bash
php artisan db:seed
```

---

### Step 8.3 — Expand activity data diversity

The current activity seeder creates 8 entries all tied to `summary_id: 1`. Update `database/seeders/ActivityLogSeeder.php` to spread events over at least the past 14 days with varied event types (`quiz_completed`, `flashcard_mastered`, `upload_processed`, `badge_unlocked`) so that the analytics weekly progress chart shows real variation.

---

## Phase 9 — FormRequest Validation & API Resources

### Step 9.1 — Create FormRequest classes

Generate the stubs:

```bash
php artisan make:request StoreUploadRequest
php artisan make:request SubmitQuizRequest
php artisan make:request UpdatePreferencesRequest
php artisan make:request UpdateFlashcardProgressRequest
```

**`app/Http/Requests/StoreUploadRequest.php`** — add inside `rules()`:

```php
public function authorize(): bool { return true; }

public function rules(): array
{
    return [
        'type'     => 'required|in:text,file,url,sample',
        'text'     => 'required_if:type,text|nullable|string|min:50|max:50000',
        'file'     => 'required_if:type,file|nullable|file|mimes:pdf,docx,txt|max:10240',
        'url'      => 'required_if:type,url|nullable|url|max:2048',
        'skill_id' => 'required_if:type,sample|nullable|integer|exists:skills,id',
    ];
}
```

**`app/Http/Requests/SubmitQuizRequest.php`**:

```php
public function rules(): array
{
    return [
        'answers'                  => 'required|array|min:1',
        'answers.*.question_id'    => 'required|integer|exists:quiz_questions,id',
        'answers.*.selected'       => 'nullable|string',
        'started_at'               => 'nullable|date',
    ];
}
```

**`app/Http/Requests/UpdatePreferencesRequest.php`**:

```php
public function rules(): array
{
    return [
        'dyslexia_font'   => 'nullable|boolean',
        'high_contrast'   => 'nullable|boolean',
        'reduce_motion'   => 'nullable|boolean',
        'font_size'       => 'nullable|in:small,medium,large,xlarge',
        'line_height'     => 'nullable|numeric|between:1,3',
        'letter_spacing'  => 'nullable|numeric|between:0,5',
        'word_spacing'    => 'nullable|numeric|between:0,10',
        'daily_goal_minutes' => 'nullable|integer|min:5|max:240',
    ];
}
```

**`app/Http/Requests/UpdateFlashcardProgressRequest.php`**:

```php
public function rules(): array
{
    return [
        'status' => 'required|in:unseen,current,saved,mastered',
    ];
}
```

Replace the raw `Request` type-hints in each controller with the appropriate FormRequest class. For example in `QuizController::submit()`:

```php
// Before
public function submit(Request $request, $quizId)

// After
public function submit(SubmitQuizRequest $request, $quizId)
```

---

### Step 9.2 — Create API Resource classes

Generate stubs:

```bash
php artisan make:resource UserResource
php artisan make:resource SummaryResource
php artisan make:resource FlashcardResource
php artisan make:resource QuizResource
php artisan make:resource BadgeResource
```

Example `UserResource`:

```php
public function toArray(Request $request): array
{
    return [
        'id'                     => $this->id,
        'name'                   => $this->name,
        'email'                  => $this->email,
        'avatar'                 => $this->avatar,
        'xp_total'               => $this->xp_total ?? 0,
        'level'                  => $this->level ?? 1,
        'streak_current'         => $this->streak_current ?? 0,
        'streak_best'            => $this->streak_best ?? 0,
        'daily_goal_minutes'     => $this->daily_goal_minutes ?? 20,
        'onboarding_completed_at'=> $this->onboarding_completed_at,
    ];
}
```

Once resources exist, update controllers to return them instead of raw arrays:

```php
// In UserController::show()
return new UserResource($request->user());
```

---

## Phase 10 — Testing

### Step 10.1 — Feature tests for all API endpoints

Create test files using Artisan:

```bash
php artisan make:test Api/AuthTest
php artisan make:test Api/DashboardTest
php artisan make:test Api/QuizTest
php artisan make:test Api/FlashcardTest
php artisan make:test Api/UploadTest
php artisan make:test Api/AnalyticsTest
php artisan make:test Api/AchievementTest
```

All feature tests should:

1. Use `RefreshDatabase` to start from a clean state.
2. Call `$this->actingAs($user, 'sanctum')` to authenticate.
3. Assert both the HTTP status code and the JSON structure of the response.

Example for `AuthTest`:

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }
}
```

Example for `QuizTest`:

```php
public function test_submit_saves_attempt_and_awards_xp(): void
{
    $user = User::factory()->create();
    $quiz = Quiz::factory()->has(QuizQuestion::factory()->count(5))->create();

    $answers = $quiz->questions->map(fn($q) => [
        'question_id' => $q->id,
        'selected'    => $q->correct_option, // all correct
    ])->toArray();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/quizzes/{$quiz->id}/submit", ['answers' => $answers]);

    $response->assertOk()
        ->assertJsonPath('score.correct', 5)
        ->assertJsonPath('score.accuracy', 100);

    $this->assertDatabaseHas('quiz_attempts', [
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'correct' => 5,
    ]);

    // XP should have been awarded
    $user->refresh();
    $this->assertGreaterThan(0, $user->xp_total);
}
```

---

### Step 10.2 — Unit tests for GamificationService

Create `tests/Unit/Services/GamificationServiceTest.php`:

```bash
php artisan make:test Unit/Services/GamificationServiceTest --unit
```

Key test cases to write:

```php
public function test_level_calculation(): void
{
    $service = new GamificationService();
    $this->assertEquals(1,  $service->calculateLevel(0));
    $this->assertEquals(1,  $service->calculateLevel(199));
    $this->assertEquals(2,  $service->calculateLevel(200));
    $this->assertEquals(6,  $service->calculateLevel(1000));
    $this->assertEquals(100, $service->calculateLevel(99999)); // cap
}

public function test_streak_increments_for_yesterday_activity(): void
{
    $user = User::factory()->create([
        'streak_current' => 3,
        'streak_best'    => 3,
        'last_active_at' => now()->subDay(),
    ]);

    (new GamificationService())->updateStreak($user);
    $user->refresh();

    $this->assertEquals(4, $user->streak_current);
    $this->assertEquals(4, $user->streak_best);
}

public function test_streak_resets_after_gap(): void
{
    $user = User::factory()->create([
        'streak_current' => 10,
        'last_active_at' => now()->subDays(3),
    ]);

    (new GamificationService())->updateStreak($user);
    $user->refresh();

    $this->assertEquals(1, $user->streak_current);
}
```

---

### Step 10.3 — Run the test suite

```bash
php artisan test
# or for detailed output:
php artisan test --verbose
```

All tests should be green before moving to Phase 11.

---

## Phase 11 — Deployment Readiness

### Step 11.1 — Verify Docker build

The `Dockerfile` at the project root exists but has not been tested. Build and run it locally:

```bash
docker build -t skillsprint:local .
docker run -p 8080:80 --env-file .env skillsprint:local
```

Visit `http://localhost:8080` and confirm the app loads. If the build fails, check that PHP extensions required by the project (pdo_pgsql or pdo_mysql, gd, zip, mbstring, tokenizer, xml) are installed in the Dockerfile's base image.

---

### Step 11.2 — Configure queue worker for production

The queue driver is `database`. In production, a worker process must run continuously. Add a `worker` process to `render.yaml`:

```yaml
services:
  - type: worker
    name: skillsprint-queue
    env: php
    buildCommand: composer install --no-dev
    startCommand: php artisan queue:work --tries=2 --timeout=120 --sleep=3
    envVars:
      - fromGroup: skillsprint-env
```

If deploying with Docker, add a `CMD` override or a `Procfile` entry:

```
worker: php artisan queue:work --tries=2 --timeout=120
```

---

### Step 11.3 — Add scheduled tasks in `routes/console.php`

Open (or create) `routes/console.php` and define the schedule for daily maintenance tasks:

```php
<?php

use Illuminate\Support\Facades\Schedule;

// Rotate the daily challenge at midnight (pick a new random quiz as the day's challenge)
Schedule::command('app:rotate-daily-challenge')->dailyAt('00:00');

// Optional: prune old activity logs older than 90 days to keep the table manageable
Schedule::command('model:prune', ['--model' => 'App\\Models\\ActivityLog'])->daily();
```

Create the `RotateDailyChallenge` command:

```bash
php artisan make:command RotateDailyChallenge
```

In `app/Console/Commands/RotateDailyChallenge.php`, implement `handle()` to select a random active quiz and store its ID in cache:

```php
public function handle(): void
{
    $quiz = \App\Models\Quiz::inRandomOrder()->first();
    if ($quiz) {
        cache()->put('daily_challenge_quiz_id', $quiz->id, now()->addDay());
        $this->info("Daily challenge set to quiz #{$quiz->id}");
    }
}
```

Then update `DashboardController` to read `cache('daily_challenge_quiz_id')` instead of querying for an unattempted quiz dynamically.

To enable the scheduler in production, add a cron entry (or a Render cron service):

```
* * * * * php /var/www/html/artisan schedule:run >> /dev/null 2>&1
```

---

### Step 11.4 — Production `.env` checklist

Ensure the following values are set in the production environment (not committed to the repository):

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-render-domain.onrender.com

DB_CONNECTION=pgsql
DB_HOST=<render postgres host>
DB_PORT=5432
DB_DATABASE=<database name>
DB_USERNAME=<username>
DB_PASSWORD=<password>

QUEUE_CONNECTION=database
CACHE_DRIVER=database

GEMINI_API_KEY=<your key>
GEMINI_MODEL=gemini-2.0-flash

SESSION_DRIVER=cookie
SESSION_SECURE_COOKIE=true
```

---

### Step 11.5 — Build assets for production

Before deploying, always rebuild frontend assets:

```bash
npm run build
```

Commit the compiled assets to the repository (or configure Render's build pipeline to run `npm run build` as part of the deploy command).

In `render.yaml`, the `buildCommand` should be:

```yaml
buildCommand: composer install --no-dev --optimize-autoloader && npm run build && php artisan migrate --force && php artisan config:cache && php artisan route:cache
```

---

## Summary — What to Implement in Order

| Priority | Step | Section |
|----------|------|---------|
| 1 | Install Sanctum | 1.1 |
| 2 | Add `HasApiTokens` to User | 1.2 |
| 3 | Register Sanctum middleware | 1.3 |
| 4 | Create `AuthController` | 1.4 |
| 5 | Protect all routes with `auth:sanctum` | 1.5 |
| 6 | Replace all `user_id = 1` hardcodes | 1.6 |
| 7 | Fix `User` model fillable/casts/relations | 2.1 |
| 8 | Create `quiz_attempts` migration + model | 2.2 |
| 9 | Create `user_skills` migration | 2.3 |
| 10 | Rewrite `DashboardController` | 3.1 |
| 11 | Rewrite `QuizController::submit()` + `results()` | 3.2 |
| 12 | Rewrite `AnalyticsController` | 3.3 |
| 13 | Fix `AchievementController` leaderboard/rank | 3.4 |
| 14 | Fix `UserController` + enrollment methods | 3.5 |
| 15 | Create directories + enums | 4.1–4.2 |
| 16 | Create authorization policies | 4.3 |
| 17 | Create `GamificationService` | 5.1 |
| 18 | Wire gamification into `FlashcardController` | 5.2 |
| 19 | Create `UpdateStreak` middleware | 5.3 |
| 20 | Add Gemini config | 6.1 |
| 21 | Create `GeminiService` | 6.2 |
| 22 | Create `ProcessUploadJob` | 6.3 |
| 23 | Add `UploadController::store()` + `status()` | 6.4 |
| 24 | Configure queue driver | 6.5 |
| 25 | Apply preferences in `AppLayout.vue` | 7.1–7.2 |
| 26 | Improve seeders | 8.1–8.3 |
| 27 | Create FormRequest classes | 9.1 |
| 28 | Create API Resource classes | 9.2 |
| 29 | Write feature and unit tests | 10.1–10.3 |
| 30 | Docker + queue worker + scheduler + deploy | 11.1–11.5 |

---

**Total new files to create: ~22 | Files to modify: ~10**
