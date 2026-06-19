<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the badge catalog (definitions only — no user awards).
     * Idempotent: keyed on slug, so re-running and running on an
     * already-populated production database is safe.
     */
    public function up(): void
    {
        foreach ($this->badges() as $badge) {
            DB::table('badges')->updateOrInsert(
                ['slug' => $badge['slug']],
                [
                    'title'       => $badge['title'],
                    'description' => $badge['description'],
                    'icon'        => $badge['icon'],
                    'xp_reward'   => $badge['xp_reward'],
                    'criteria'    => json_encode([]),
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        DB::table('badges')->whereIn('slug', array_column($this->badges(), 'slug'))->delete();
    }

    private function badges(): array
    {
        return [
            ['slug' => 'first-step',            'title' => 'First Step',            'description' => 'Completed your first lesson',               'icon' => '🎯',  'xp_reward' => 50],
            ['slug' => 'streak-master',         'title' => 'Streak Master',         'description' => '7-day learning streak',                     'icon' => '🔥',  'xp_reward' => 150],
            ['slug' => 'quiz-champion',         'title' => 'Quiz Champion',         'description' => 'Score 90%+ on 5 quizzes',                   'icon' => '🏆',  'xp_reward' => 200],
            ['slug' => 'night-owl',             'title' => 'Night Owl',             'description' => 'Study after 10 PM',                         'icon' => '🌙',  'xp_reward' => 75],
            ['slug' => 'speed-reader',          'title' => 'Speed Reader',          'description' => 'Read 10 summaries',                         'icon' => '📚',  'xp_reward' => 100],
            ['slug' => 'flashcard-hero',        'title' => 'Flashcard Hero',        'description' => 'Reviewed 100 flashcards',                   'icon' => '🃏',  'xp_reward' => 120],
            ['slug' => 'content-creator',       'title' => 'Content Creator',       'description' => 'Uploaded 5 materials',                      'icon' => '📤',  'xp_reward' => 80],
            ['slug' => 'early-bird',            'title' => 'Early Bird',            'description' => 'Study before 8 AM',                         'icon' => '🌅',  'xp_reward' => 75],
            ['slug' => 'week-warrior',          'title' => 'Week Warrior',          'description' => '7 consecutive daily goals',                 'icon' => '⚡',  'xp_reward' => 100],
            ['slug' => 'ml-fundamentals',       'title' => 'ML Fundamentals',       'description' => 'Complete ML learning path',                 'icon' => '🤖',  'xp_reward' => 200],
            ['slug' => 'python-basics',         'title' => 'Python Basics',         'description' => 'Complete Python path',                      'icon' => '🐍',  'xp_reward' => 150],
            ['slug' => 'quick-learner',         'title' => 'Quick Learner',         'description' => 'Finish a quiz in under 2 min',              'icon' => '⏱️',  'xp_reward' => 60],
            ['slug' => 'social-learner',        'title' => 'Social Learner',        'description' => 'Share 3 results',                           'icon' => '🤝',  'xp_reward' => 50],
            ['slug' => 'perfect-score',         'title' => 'Perfect Score',         'description' => '100% on any quiz',                          'icon' => '💯',  'xp_reward' => 250],

            ['slug' => 'fortnight-warrior',     'title' => 'Fortnight Warrior',     'description' => '14-day learning streak',                    'icon' => '⚔️',  'xp_reward' => 300],
            ['slug' => 'ai-master',             'title' => 'AI Master',             'description' => 'Complete all AI-related skills',            'icon' => '🧠',  'xp_reward' => 500],
            ['slug' => 'perfect-week',          'title' => 'Perfect Week',          'description' => 'Hit daily goal 7 days straight',            'icon' => '🌟',  'xp_reward' => 200],
            ['slug' => 'polymath',              'title' => 'Polymath',              'description' => 'Complete skills in 5 different categories', 'icon' => '🎓',  'xp_reward' => 400],
            ['slug' => 'speed-demon',           'title' => 'Speed Demon',           'description' => 'Complete a full quiz in under 60 seconds',  'icon' => '💨',  'xp_reward' => 100],
            ['slug' => 'knowledge-vault',       'title' => 'Knowledge Vault',       'description' => 'Upload 20 materials',                       'icon' => '🏛️',  'xp_reward' => 150],
            ['slug' => 'deep-diver',            'title' => 'Deep Diver',            'description' => 'Complete 5 advanced-level skills',          'icon' => '🤿',  'xp_reward' => 250],
            ['slug' => 'consistency-king',      'title' => 'Consistency King',      'description' => '30 consecutive days active',                'icon' => '👑',  'xp_reward' => 500],
            ['slug' => 'night-scholar',         'title' => 'Night Scholar',         'description' => 'Study after midnight',                      'icon' => '🦉',  'xp_reward' => 50],
            ['slug' => 'data-wizard',           'title' => 'Data Wizard',           'description' => 'Complete Data Science learning path',       'icon' => '📊',  'xp_reward' => 300],
            ['slug' => 'teaching-assistant',    'title' => 'Teaching Assistant',    'description' => 'Help 10 other learners',                    'icon' => '📝',  'xp_reward' => 200],
            ['slug' => 'explorer',              'title' => 'Explorer',              'description' => 'Try 3 different output types',              'icon' => '🧭',  'xp_reward' => 75],
            ['slug' => 'bookworm',              'title' => 'Bookworm',              'description' => 'Read 50 summaries',                         'icon' => '📖',  'xp_reward' => 200],
            ['slug' => 'challenger',            'title' => 'Challenger',            'description' => 'Complete 10 daily challenges',              'icon' => '🎯',  'xp_reward' => 150],
            ['slug' => 'multi-linguist',        'title' => 'Multi-Linguist',        'description' => 'Learn in 3 different languages',            'icon' => '🌐',  'xp_reward' => 100],
            ['slug' => 'accessibility-advocate','title' => 'Accessibility Advocate','description' => 'Enable 5 accessibility features',           'icon' => '♿',  'xp_reward' => 50],
            ['slug' => 'community-builder',     'title' => 'Community Builder',     'description' => 'Invite 5 friends to SkillSprint',           'icon' => '👥',  'xp_reward' => 100],
            ['slug' => 'legend',                'title' => 'Legend',                'description' => 'Reach level 50',                            'icon' => '🏅',  'xp_reward' => 1000],
        ];
    }
};
