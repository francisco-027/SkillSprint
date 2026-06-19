<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\UserBadge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $earnedBadges = [
            ['slug' => 'first-step',      'title' => 'First Step',      'description' => 'Completed your first lesson',               'icon' => '🎯', 'xp_reward' => 50,  'is_new' => false],
            ['slug' => 'streak-master',   'title' => 'Streak Master',   'description' => '7-day learning streak',                     'icon' => '🔥', 'xp_reward' => 150, 'is_new' => true],
            ['slug' => 'quiz-champion',   'title' => 'Quiz Champion',   'description' => 'Score 90%+ on 5 quizzes',                   'icon' => '🏆', 'xp_reward' => 200, 'is_new' => true],
            ['slug' => 'night-owl',       'title' => 'Night Owl',       'description' => 'Study after 10 PM',                         'icon' => '🌙', 'xp_reward' => 75,  'is_new' => true],
            ['slug' => 'speed-reader',    'title' => 'Speed Reader',    'description' => 'Read 10 summaries',                         'icon' => '📚', 'xp_reward' => 100, 'is_new' => false],
            ['slug' => 'flashcard-hero',  'title' => 'Flashcard Hero',  'description' => 'Reviewed 100 flashcards',                   'icon' => '🃏', 'xp_reward' => 120, 'is_new' => false],
            ['slug' => 'content-creator', 'title' => 'Content Creator', 'description' => 'Uploaded 5 materials',                      'icon' => '📤', 'xp_reward' => 80,  'is_new' => false],
            ['slug' => 'early-bird',      'title' => 'Early Bird',      'description' => 'Study before 8 AM',                         'icon' => '🌅', 'xp_reward' => 75,  'is_new' => false],
            ['slug' => 'week-warrior',    'title' => 'Week Warrior',    'description' => '7 consecutive daily goals',                 'icon' => '⚡', 'xp_reward' => 100, 'is_new' => false],
            ['slug' => 'ml-fundamentals', 'title' => 'ML Fundamentals', 'description' => 'Complete ML learning path',                 'icon' => '🤖', 'xp_reward' => 200, 'is_new' => false],
            ['slug' => 'python-basics',   'title' => 'Python Basics',   'description' => 'Complete Python path',                      'icon' => '🐍', 'xp_reward' => 150, 'is_new' => false],
            ['slug' => 'quick-learner',   'title' => 'Quick Learner',   'description' => 'Finish a quiz in under 2 min',              'icon' => '⏱️', 'xp_reward' => 60,  'is_new' => false],
            ['slug' => 'social-learner',  'title' => 'Social Learner',  'description' => 'Share 3 results',                           'icon' => '🤝', 'xp_reward' => 50,  'is_new' => false],
            ['slug' => 'perfect-score',   'title' => 'Perfect Score',   'description' => '100% on any quiz',                          'icon' => '💯', 'xp_reward' => 250, 'is_new' => false],
        ];

        $lockedBadges = [
            ['slug' => 'fortnight-warrior',     'title' => 'Fortnight Warrior',     'description' => '14-day learning streak',                       'icon' => '⚔️', 'xp_reward' => 300],
            ['slug' => 'ai-master',             'title' => 'AI Master',             'description' => 'Complete all AI-related skills',               'icon' => '🧠', 'xp_reward' => 500],
            ['slug' => 'perfect-week',          'title' => 'Perfect Week',          'description' => 'Hit daily goal 7 days straight',               'icon' => '🌟', 'xp_reward' => 200],
            ['slug' => 'polymath',              'title' => 'Polymath',              'description' => 'Complete skills in 5 different categories',    'icon' => '🎓', 'xp_reward' => 400],
            ['slug' => 'speed-demon',           'title' => 'Speed Demon',           'description' => 'Complete a full quiz in under 60 seconds',     'icon' => '💨', 'xp_reward' => 100],
            ['slug' => 'knowledge-vault',       'title' => 'Knowledge Vault',       'description' => 'Upload 20 materials',                          'icon' => '🏛️', 'xp_reward' => 150],
            ['slug' => 'deep-diver',            'title' => 'Deep Diver',            'description' => 'Complete 5 advanced-level skills',             'icon' => '🤿', 'xp_reward' => 250],
            ['slug' => 'consistency-king',      'title' => 'Consistency King',      'description' => '30 consecutive days active',                   'icon' => '👑', 'xp_reward' => 500],
            ['slug' => 'night-scholar',         'title' => 'Night Scholar',         'description' => 'Study after midnight',                         'icon' => '🦉', 'xp_reward' => 50],
            ['slug' => 'data-wizard',           'title' => 'Data Wizard',           'description' => 'Complete Data Science learning path',          'icon' => '📊', 'xp_reward' => 300],
            ['slug' => 'teaching-assistant',    'title' => 'Teaching Assistant',    'description' => 'Help 10 other learners',                       'icon' => '📝', 'xp_reward' => 200],
            ['slug' => 'explorer',              'title' => 'Explorer',              'description' => 'Try 3 different output types',                 'icon' => '🧭', 'xp_reward' => 75],
            ['slug' => 'bookworm',              'title' => 'Bookworm',              'description' => 'Read 50 summaries',                            'icon' => '📖', 'xp_reward' => 200],
            ['slug' => 'challenger',            'title' => 'Challenger',            'description' => 'Complete 10 daily challenges',                 'icon' => '🎯', 'xp_reward' => 150],
            ['slug' => 'multi-linguist',        'title' => 'Multi-Linguist',        'description' => 'Learn in 3 different languages',               'icon' => '🌐', 'xp_reward' => 100],
            ['slug' => 'accessibility-advocate','title' => 'Accessibility Advocate','description' => 'Enable 5 accessibility features',              'icon' => '♿', 'xp_reward' => 50],
            ['slug' => 'community-builder',     'title' => 'Community Builder',     'description' => 'Invite 5 friends to SkillSprint',               'icon' => '👥', 'xp_reward' => 100],
            ['slug' => 'legend',                'title' => 'Legend',                'description' => 'Reach level 50',                               'icon' => '🏅', 'xp_reward' => 1000],
        ];

        foreach ($earnedBadges as $i => $badge) {
            $created = Badge::create([
                'slug'        => $badge['slug'],
                'title'       => $badge['title'],
                'description' => $badge['description'],
                'icon'        => $badge['icon'],
                'xp_reward'   => $badge['xp_reward'],
                'criteria'    => json_encode([]),
            ]);

            UserBadge::create([
                'user_id'   => 1,
                'badge_id'  => $created->id,
                'earned_at' => now()->subDays($i + 1),
                'is_new'    => $badge['is_new'],
            ]);
        }

        foreach ($lockedBadges as $badge) {
            Badge::create([
                'slug'        => $badge['slug'],
                'title'       => $badge['title'],
                'description' => $badge['description'],
                'icon'        => $badge['icon'],
                'xp_reward'   => $badge['xp_reward'],
                'criteria'    => json_encode([]),
            ]);
        }
    }
}