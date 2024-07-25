<?php

namespace App\Transformers;

use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id' => $user->sqid,
            'name' => $user->name,
            'email' => $user->email,
            'points' => $user->points,
            'created_at' => Carbon::parse($user->created_at)->toIso8601String()
        ];
    }
}
