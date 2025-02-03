<?php

namespace App\Transformers;

use App\Enums\QuestionType;
use App\Models\Attempt;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class AttemptTransformer extends TransformerAbstract
{
    public function transform(Attempt $attempt): array
    {
        if ($attempt->flashcard->type->value !== QuestionType::STATEMENT->value) {
            $answers = [];
            foreach ($attempt->answers as $answer) {
                $answers[$answer->text] = $answer->is_correct;
            }

            $answersGiven = $answers;
        }
        
        return [
            'question' => $attempt->flashcard->text,
            'correctness' => $attempt->correctness->value,
            'question_type' => $attempt->flashcard->type->value,
            'difficulty' => $attempt->difficulty->value,
            'points_earned' => $attempt->points_earned,
            'answered_at' => Carbon::parse($attempt->answered_at)->toIso8601String(),
            'answers_given' => $answersGiven ?? [],
        ];
    }
}
