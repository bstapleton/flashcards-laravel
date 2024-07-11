<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 *
 * Type is added as a discrete model to allow for extension into additional
 * types at a later date as desired without binding us to an enum type.
 */
class Type extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name'
    ];
}
