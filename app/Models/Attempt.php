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
 * @property string question
 * @property string answers
 * @property array formatted_answers
 * @property QuestionType question_type
 * @property string tags
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
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function getFormattedAnswersAttribute(): Collection
    {
        $answers = json_decode($this->answers, true);

        if (count($answers) === 0) {
            return collect();
        }

        foreach ($answers as $key => $answer) {
            // Check for missing properties
            if (!isset($answer['was_selected'], $answer['is_correct'], $answer['text'])) {
                throw new \RuntimeException('Missing answer properties');
            }

            // Check for malformed content
            if (!is_bool($answer['was_selected']) || !is_bool($answer['is_correct']) || !is_string($answer['text'])) {
                throw new \RuntimeException('Invalid answer format');
            }

            // Map to the correct model
            $attemptAnswer = new AttemptAnswer();
            $attemptAnswer->setIsCorrect($answer['is_correct']);
            $attemptAnswer->setWasSelected($answer['was_selected']);
            $attemptAnswer->setText($answer['text']);
            $answers[$key] = $attemptAnswer;
        }

        return collect($answers);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function getOlderAttemptsAttribute(): Collection
    {
        return Attempt::where('user_id', $this->user_id)
            ->where('question', $this->question)
            ->where('id', '<', $this->id)
            ->orderBy('answered_at', 'desc')
            ->get();
    }

    public function getNewerAttemptsAttribute(): Collection
    {
        return Attempt::where('user_id', $this->user_id)
            ->where('question', $this->question)
            ->where('id', '>', $this->id)
            ->orderBy('answered_at', 'asc')
            ->get();
    }
}
