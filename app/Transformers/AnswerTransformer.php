<?php

namespace App\Transformers;

use App\Models\Answer;
use League\Fractal\TransformerAbstract;

class AnswerTransformer extends TransformerAbstract
{
    public function transform(Answer $answer)
    {
        return [
            'flashcard' => [
                'id' => $answer->flashcard->id,
                'text' => $answer->flashcard->text,
            ],
            'text' => $answer->text,
            'explanation' => $answer->explanation,
            'correct' => $answer->is_correct,
        ];
    }
}
