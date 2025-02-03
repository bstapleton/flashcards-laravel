<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function attempts(): BelongsToMany
    {
        return $this->belongsToMany(Attempt::class);
    }
}
