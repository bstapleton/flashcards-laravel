<?php

namespace App\Models;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property int user_id
 * @property int flashcard_id
 * @property string question
 * @property string answers
 * @property array formatted_answers
 * @property QuestionType question_type
 * @property string subjects
 * @property Correctness correctness
 * @property Difficulty difficulty
 * @property int points_earned
 * @property string answered_at
 * @property Collection older_attempts
 * @property Collection newer_attempts
 * @property Collection keywords
 */
class Attempt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'flashcard_id',
        'question',
        'question_type',
        'answers',
        'tags',
        'correctness',
        'difficulty',
        'points_earned',
        'answered_at',
    ];

    protected $casts = [
        'correctness' => Correctness::class,
        'difficulty' => Difficulty::class,
        'answered_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function getQuestionTypeAttribute(): string
    {
        return QuestionType::from($this->attributes['question_type'])->longName();
    }

    public function getFormattedAnswersAttribute(): Collection
    {
        $answers = json_decode($this->answers, true);
        $formattedAnswers = [];

        if (count($answers) === 0) {
            return collect();
        }

        foreach ($answers as $key => $answer) {
            if (! isset($answer['was_selected'], $answer['is_correct'], $answer['text'])) {
                \Log::warning('Missing answer properties. Attempt ID: '.$this->id);
            } elseif (! is_bool($answer['was_selected']) || ! is_bool($answer['is_correct']) || ! is_string($answer['text'])) {
                \Log::error('Invalid answer format. Attempt ID: '.$this->id);
            } else {
                $attemptAnswer = new AttemptAnswer;
                $attemptAnswer->setIsCorrect($answer['is_correct']);
                $attemptAnswer->setWasSelected($answer['was_selected']);
                $attemptAnswer->setText($answer['text']);
                $formattedAnswers[$key] = $attemptAnswer;
            }
        }

        return collect($formattedAnswers);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function getOlderAttemptsAttribute(): Collection
    {
        return Attempt::where('user_id', $this->user_id)
            ->where('flashcard_id', $this->flashcard_id)
            ->where('id', '<', $this->id)
            ->orderBy('answered_at', 'desc')
            ->get();
    }

    public function getNewerAttemptsAttribute(): Collection
    {
        return Attempt::where('user_id', $this->user_id)
            ->where('flashcard_id', $this->flashcard_id)
            ->where('id', '>', $this->id)
            ->orderBy('answered_at', 'asc')
            ->get();
    }

    public function scopeEasy($query)
    {
        return $query->where('difficulty', Difficulty::EASY);
    }

    public function scopeMedium($query)
    {
        return $query->where('difficulty', Difficulty::MEDIUM);
    }

    public function scopeHard($query)
    {
        return $query->where('difficulty', Difficulty::HARD);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('answered_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeLastWeek($query)
    {
        return $query->whereBetween('answered_at', [
            now()->startOfWeek()->subWeek(),
            now()->endOfWeek()->subWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('answered_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }

    public function scopeLastMonth($query)
    {
        return $query->whereBetween('answered_at', [
            now()->startOfMonth()->subMonth(),
            now()->endOfMonth()->subMonth(),
        ]);
    }

    /**
     * Fetch attempts made before they were bindable to a specific question
     */
    public function scopeLegacy($query)
    {
        return $query->whereNull('flashcard_id');
    }
}
