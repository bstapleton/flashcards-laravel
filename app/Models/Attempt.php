<?php

namespace App\Models;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string answered_at
 * @property Difficulty difficulty
 * @property Correctness correctness
 * @property int points_earned
 */
class Attempt extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'flashcard_id',
        'user_id',
        'answered_at',
        'difficulty',
        'correctness',
        'points_earned',
    ];

    protected function casts(): array
    {
        return [
            'correctness' => Correctness::class,
            'difficulty' => Difficulty::class,
        ];
    }

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): BelongsToMany
    {
        return $this->belongsToMany(Answer::class);
    }
}
