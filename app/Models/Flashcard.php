<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Enums\Status;
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
 * @property int id
 * @property int user_id
 * @property string text
 * @property Difficulty difficulty
 * @property string explanation
 * @property QuestionType type
 * @property bool is_true
 * @property Carbon last_seen_at
 * @property Carbon eligible_at
 * @property Status status
 *
 * @OA\Schema(
 *     required={"text"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="text", type="string"),
 *     @OA\Property(property="difficulty", type="string"),
 *     @OA\Property(property="is_true", type="boolean"),
 *     @OA\Property(property="explanation", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="eligible_at", type="string", format="date-time"),
 *     @OA\Property(property="last_seen_at", type="string", format="date-time"),
 * )
 */
class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'text',
        'difficulty',
        'is_true',
        'explanation',
        'last_seen_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'difficulty' => Difficulty::class,
            'type' => QuestionType::class,
            'is_true' => 'boolean',
            'status' => Status::class,
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
            return new Collection;
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

    public function getEligibleAtAttribute(?User $user = null): Carbon
    {
        if (! $user) {
            $user = Auth::user();
        }

        if (! $this->last_seen_at) {
            return Carbon::now();
        }

        return match ($this->difficulty) {
            Difficulty::EASY => Carbon::parse($this->last_seen_at)->addMinutes($user->easy_time),
            Difficulty::MEDIUM => Carbon::parse($this->last_seen_at)->addMinutes($user->medium_time),
            Difficulty::HARD => Carbon::parse($this->last_seen_at)->addMinutes($user->hard_time),
            Difficulty::BURIED => Carbon::parse($this->last_seen_at)->addCenturies($user->easy_time),
        };
    }

    public function scopeCurrentUser(Builder $query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function scopeBuried(Builder $query)
    {
        return $query->where('difficulty', Difficulty::BURIED);
    }

    public function scopeAlive(Builder $query)
    {
        return $query->whereNot('difficulty', Difficulty::BURIED);
    }

    public function scopeDraft(Builder $query)
    {
        return $query->where('status', Status::DRAFT);
    }

    public function scopePublished(Builder $query)
    {
        return $query->where('status', Status::PUBLISHED);
    }

    public function scopeHidden(Builder $query)
    {
        return $query->where('status', Status::HIDDEN);
    }
}
