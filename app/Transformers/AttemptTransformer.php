<?php

namespace App\Transformers;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class AttemptTransformer extends TransformerAbstract
{
    public function transform(Attempt $attempt): array
    {
        return [
            'question' => $attempt->question,
            'correctness' => $attempt->correctness->value,
            'question_type' => $attempt->question_type,
            'difficulty' => $attempt->difficulty->value,
            'points_earned' => $attempt->points_earned,
            'answered_at' => Carbon::parse($attempt->answered_at)->toIso8601String(),
            'answers_given' => $attempt->formatted_answers->map(function (AttemptAnswer $answer) {
                return [
                    'text' => $answer->getText(),
                    'was_selected' => $answer->getWasSelected(),
                    'is_correct' => $answer->getIsCorrect(),
                ];
            }),
            'tags' => explode(',', $attempt->tags),
            'others' => $attempt->other_attempts ? $attempt->other_attempts->map(function (Attempt $otherAttempt) {
                return [
                    'correctness' => $otherAttempt->correctness->value,
                    'difficulty' => $otherAttempt->difficulty,
                    'points_earned' => $otherAttempt->points_earned,
                    'answered_at' => Carbon::parse($otherAttempt->answered_at)->toIso8601String()
                ];
            }) : [], // TODO: make this an include or something so it doesn't need to be there on the list response
        ];
    }
}
