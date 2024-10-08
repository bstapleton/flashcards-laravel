<?php

namespace App\Transformers;

use App\Models\Answer;
use App\Models\Scorecard;
use Illuminate\Support\Carbon;
use League\Fractal\TransformerAbstract;

class ScorecardTransformer extends TransformerAbstract
{
    public function transform(Scorecard $scorecard): array
    {
        return [
            'question' => $scorecard->getQuestion(),
            'type' => $scorecard->getType()->value,
            'correctness' => $scorecard->getCorrectness()->value,
            'previous_attempt' => [
                'attempted_at' => !is_null($scorecard->getLastAttempt())
                    ? Carbon::parse($scorecard->getLastAttempt()->answered_at)->toIso8601String()
                    : null,
                'correctness' => !is_null($scorecard->getLastAttempt())
                    ? $scorecard->getLastAttempt()->correctness->value
                    : null,
            ],
            'score' => $scorecard->getScore(),
            'user_current_score' => $scorecard->getTotalScore(),
            'old_difficulty' => $scorecard->getOldDifficulty()->value,
            'new_difficulty' => $scorecard->getNewDifficulty()->value,
            'next_eligible_at' => $scorecard->getEligibleAt()->toIso8601String(),
            'flashcard_answers' => $scorecard->getFlashcardAnswers()->map(function (Answer $answer) use ($scorecard) {
                return [
                    'id' => $answer->id,
                    'is_correct' => $answer->is_correct,
                    'was_selected' => in_array($answer->id, $scorecard->getAnswerGiven()),
                    'text' => $answer->text,
                    'explanation' => $answer->explanation,
                ];
            })
        ];
    }
}
