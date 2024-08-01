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
    private Difficulty $difficulty;
    private QuestionType $type;
    private Carbon $eligible_at;
    private Collection $answers;

    public function __construct(Flashcard $flashcard)
    {
        $this->setQuestion($flashcard->text);
        $this->setDifficulty($flashcard->difficulty);
        $this->setType($flashcard->type);
        $this->setEligibleAt($flashcard->eligible_at);
        $this->setAnswers($flashcard->answers);
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

    public function getDifficulty(): Difficulty
    {
        return $this->difficulty;
    }

    public function setDifficulty(Difficulty $difficulty): void
    {
        $this->difficulty = $difficulty;
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

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function setAnswers(Collection $answers): void
    {
        $this->answers = $answers;
    }
}
