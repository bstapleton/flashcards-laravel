<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Events\FlashcardDeleting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * @property integer id
 * @property integer user_id
 * @property string text
 * @property Difficulty difficulty
 * @property string explanation
 * @property QuestionType type
 * @property boolean is_true
 * @property Carbon last_seen_at
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
        'last_seen_at',
    ];

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

    public function getEligibleAtAttribute(User $user = null): Carbon
    {
        if (!$user) {
            $user = Auth::user();
        }

        if (!$this->last_seen_at) {
            return Carbon::now();
        }

        return match ($this->difficulty) {
            Difficulty::EASY => Carbon::parse($this->last_seen_at)->addMinutes($user->easy_time),
            Difficulty::MEDIUM => Carbon::parse($this->last_seen_at)->addMinutes($user->medium_time),
            Difficulty::HARD => Carbon::parse($this->last_seen_at)->addMinutes($user->hard_time),
            Difficulty::BURIED => Carbon::parse($this->last_seen_at)->addCenturies($user->easy_time),
        };
    }

    public function scopeBuried(Builder $query)
    {
        return $query->where(['user_id' => Auth::id(), 'difficulty' => Difficulty::BURIED])
            ->orderBy('last_seen_at', 'desc');
    }

    public function scopeAlive(Builder $query)
    {
        return $query->where('user_id', Auth::id())
            ->whereNot('difficulty', Difficulty::BURIED)
            ->orderBy('last_seen_at', 'desc');
    }
}
