<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $quiz = Quiz::create([
            'user_id'        => 1,
            'summary_id'     => 1,
            'title'          => 'Machine Learning Quiz',
            'mode'           => 'adaptive',
            'question_count' => 5,
            'difficulty'     => 'beginner',
        ]);

        $questions = [
            [
                'body'           => 'What is Machine Learning?',
                'correct_option' => 'A branch of AI that enables systems to learn from data',
                'options'        => json_encode([
                    'A programming language for building web apps',
                    'A branch of AI that enables systems to learn from data',
                    'A hardware component in modern computers',
                    'A type of database management system',
                ]),
                'explanation' => 'Machine learning is a subset of AI focused on building systems that learn from data, identify patterns, and make decisions with minimal human intervention.',
                'type'        => 'Foundation',
                'difficulty'  => 'Easy',
                'xp_reward'   => 15,
            ],
            [
                'body'           => 'Which of the following best describes Supervised Learning?',
                'correct_option' => 'The algorithm is trained on labeled data where each input is paired with the correct output.',
                'options'        => json_encode([
                    'The algorithm learns by trial and error without any guidance',
                    'The algorithm groups similar data points without labels',
                    'The algorithm is trained on labeled data where each input is paired with the correct output.',
                    'The algorithm randomly guesses until it finds the right answer',
                ]),
                'explanation' => 'Supervised learning uses labeled training data — each training example is a pair consisting of an input and the desired output value.',
                'type'        => 'Core Concept',
                'difficulty'  => 'Medium',
                'xp_reward'   => 20,
            ],
            [
                'body'           => 'How many main types of ML algorithms exist?',
                'correct_option' => 'Three — Supervised, Unsupervised, and Reinforcement',
                'options'        => json_encode([
                    'Two — Simple and Complex',
                    'Three — Supervised, Unsupervised, and Reinforcement',
                    'Four — Basic, Intermediate, Advanced, and Expert',
                    'Five — each with its own sub-types',
                ]),
                'explanation' => 'The three main paradigms of machine learning are Supervised Learning, Unsupervised Learning, and Reinforcement Learning.',
                'type'        => 'Foundation',
                'difficulty'  => 'Easy',
                'xp_reward'   => 15,
            ],
            [
                'body'           => 'What is Gradient Descent primarily used for?',
                'correct_option' => 'Minimizing the error by adjusting model parameters',
                'options'        => json_encode([
                    'Increasing the size of the training dataset',
                    'Minimizing the error by adjusting model parameters',
                    'Converting images to text format',
                    'Storing large amounts of data efficiently',
                ]),
                'explanation' => 'Gradient Descent is an optimization algorithm that iteratively adjusts model parameters to minimize the loss function, effectively reducing prediction errors.',
                'type'        => 'Algorithm',
                'difficulty'  => 'Hard',
                'xp_reward'   => 20,
            ],
            [
                'body'           => 'Which term describes a model that overfits training data?',
                'correct_option' => 'Overfitting',
                'options'        => json_encode([
                    'Underfitting',
                    'Overfitting',
                    'Perfect fitting',
                    'Data leakage',
                ]),
                'explanation' => 'Overfitting occurs when a model learns the training data too well, including its noise, resulting in poor generalization to new data.',
                'type'        => 'Problem',
                'difficulty'  => 'Medium',
                'xp_reward'   => 15,
            ],
        ];

        foreach ($questions as $i => $q) {
            QuizQuestion::create([
                'quiz_id'        => $quiz->id,
                'body'           => $q['body'],
                'correct_option' => $q['correct_option'],
                'options'        => $q['options'],
                'explanation'    => $q['explanation'],
                'type'           => $q['type'],
                'difficulty'     => $q['difficulty'],
                'sort_order'     => $i + 1,
                'xp_reward'      => $q['xp_reward'],
            ]);
        }

        // Second demo quiz — Python Basics
        $quiz2 = Quiz::create([
            'user_id'        => 1,
            'summary_id'     => 1,
            'title'          => 'Python Basics Quiz',
            'mode'           => 'practice',
            'question_count' => 5,
            'difficulty'     => 'beginner',
        ]);

        $questions2 = [
            [
                'body'           => 'What is the correct way to create a variable in Python?',
                'correct_option' => 'x = 5',
                'options'        => json_encode([
                    'var x = 5',
                    'int x = 5',
                    'x = 5',
                    'let x = 5',
                ]),
                'explanation' => 'Python uses simple assignment with the equals sign. No variable type declaration is needed.',
                'type'        => 'Syntax',
                'difficulty'  => 'Easy',
                'xp_reward'   => 10,
            ],
            [
                'body'           => 'What does the len() function do in Python?',
                'correct_option' => 'Returns the number of items in an object',
                'options'        => json_encode([
                    'Converts a number to a string',
                    'Creates a loop that runs a specific number of times',
                    'Returns the number of items in an object',
                    'Checks if a variable is of type integer',
                ]),
                'explanation' => 'len() returns the length (number of items) of an object such as a string, list, tuple, or dictionary.',
                'type'        => 'Built-in',
                'difficulty'  => 'Easy',
                'xp_reward'   => 10,
            ],
            [
                'body'           => 'Which data structure is immutable in Python?',
                'correct_option' => 'Tuple',
                'options'        => json_encode([
                    'List',
                    'Dictionary',
                    'Set',
                    'Tuple',
                ]),
                'explanation' => 'Tuples are immutable sequences — once created, they cannot be modified. Lists, dictionaries, and sets are mutable.',
                'type'        => 'Data Structures',
                'difficulty'  => 'Medium',
                'xp_reward'   => 15,
            ],
            [
                'body'           => 'What is the output of: print(type([]))',
                'correct_option' => "<class 'list'>",
                'options'        => json_encode([
                    "<class 'tuple'>",
                    "<class 'dict'>",
                    "<class 'set'>",
                    "<class 'list'>",
                ]),
                'explanation' => 'Empty square brackets [] create an empty list in Python. The type() function returns the type of the object.',
                'type'        => 'Code Output',
                'difficulty'  => 'Medium',
                'xp_reward'   => 15,
            ],
            [
                'body'           => 'What keyword is used to define a function in Python?',
                'correct_option' => 'def',
                'options'        => json_encode([
                    'function',
                    'define',
                    'def',
                    'func',
                ]),
                'explanation' => 'Python uses the def keyword to define functions, followed by the function name, parentheses, and a colon.',
                'type'        => 'Syntax',
                'difficulty'  => 'Easy',
                'xp_reward'   => 10,
            ],
        ];

        foreach ($questions2 as $i => $q) {
            QuizQuestion::create([
                'quiz_id'        => $quiz2->id,
                'body'           => $q['body'],
                'correct_option' => $q['correct_option'],
                'options'        => $q['options'],
                'explanation'    => $q['explanation'],
                'type'           => $q['type'],
                'difficulty'     => $q['difficulty'],
                'sort_order'     => $i + 1,
                'xp_reward'      => $q['xp_reward'],
            ]);
        }
    }
}