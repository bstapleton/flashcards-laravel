<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string valid_until
 * @property bool auto_renew
 */
class RoleUser extends Pivot
{
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
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
