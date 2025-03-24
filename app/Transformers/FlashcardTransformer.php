<?php

namespace App\Transformers;

use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Tag;
use Illuminate\Support\Carbon;
use League\Fractal\TransformerAbstract;

/**
 * This transformer is used to 'hide' certain aspects of the flashcard from the consumer, making it harder to cheat.
 */
class FlashcardTransformer extends TransformerAbstract
{
    public function transform(Flashcard $flashcard): array
    {
        $data = [
            'id' => $flashcard->id,
            'type' => $flashcard->type->value,
            'status' => $flashcard->status->value,
            'text' => $flashcard->text,
            'explanation' => $flashcard->explanation,
            'difficulty' => $flashcard->difficulty,
            'eligible_at' => Carbon::parse($flashcard->eligible_at)->toIso8601String(),
            'tags' => $flashcard->tags->map(function (Tag $tag) {
                return (new TagTransformer)->transform($tag);
            }),
            'answers' => $flashcard->answers->map(function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text,
                ];
            }),
        ];

        return $data;
    }
}
