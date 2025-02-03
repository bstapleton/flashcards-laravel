<?php

namespace App\Transformers;

use App\Models\Attempt;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class AttemptTransformer extends TransformerAbstract
{
    public function transform(Attempt $attempt): array
    {
        $data =  [
            'question' => $attempt->flashcard->text,
            'correctness' => $attempt->correctness->value,
            'question_type' => $attempt->flashcard->type->value,
            'answered_at' => Carbon::parse($attempt->answered_at)->toIso8601String(),
        ];

        return $data;
    }
}
