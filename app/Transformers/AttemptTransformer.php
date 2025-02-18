<?php

namespace App\Transformers;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Keyword;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class AttemptTransformer extends TransformerAbstract
{
    public function transform(Attempt $attempt): array
    {
        return [
            'id' => $attempt->id,
            'question' => $attempt->question,
            'correctness' => $attempt->correctness,
            'question_type' => $attempt->question_type,
            'difficulty' => $attempt->difficulty,
            'points_earned' => $attempt->points_earned,
            'answered_at' => Carbon::parse($attempt->answered_at)->toIso8601String(),
            'answers_given' => $attempt->formatted_answers->map(function (AttemptAnswer $answer) {
                return [
                    'text' => $answer->getText(),
                    'was_selected' => $answer->getWasSelected(),
                    'is_correct' => $answer->getIsCorrect(),
                ];
            }),
            'keywords' => $attempt->keywords->map(function (Keyword $keyword) {
                return $keyword->name;
            }),
            'older_attempts' => $attempt->older_attempts ? $attempt->older_attempts->map(function (Attempt $olderAttempt) {
                return (new HistoricAttemptTransformer())->transform($olderAttempt);
            }) : [],
            'newer_attempts' => $attempt->newer_attempts ? $attempt->newer_attempts->map(function (Attempt $newerAttempt) {
                return (new HistoricAttemptTransformer())->transform($newerAttempt);
            }) : [],
        ];
    }
}
