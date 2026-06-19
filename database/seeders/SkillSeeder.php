<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            ['title' => 'Python Programming',       'category' => 'Technology', 'level' => 'Beginner',     'estimated_minutes' => 45, 'learner_count' => 12400, 'is_popular' => true,  'tags' => ['Coding','Automation','Scripting']],
            ['title' => 'Machine Learning Basics',  'category' => 'Technology', 'level' => 'Intermediate', 'estimated_minutes' => 60, 'learner_count' => 8900,  'is_popular' => true,  'tags' => ['AI','Data Science','Algorithms']],
            ['title' => 'Photosynthesis',           'category' => 'Science',    'level' => 'Beginner',     'estimated_minutes' => 25, 'learner_count' => 5200,  'is_popular' => false, 'tags' => ['Biology','Plants','Energy']],
            ['title' => 'Climate Change Essentials','category' => 'Science',    'level' => 'Beginner',     'estimated_minutes' => 35, 'learner_count' => 9700,  'is_popular' => true,  'tags' => ['Environment','Sustainability','Science']],
            ['title' => 'Stoicism Philosophy',      'category' => 'Humanities', 'level' => 'Beginner',     'estimated_minutes' => 30, 'learner_count' => 4100,  'is_popular' => false, 'tags' => ['Philosophy','Mindfulness','History']],
            ['title' => 'World War II Overview',    'category' => 'Humanities', 'level' => 'Intermediate', 'estimated_minutes' => 50, 'learner_count' => 6300,  'is_popular' => false, 'tags' => ['History','War','20th Century']],
            ['title' => 'Digital Marketing 101',    'category' => 'Business',   'level' => 'Beginner',     'estimated_minutes' => 40, 'learner_count' => 11200, 'is_popular' => true,  'tags' => ['Marketing','SEO','Social Media']],
            ['title' => 'Personal Finance Basics',  'category' => 'Business',   'level' => 'Beginner',     'estimated_minutes' => 35, 'learner_count' => 14500, 'is_popular' => true,  'tags' => ['Finance','Budgeting','Investing']],
            ['title' => 'Nutrition Fundamentals',   'category' => 'Health',     'level' => 'Beginner',     'estimated_minutes' => 30, 'learner_count' => 7800,  'is_popular' => false, 'tags' => ['Health','Diet','Wellness']],
            ['title' => 'React.js Fundamentals',    'category' => 'Technology', 'level' => 'Intermediate', 'estimated_minutes' => 55, 'learner_count' => 10100, 'is_popular' => true,  'tags' => ['JavaScript','Frontend','Web Dev']],
            ['title' => 'Data Visualization',       'category' => 'Technology', 'level' => 'Intermediate', 'estimated_minutes' => 45, 'learner_count' => 5600,  'is_popular' => false, 'tags' => ['Charts','Analytics','Dashboards']],
            ['title' => 'Leadership Essentials',    'category' => 'Business',   'level' => 'Intermediate', 'estimated_minutes' => 40, 'learner_count' => 900,   'is_popular' => false, 'tags' => ['Leadership','Management','Teams']],
            ['title' => 'Genetics & DNA',           'category' => 'Science',    'level' => 'Advanced',     'estimated_minutes' => 60, 'learner_count' => 3400,  'is_popular' => false, 'tags' => ['Biology','DNA','Heredity']],
            ['title' => 'Mindfulness & Meditation', 'category' => 'Health',     'level' => 'Beginner',     'estimated_minutes' => 20, 'learner_count' => 13200, 'is_popular' => true,  'tags' => ['Wellness','Mental Health','Focus']],
            ['title' => 'SQL for Beginners',        'category' => 'Technology', 'level' => 'Beginner',     'estimated_minutes' => 40, 'learner_count' => 8100,  'is_popular' => false, 'tags' => ['Database','Queries','Data']],
            ['title' => 'Economics Fundamentals',   'category' => 'Business',   'level' => 'Intermediate', 'estimated_minutes' => 50, 'learner_count' => 4800,  'is_popular' => false, 'tags' => ['Economics','Markets','Supply Demand']],
            ['title' => 'Cybersecurity Basics',     'category' => 'Technology', 'level' => 'Intermediate', 'estimated_minutes' => 45, 'learner_count' => 9200,  'is_popular' => true,  'tags' => ['Security','Networking','Ethical Hacking']],
            ['title' => 'Human Anatomy 101',        'category' => 'Science',    'level' => 'Intermediate', 'estimated_minutes' => 55, 'learner_count' => 3100,  'is_popular' => false, 'tags' => ['Anatomy','Body','Medical']],
        ];

        foreach ($skills as $skill) {
            Skill::create([
                'title'             => $skill['title'],
                'slug'              => Str::slug($skill['title']),
                'description'       => 'Learn ' . $skill['title'] . ' with bite-sized, interactive lessons.',
                'category'          => $skill['category'],
                'level'             => $skill['level'],
                'icon'              => null,
                'estimated_minutes' => $skill['estimated_minutes'],
                'learner_count'     => $skill['learner_count'],
                'tags'              => json_encode($skill['tags']),
                'is_featured'       => $skill['is_popular'],
                'is_popular'        => $skill['is_popular'],
            ]);
        }
    }
}