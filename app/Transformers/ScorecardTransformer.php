<?php

namespace App\Transformers;

use App\Models\Answer;
use App\Models\Scorecard;
use League\Fractal\TransformerAbstract;

class ScorecardTransformer extends TransformerAbstract
{
    public function transform(Scorecard $scorecard): array
    {
        return [
            'question' => $scorecard->getQuestion(),
            'answers_given' => $scorecard->getAnswerGiven(),
            'correctness' => $scorecard->getCorrectness()->value,
            'type' => $scorecard->getType()->value,
            'new_difficulty' => $scorecard->getDifficulty()->value,
            'next_eligible_at' => $scorecard->getEligibleAt()->toIso8601String(),
            'score' => $scorecard->getScore(),
            'user_current_score' => $scorecard->getTotalScore(),
            'correct' => $scorecard->getScore() > 0,
            'answers' => $scorecard->getAnswers()->map(function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'is_correct' => $answer->is_correct,
                    'text' => $answer->text,
                    'explanation' => $answer->explanation,
                ];
            })
        ];
    }
}
