<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lesson extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gets the flashcards as well as their instance for this lesson, along
     * with the associated score and answer date
     *
     * @return BelongsToMany
     */
    public function flashcards(): BelongsToMany
    {
        return $this->belongsToMany(Flashcard::class, 'lesson_flashcard')
            ->withPivot([
                'score',
                'answered_at'
            ])
            ->as('data');
    }

    /**
     * Gets the aggregate score across all flashcards answered in the lesson
     * Does not include any Statement type flashcards
     * Score will fluctuate until the lesson has been ended
     *
     * @return float|int
     */
    public function getScoreAttribute(): float|int
    {
        $selection = $this->flashcards
            ->where('type_id', '<>', Type::where('name', 'Statement')->first()->id)
            ->where('data.answered_at', '<>', null);
        $count = $selection->count();

        // Nothing selected = score is zero
        if (0 === $count) {
            return $count;
        }

        $total = $selection->sum('data.score');

        return $total / $count;
    }
}
