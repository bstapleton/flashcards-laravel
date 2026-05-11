<?php

namespace App\Transformers;

use App\Enums\QuestionType;
use App\Models\AttemptAnswer;
use App\Models\Scorecard;
use League\Fractal\TransformerAbstract;

class ScorecardTransformer extends TransformerAbstract
{
    public function transform(Scorecard $scorecard): array
    {
        return [
            'attempt_id' => $scorecard->getAttemptId(),
            'flashcard_id' => $scorecard->getFlashcardId(),
            'question' => $scorecard->question,
            'type' => $scorecard->question_type->value,
            'correctness' => $scorecard->correctness->value,
            'score' => $scorecard->points_earned,
            'explanation' => $scorecard->explanation,
            'user_current_score' => $scorecard->getTotalScore(),
            'old_difficulty' => $scorecard->difficulty->value,
            'new_difficulty' => $scorecard->getNewDifficulty()->value,
            'next_eligible_at' => $scorecard->getEligibleAt(),
            'flashcard_answers' => $scorecard->formatted_answers->map(function (AttemptAnswer $answer) use ($scorecard) {
                return [
                    'id' => $scorecard->question_type === QuestionType::STATEMENT ? null : null, // AttemptAnswer doesn't have getId()
                    'text' => $answer->getText(),
                    'is_correct' => $answer->getIsCorrect(),
                    'was_selected' => $answer->getWasSelected(),
                    'explanation' => null, // AttemptAnswer doesn't have getExplanation()
                ];
            }),
        ];
    }
}
