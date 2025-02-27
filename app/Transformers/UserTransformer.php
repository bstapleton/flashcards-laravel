<?php

namespace App\Transformers;

use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user): array
    {
        return [
            'id' => $user->sqid,
            'name' => $user->display_name,
            'points' => $user->points,
            'created_at' => Carbon::parse($user->created_at)->toIso8601String()
        ];
    }
}
