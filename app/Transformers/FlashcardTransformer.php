<?php

namespace App\Transformers;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Tag;
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
            'last_seen_at' => $flashcard->lastAttempt()
                ? Carbon::parse($flashcard->lastAttempt()->answered_at)->toIso8601String()
                : null,
            'eligible_at' => $flashcard->eligible_at->toIso8601String(),
            'tags' => $flashcard->tags->map(function (Tag $tag) {
                return (new TagTransformer())->transform($tag);
            }),
            'answers' => $flashcard->answers->map(function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text,
                ];
            })
        ];

        if ($flashcard->type === QuestionType::STATEMENT) {
            $data['is_true'] = $flashcard->is_true;
        }

        return $data;
    }
}
