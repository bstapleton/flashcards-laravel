<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property string text
 * @property string explanation
 * @property boolean is_correct
 */
class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'flashcard_id',
        'text',
        'explanation',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean'
    ];

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }
}
