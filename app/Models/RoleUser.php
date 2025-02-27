<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int role_id
 * @property int user_id
 * @property string valid_until
 * @property bool auto_renew
 */
class RoleUser extends Pivot
{
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'role_id',
        'user_id',
        'valid_until',
        'auto_renew',
    ];

    protected function casts(): array
    {
        return [
            'auto_renew' => 'boolean'
        ];
    }
}
