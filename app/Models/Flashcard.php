<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Events\FlashcardDeleting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer id
 * @property integer user_id
 * @property string text
 * @property Difficulty difficulty
 * @property string last_seen
 * @property string explanation
 * @property QuestionType type
 * @property boolean is_true
 * @property Carbon eligible_at
 */
class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'difficulty',
        'is_true',
        'explanation',
    ];

    public static function boot()
    {
        static::creating(function ($model) {
            // Make it 'last seen' a year ago, so it's immediately available
            $model->last_seen = NOW()->subYear();
        });

        parent::boot();
    }

    protected function casts(): array
    {
        return [
            'difficulty' => Difficulty::class,
            'type' => QuestionType::class,
            'is_true' => 'boolean',
        ];
    }

    protected $dispatchesEvents = [
        'deleting' => FlashcardDeleting::class,
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'flashcard_tag');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function lastAttempt(): Attempt
    {
        return $this->attempts()->orderBy('answered_at', 'desc')->first();
    }

    public function getCorrectAnswerAttribute(): ?Answer
    {
        if ($this->type !== QuestionType::SINGLE) {
            return null;
        }

        return $this->answers->where('is_correct')->first();
    }

    public function getCorrectAnswersAttribute(): Collection
    {
        if ($this->type !== QuestionType::MULTIPLE) {
            return new Collection();
        }

        return $this->answers->where('is_correct');
    }

    public function getTypeAttribute(): QuestionType
    {
        $answers = $this->answers;

        if ($answers->isEmpty()) {
            return QuestionType::STATEMENT;
        }

        if ($answers->where('is_correct')->count() > 1) {
            return QuestionType::MULTIPLE;
        }

        return QuestionType::SINGLE;
    }

    public function getEligibleAtAttribute(): Carbon
    {
        return match ($this->difficulty) {
            Difficulty::EASY => Carbon::parse($this->last_seen)->addMinutes(config('flashcard.difficulty_minutes.easy')),
            Difficulty::MEDIUM => Carbon::parse($this->last_seen)->addMinutes(config('flashcard.difficulty_minutes.medium')),
            Difficulty::HARD => Carbon::parse($this->last_seen)->addMinutes(config('flashcard.difficulty_minutes.hard')),
            default => Carbon::now(),
        };
    }
}
