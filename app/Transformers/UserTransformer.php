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
            'tag_correctness_breakdown' => $this->getTagCorrectnessBreakdown($user),
        ];
    }

    private function getTagCorrectnessBreakdown(User $user): array
    {
        $tagData = [];

        $user->attempts()
            ->with('keywords')
            ->get()
            ->flatMap(function ($attempt) {
                return $attempt->keywords->map(function ($keyword) use ($attempt) {
                    return [
                        'tag' => $keyword->name,
                        'correctness' => $attempt->correctness->value,
                    ];
                });
            })
            ->groupBy('tag')
            ->each(function ($attempts, $tagName) use (&$tagData) {
                $totalAttempts = $attempts->count();

                $correctCount = $attempts->where('correctness', 'complete')->count();
                $partialCount = $attempts->where('correctness', 'partial')->count();
                $incorrectCount = $attempts->where('correctness', 'none')->count();

                $tagData[] = [
                    'tag' => $tagName,
                    'total_attempts' => $totalAttempts,
                    'correct_count' => $correctCount,
                    'partial_count' => $partialCount,
                    'incorrect_count' => $incorrectCount,
                    'correct_percentage' => $totalAttempts > 0 ? round(($correctCount / $totalAttempts) * 100, 1) : 0,
                    'partial_percentage' => $totalAttempts > 0 ? round(($partialCount / $totalAttempts) * 100, 1) : 0,
                    'incorrect_percentage' => $totalAttempts > 0 ? round(($incorrectCount / $totalAttempts) * 100, 1) : 0,
                ];
            });

        // Sort by total attempts (descending) to show most practiced subjects first
        usort($tagData, function ($a, $b) {
            return $b['total_attempts'] <=> $a['total_attempts'];
        });

        return $tagData;
    }
}
