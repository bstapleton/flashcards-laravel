<?php

return [
    'scoring' => [
        'base_score' => 1,
        'multiple_correct_multiplier' => 2,
    ],
    'difficulty_minutes' => [
        'easy' => 30,
        'medium' => 10080, // 7 days
        'hard' => 40320, // 28 days
    ],
    'free_account_limit' => 20,
];
