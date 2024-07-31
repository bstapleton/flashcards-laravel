<?php

namespace App\Transformers;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Flashcard;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class FlashcardTransformer extends TransformerAbstract
{
    public function transform(Flashcard $flashcard): array
    {
        $data =  [
            'id' => $flashcard->id,
            'type' => $flashcard->type->value,
            'text' => $flashcard->text,
            'difficulty' => $flashcard->difficulty,
            'last_seen_at' => Carbon::parse($flashcard->last_seen)->toIso8601String(),
            'eligible_at' => $flashcard->eligible_at,
            'tags' => $flashcard->tags->pluck('name')->toArray(),
            'answers' => $flashcard->answers->map(function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text,
                    'explanation' => $answer->explanation,
                ];
            })
        ];

        if ($flashcard->type === QuestionType::STATEMENT) {
            $data['is_true'] = $flashcard->is_true;
        }

        return $data;
    }
}
