<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string name
 */
class Keyword extends Model
{
    use HasFactory;

    protected $table = 'attempt_keyword';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function attempts(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }
}
