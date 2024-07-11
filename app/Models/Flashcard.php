<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string text
 * @property string difficulty
 * @property string last_seen
 * @property Type type
 */
class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'difficulty',
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

    public function getEligibleDateTime()
    {
        // Never been seen before? Immediately eligible.
        if (!$this->last_seen) {
            return Carbon::now()->toIso8601String();
        }

        return match ($this->difficulty) {
            'easy' => Carbon::parse($this->last_seen)->addMinutes(30)->toIso8601String(),
            'medium' => Carbon::parse($this->last_seen)->addWeek()->toIso8601String(),
            'hard' => Carbon::parse($this->last_seen)->addMonth()->toIso8601String(),
            default => Carbon::now()->toIso8601String(),
        };
    }
}
