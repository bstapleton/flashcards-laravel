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
