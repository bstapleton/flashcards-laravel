<?php

namespace App\Transformers;

use App\Models\Answer;
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
            'eligible_at' => $flashcard->getEligibleDateTime(),
            'tags' => $flashcard->tags->pluck('name')->toArray(),
            'answers' => $flashcard->answers->map(function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text,
                    'explanation' => $answer->explanation,
                ];
            })
        ];
    }
}
