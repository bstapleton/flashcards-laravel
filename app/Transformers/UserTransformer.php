<?php

namespace App\Transformers;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user): array
    {
        return [
            'id' => $user->sqid,
            'username' => $user->username,
            'name' => $user->display_name,
            'points' => $user->points,
            'created_at' => Carbon::parse($user->created_at)->toIso8601String(),
            'this_week' => [
                'total_attempts' => $user->attempts()->thisWeek()->count(),
                'fresh_learning_questions' => $user->attempts()->thisWeek()->easy()->count(),
                'intermediate_mastery_questions' => $user->attempts()->thisWeek()->medium()->count(),
                'high_mastery_questions' => $user->attempts()->thisWeek()->hard()->count(),
            ],
            'last_week' => [
                'total_attempts' => $user->attempts()->lastWeek()->count(),
                'fresh_learning_questions' => $user->attempts()->lastWeek()->easy()->count(),
                'intermediate_mastery_questions' => $user->attempts()->lastWeek()->medium()->count(),
                'high_mastery_questions' => $user->attempts()->lastWeek()->hard()->count(),
            ],
            'this_month' => [
                'total_attempts' => $user->attempts()->thisMonth()->count(),
                'fresh_learning_questions' => $user->attempts()->thisMonth()->easy()->count(),
                'intermediate_mastery_questions' => $user->attempts()->thisMonth()->medium()->count(),
                'high_mastery_questions' => $user->attempts()->thisMonth()->hard()->count(),
            ],
            'last_month' => [
                'total_attempts' => $user->attempts()->lastMonth()->count(),
                'fresh_learning_questions' => $user->attempts()->lastMonth()->easy()->count(),
                'intermediate_mastery_questions' => $user->attempts()->lastMonth()->medium()->count(),
                'high_mastery_questions' => $user->attempts()->lastMonth()->hard()->count(),
            ],
            'question_count' => $user->total_questions,
            'attempt_count' => $user->total_attempts,
            'options' => [
                'easy_time' => $user->easy_time,
                'medium_time' => $user->medium_time,
                'hard_time' => $user->hard_time,
                'lose_points' => $user->lose_points,
                'page_limit' => $user->page_limit,
            ],
            'roles' => $user->roles->map(function (Role $role) {
                return (new RoleTransformer)->transform($role);
            }),
        ];
    }
}
