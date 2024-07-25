<?php

namespace App\Models;

use App\Enums\Difficulty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer user_id
 * @property string text
 * @property Difficulty difficulty
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

    protected function casts(): array
    {
        return [
            'difficulty' => Difficulty::class,
        ];
    }

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

    /**
     * All flashcards not in the graveyard are in the pool.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('difficulty', [Difficulty::BURIED]);
    }

    /**
     * Get al the flashcards buried in the graveyard.
     *
     * @param $query
     * @return mixed
     */
    public function scopeInactive($query)
    {
        return $query->where('difficulty', Difficulty::BURIED);
    }

    public function getEligibleDateTime(): string
    {
        // Never been seen before? Immediately eligible. This is so newly added flashcards will always be available in
        // the pool right after being added.
        if (!$this->last_seen) {
            return Carbon::now()->toIso8601String();
        }

        return match ($this->difficulty) {
            Difficulty::EASY => Carbon::parse($this->last_seen)->addMinutes(30)->toIso8601String(),
            Difficulty::MEDIUM => Carbon::parse($this->last_seen)->addWeek()->toIso8601String(),
            Difficulty::HARD => Carbon::parse($this->last_seen)->addMonth()->toIso8601String(),
            default => Carbon::now()->toIso8601String(),
        };
    }
}
