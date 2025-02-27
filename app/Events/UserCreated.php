<?php

namespace App\Events;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class UserCreated
{
    use Dispatchable;

    public function __construct(public User $user)
    {
        $user->roles()->attach(Role::where('code', 'user')->first());
    }
}
