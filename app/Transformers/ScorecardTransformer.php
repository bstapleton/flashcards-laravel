<?php

namespace App\Transformers;

use App\Models\GivenAnswer;
use App\Models\Scorecard;
use League\Fractal\TransformerAbstract;

class ScorecardTransformer extends TransformerAbstract
{
    public function transform(Scorecard $scorecard): array
    {
        return [
            'question' => $scorecard->question,
            'type' => $scorecard->question_type->value,
            'correctness' => $scorecard->correctness->value,
            'score' => $scorecard->points_earned,
            'user_current_score' => $scorecard->getTotalScore(),
            'old_difficulty' => $scorecard->difficulty->value,
            'new_difficulty' => $scorecard->getNewDifficulty()->value,
            'next_eligible_at' => $scorecard->getEligibleAt()->toIso8601String(),
            'flashcard_answers' => $scorecard->answers->map(function (GivenAnswer $answer) {
                return [
                    'id' => $answer->getId(),
                    'text' => $answer->getText(),
                    'is_correct' => $answer->getIsCorrect(),
                    'was_selected' => $answer->getWasSelected(),
                    'explanation' => $answer->getExplanation(),
                ];
            })
        ];
    }
}
