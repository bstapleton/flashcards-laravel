<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'flashcard_tag');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_flashcard')
            ->distinct();
    }
}
