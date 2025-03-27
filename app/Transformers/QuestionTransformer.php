<?php

namespace App\Transformers;

use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Tag;
use Illuminate\Support\Carbon;
use League\Fractal\TransformerAbstract;

/**
 * The intention of this 'full' transformer is for consumers to be able to see all the data they created/updated at
 * that point in the flow. The other transformer is what you get back after submitting an answer. Basically, some
 * rudimentary anti-cheat, even though you're only cheating yourself if you do it.
 */
class QuestionTransformer extends TransformerAbstract
{
    public function transform(Flashcard $flashcard): array
    {
        return [
            'id' => $flashcard->id,
            'type' => $flashcard->type->value,
            'status' => $flashcard->status->value,
            'text' => $flashcard->text,
            'explanation' => $flashcard->explanation,
            'is_true' => $flashcard->is_true,
            'difficulty' => $flashcard->difficulty,
            'eligible_at' => Carbon::parse($flashcard->eligible_at)->toIso8601String(),
            'tags' => $flashcard->tags->map(function (Tag $tag) {
                return (new TagTransformer)->transform($tag);
            }),
            'answers' => $flashcard->answers->map(function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text,
                    'explanation' => $answer->explanation,
                    'is_correct' => $answer->is_correct,
                ];
            }),
        ];
    }
}
