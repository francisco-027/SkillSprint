<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Models\Summary;
use App\Models\UserFlashcardProgress;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    public function __construct(private GamificationService $gamification) {}

    public function show($deckId)
    {
        $cards = Flashcard::where('summary_id', $deckId)->orderBy('sort_order')->get();

        $cardsWithStatus = $cards->map(function ($card) {
            $progress = UserFlashcardProgress::where('user_id', auth()->id())
                ->where('flashcard_id', $card->id)
                ->first();

            return [
                'id'       => $card->id,
                'question' => $card->question,
                'answer'   => $card->answer,
                'category' => $card->category,
                'status'   => $progress ? $progress->status : 'unseen',
            ];
        });

        return response()->json([
            'deck' => [
                'id'    => (int) $deckId,
                'title' => Summary::find($deckId)?->title ?? 'Flashcard Deck',
                'total' => count($cardsWithStatus),
            ],
            'cards' => $cardsWithStatus,
        ]);
    }

    public function updateCard(Request $request, $deckId, $cardId)
    {
        $user      = $request->user();
        $newStatus = $request->input('status');

        $progress = UserFlashcardProgress::firstOrCreate(
            ['user_id' => $user->id, 'flashcard_id' => $cardId],
            ['status' => 'unseen']
        );

        $wasMastered = $progress->status === 'mastered';
        $progress->status = $newStatus;
        $progress->save();

        if ($newStatus === 'mastered' && !$wasMastered) {
            $this->gamification->awardXp($user, 'flashcard_mastered', 10);
            $this->gamification->checkBadgeUnlock($user);
        }

        return response()->json($progress);
    }
}