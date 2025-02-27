<?php

namespace App\Transformers;

use App\Models\Role;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class RoleTransformer extends TransformerAbstract
{
    public function transform(Role $role): array
    {
        return [
            'name' => $role->name,
            'code' => $role->code,
            'valid_until' => $role->pivot->valid_until
                ? Carbon::parse($role->pivot->valid_until)->toIso8601String()
                : null,
            'auto_renew' => $role->pivot->auto_renew,
        ];
    }
}
