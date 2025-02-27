<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string name
 * @property string code
 */
class Role extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'code'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['valid_until', 'auto_renew']);
    }
}
