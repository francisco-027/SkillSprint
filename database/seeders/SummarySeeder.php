<?php

namespace Database\Seeders;

use App\Models\Summary;
use App\Models\Upload;
use Illuminate\Database\Seeder;

class SummarySeeder extends Seeder
{
    public function run(): void
    {
        $upload = Upload::create([
            'user_id'           => 1,
            'original_filename' => 'ML_Chapter3_Notes.pdf',
            'type'              => 'pdf',
            'raw_content'       => 'Machine Learning chapter notes',
            'word_count'        => 2500,
            'status'            => 'done',
            'processed_at'      => now(),
        ]);

        Summary::create([
            'user_id'            => 1,
            'upload_id'          => $upload->id,
            'title'              => 'Machine Learning Basics',
            'difficulty'         => 'beginner',
            'estimated_minutes'  => 25,
            'source_filename'    => 'ML_Chapter3_Notes.pdf',
            'content_sections'   => json_encode([
                [
                    'number'       => '01',
                    'title'        => 'What is Machine Learning?',
                    'tag'          => 'Core Concept',
                    'body'         => 'Machine learning is a branch of artificial intelligence that enables systems to learn from data and improve their accuracy over time — without being explicitly programmed for every task.',
                    'read_minutes' => 3,
                ],
                [
                    'number'       => '02',
                    'title'        => 'The Three Learning Types',
                    'tag'          => 'Classification',
                    'body'         => 'There are three fundamental paradigms in machine learning, each suited to different types of problems and data availability.',
                    'subtypes'     => [
                        ['label' => 'Supervised',    'desc' => 'Learns from labeled training data to predict outcomes.'],
                        ['label' => 'Unsupervised',  'desc' => 'Finds hidden patterns in unlabeled data independently.'],
                        ['label' => 'Reinforcement', 'desc' => 'Learns by trial and reward signals from an environment.'],
                    ],
                    'read_minutes' => 6,
                ],
                [
                    'number'       => '03',
                    'title'        => 'Real-World Applications',
                    'tag'          => 'Application',
                    'body'         => 'Machine learning powers many everyday technologies we use. Key application domains include:',
                    'tags'         => ['Chatbots & NLP', 'Computer Vision', 'Fraud Detection', 'Recommendations', 'Healthcare AI', 'Self-Driving Cars'],
                    'read_minutes' => 4,
                ],
                [
                    'number'       => '04',
                    'title'        => 'How Algorithms Learn',
                    'tag'          => 'Process',
                    'body'         => 'Algorithms learn by processing training data, identifying statistical patterns, and iteratively adjusting internal parameters to minimize prediction errors.',
                    'analogy'      => 'Think of it like learning to ride a bike — you make mistakes, get feedback, and gradually improve until it becomes natural.',
                    'read_minutes' => 6,
                ],
                [
                    'number'       => '05',
                    'title'        => 'Data: The Fuel of ML',
                    'tag'          => 'Foundation',
                    'body'         => 'Data quality and quantity are the most critical factors in ML success. Models trained on biased, incomplete, or insufficient data will produce unreliable results — a concept known as "garbage in, garbage out."',
                    'read_minutes' => 4,
                ],
            ]),
            'key_terms' => json_encode([
                'Algorithm', 'Neural Network', 'Overfitting', 'Feature',
                'Gradient Descent', 'Classification', 'Regression',
                'Training Data', 'Clustering',
            ]),
            'timeline_steps' => json_encode([
                ['step' => 1, 'title' => 'Data Collection',  'desc' => 'Gather labeled training data'],
                ['step' => 2, 'title' => 'Preprocessing',    'desc' => 'Clean and normalize the dataset'],
                ['step' => 3, 'title' => 'Model Selection',  'desc' => 'Choose the right algorithm'],
                ['step' => 4, 'title' => 'Training',         'desc' => 'Run the optimization loop'],
                ['step' => 5, 'title' => 'Evaluation',       'desc' => 'Measure accuracy on test data'],
                ['step' => 6, 'title' => 'Deployment',       'desc' => 'Serve predictions in production'],
            ]),
        ]);
    }
}