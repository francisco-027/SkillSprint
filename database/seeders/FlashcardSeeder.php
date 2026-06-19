<?php

namespace Database\Seeders;

use App\Models\Flashcard;
use App\Models\UserFlashcardProgress;
use Illuminate\Database\Seeder;

class FlashcardSeeder extends Seeder
{
    public function run(): void
    {
        $cards = [
            ['question' => 'What is Machine Learning?',                              'answer' => 'A branch of AI that enables systems to learn from data without explicit programming.',                     'category' => 'Core Concept'],
            ['question' => 'AI vs Machine Learning',                                 'answer' => 'AI is the broad field; ML is a subset that learns from data patterns automatically.',                     'category' => 'Core Concept'],
            ['question' => 'Training Data Definition',                               'answer' => 'The labeled dataset used to teach an ML model to recognize patterns.',                                    'category' => 'Definition'],
            ['question' => 'Types of ML Algorithms',                                 'answer' => 'Supervised, Unsupervised, and Reinforcement Learning.',                                                  'category' => 'Classification'],
            ['question' => 'What is Supervised Learning?',                           'answer' => 'Learning from labeled data where each input is paired with the correct output.',                          'category' => 'Core Concept'],
            ['question' => 'Unsupervised Learning',                                  'answer' => 'Finds hidden patterns in unlabeled data without guidance.',                                              'category' => 'Core Concept'],
            ['question' => 'Neural Networks Basics',                                 'answer' => 'Layers of connected nodes that learn feature representations from raw data.',                             'category' => 'Architecture'],
            ['question' => 'Gradient Descent',                                       'answer' => 'An optimization algorithm that minimizes error by adjusting model parameters iteratively.',                'category' => 'Algorithm'],
            ['question' => 'Overfitting vs Underfitting',                            'answer' => 'Overfitting: too specific to training data. Underfitting: too general, poor accuracy.',                    'category' => 'Problem'],
            ['question' => 'Model Evaluation Metrics',                               'answer' => 'Accuracy, Precision, Recall, F1 Score, AUC-ROC.',                                                         'category' => 'Evaluation'],
            ['question' => 'Reinforcement Learning',                                 'answer' => 'An agent learns by receiving rewards and penalties through interaction with an environment.',              'category' => 'Core Concept'],
            ['question' => 'Real-World ML Applications',                             'answer' => 'Chatbots, fraud detection, recommendations, self-driving cars, healthcare AI.',                            'category' => 'Application'],
        ];

        foreach ($cards as $i => $card) {
            $flashcard = Flashcard::create([
                'summary_id' => 1,
                'question'   => $card['question'],
                'answer'     => $card['answer'],
                'category'   => $card['category'],
                'status'     => 'unseen',
                'sort_order' => $i + 1,
            ]);

            UserFlashcardProgress::create([
                'user_id'      => 1,
                'flashcard_id' => $flashcard->id,
                'status'       => 'unseen',
            ]);
        }
    }
}