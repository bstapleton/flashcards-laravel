<?php

namespace App\Models;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Scorecard
{
    use HasFactory;

    private string $question;
    private array $answersGiven;
    private Correctness $correctness;
    private int $score;
    private int $totalScore;
    private Difficulty $oldDifficulty;
    private Difficulty $newDifficulty;
    private QuestionType $type;
    private Carbon $eligible_at;
    private Collection $flashcardAnswers;
    private Attempt $lastAttempt;

    public function __construct(Flashcard $flashcard)
    {
        $this->setQuestion($flashcard->text);
        $this->setOldDifficulty($flashcard->difficulty);
        $this->setType($flashcard->type);
        $this->setFlashcardAnswers($flashcard->answers);
        $this->setLastAttempt($flashcard->lastAttempt());
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getAnswerGiven(): array
    {
        return $this->answersGiven;
    }

    public function setAnswerGiven(array $answersGiven): void
    {
        $this->answersGiven = $answersGiven;
    }

    public function getCorrectness(): Correctness
    {
        return $this->correctness;
    }

    public function setCorrectness(Correctness $correctness): void
    {
        $this->correctness = $correctness;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function getTotalScore(): int
    {
        return $this->totalScore;
    }

    public function setTotalScore(int $totalScore): void
    {
        $this->totalScore = $totalScore;
    }

    public function getOldDifficulty(): Difficulty
    {
        return $this->oldDifficulty;
    }

    public function setOldDifficulty(Difficulty $oldDifficulty): void
    {
        $this->oldDifficulty = $oldDifficulty;
    }

    public function getNewDifficulty(): Difficulty
    {
        return $this->newDifficulty;
    }

    public function setNewDifficulty(Difficulty $newDifficulty): void
    {
        $this->newDifficulty = $newDifficulty;
    }

    public function getType(): QuestionType
    {
        return $this->type;
    }

    public function setType(QuestionType $type): void
    {
        $this->type = $type;
    }

    public function getEligibleAt(): Carbon
    {
        return $this->eligible_at;
    }

    public function setEligibleAt(Carbon $eligible_at): void
    {
        $this->eligible_at = $eligible_at;
    }

    public function getFlashcardAnswers(): Collection
    {
        return $this->flashcardAnswers;
    }

    public function setFlashcardAnswers(Collection $answers): void
    {
        $this->flashcardAnswers = $answers;
    }

    public function getLastAttempt(): Attempt
    {
        return $this->lastAttempt;
    }

    public function setLastAttempt(Attempt $lastAttempt): void
    {
        $this->lastAttempt = $lastAttempt;
    }
}
