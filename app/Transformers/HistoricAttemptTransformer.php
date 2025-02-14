<?php

namespace App\Transformers;

use App\Models\Attempt;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class HistoricAttemptTransformer extends TransformerAbstract
{
    public function transform(Attempt $attempt): array
    {
        return [
            'id' => $attempt->id,
            'correctness' => $attempt->correctness,
            'difficulty' => $attempt->difficulty,
            'points_earned' => $attempt->points_earned,
            'answered_at' => Carbon::parse($attempt->answered_at)->toIso8601String()
        ];
    }
}
