<?php

namespace App\Transformers;

use App\Models\Flashcard;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class FlashcardTransformer extends TransformerAbstract
{
    public function transform(Flashcard $flashcard)
    {
        return [
            'id' => $flashcard->id,
            'type' => $flashcard->type->name,
            'text' => $flashcard->text,
            'difficulty' => $flashcard->difficulty,
            'last_seen_at' => Carbon::parse($flashcard->last_seen)->toIso8601String(),
            'eligible_at' => $this->transformDifficulty($flashcard->difficulty, $flashcard->last_seen),
            'answers' => $flashcard->answers->map(function ($answer) {
                return [
                    'text' => $answer->text,
                    'explanation' => $answer->explanation,
                ];
            })
        ];
    }

    private function transformDifficulty(?string $difficulty, $lastSeenAt)
    {
        if (!$lastSeenAt) {
            return null;
        }

        return match ($difficulty) {
            'easy' => Carbon::parse($lastSeenAt)->addMinutes(30)->toIso8601String(),
            'medium' => Carbon::parse($lastSeenAt)->addWeek()->toIso8601String(),
            'hard' => Carbon::parse($lastSeenAt)->addMonth()->toIso8601String(),
            default => null,
        };
    }
}
