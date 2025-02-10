<?php

namespace App\Models;

use App\Enums\Difficulty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Scorecard extends Attempt
{
    use HasFactory;
    private int $totalScore;
    private Difficulty $newDifficulty;
    private Carbon $eligible_at;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function getTotalScore(): int
    {
        return $this->totalScore;
    }

    public function setTotalScore(int $totalScore): void
    {
        $this->totalScore = $totalScore;
    }

    public function getNewDifficulty(): Difficulty
    {
        return $this->newDifficulty;
    }

    public function setNewDifficulty(Difficulty $newDifficulty): void
    {
        $this->newDifficulty = $newDifficulty;
    }

    public function getEligibleAt(): string
    {
        return $this->eligible_at->diffForHumans();
    }

    public function setEligibleAt(Carbon $eligible_at): void
    {
        $this->eligible_at = $eligible_at->addSecond();
    }
}
