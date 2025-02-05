<?php

namespace App\Transformers;

use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Tag;
use Illuminate\Support\Carbon;
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
            'eligible_at' => Carbon::parse($flashcard->eligible_at)->toIso8601String(),
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

        return $data;
    }
}
