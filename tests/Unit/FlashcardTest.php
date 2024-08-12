<?php

namespace Tests\Unit;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Services\FlashcardService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use App\Models\Flashcard;

class FlashcardTest extends TestCase
{
    const int EASY_MINUTES = 30;
    const int MEDIUM_MINUTES = 10080;
    const int HARD_MINUTES = 40320;
    protected Flashcard  $flashcard;
    protected FlashcardService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->flashcard = Flashcard::factory()->old()->make();
        $this->service = new FlashcardService();
        $this->service->setFlashcard($this->flashcard);

        $this->flashcard->difficulty = Difficulty::HARD->value;
        $this->flashcard->type = QuestionType::STATEMENT;
        $this->flashcard->is_true = true;
        $this->flashcard->explanation = 'test explanation';
    }

    public function tearDown(): void
    {
        $this->flashcard->delete();

        parent::tearDown();
    }

    public function testFlashcardHasAttributes(): void
    {
        $this->assertTrue($this->flashcard->hasAttribute('text'));
        $this->assertIsString($this->flashcard->text);
        $this->assertTrue($this->flashcard->hasAttribute('last_seen'));
        $this->assertTrue(Carbon::parse($this->flashcard->last_seen)->isLastYear());
        $this->assertIsString(Carbon::parse($this->flashcard->last_seen)->toIso8601String());
        $this->assertTrue($this->flashcard->hasAttribute('difficulty'));
        $this->assertEquals(Difficulty::HARD, $this->flashcard->difficulty);
        $this->assertTrue($this->flashcard->hasAttribute('type'));
        $this->assertEquals(QuestionType::STATEMENT, $this->flashcard->type);
        $this->assertTrue($this->flashcard->hasAttribute('is_true'));
        $this->assertTrue($this->flashcard->is_true);
        $this->assertTrue($this->flashcard->hasAttribute('explanation'));
        $this->assertEquals('test explanation', $this->flashcard->explanation);
    }

    /**
     * Scenario: Increasing the difficulty of a question
     * GIVEN a flashcard
     * AND its difficulty is hard
     * WHEN I trigger an increase in difficulty for it
     * THEN it should increase to the next hardest setting
     * AND the eligibility datetime should be next week
     *
     * @return void
     */
    public function testIncreasingDifficultyForEasy()
    {
        $this->flashcard->difficulty = Difficulty::EASY;
        $this->service->increaseDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::MEDIUM);
        $this->assertTrue(
            Carbon::parse($this->flashcard->eligible_at)
                ->equalTo(Carbon::parse($this->flashcard->last_seen)->addMinutes(10080))
        );
    }

    public function testIncreasingDifficultyForMedium()
    {
        $this->flashcard->difficulty = Difficulty::MEDIUM;
        $this->service->increaseDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::HARD);
    }

    /**
     * Scenario: Increasing the difficulty of a question
     * GIVEN a flashcard
     * AND its difficulty is hard
     * WHEN I trigger an increase in difficulty for it
     * THEN it should increase to the next hardest setting
     *
     * @return void
     */
    public function testIncreasingDifficultyForHard()
    {
        $this->flashcard->difficulty = Difficulty::HARD;
        $this->service->increaseDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::BURIED);
    }

    // TODO: Test eligibility timers

    /**
     * Scenario: Increasing the difficulty of a question when it's already in the graveyard
     * GIVEN a flashcard that is already in the graveyard
     * WHEN I trigger an increase in difficulty for it
     * THEN the difficulty should remain unchanged
     *
     * @return void
     */
    public function testBuriedIdempotency()
    {
        $this->flashcard->difficulty = Difficulty::BURIED;
        $this->service->increaseDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::BURIED);
    }

    public function testResetDifficulty()
    {
        $this->flashcard->difficulty = Difficulty::MEDIUM;
        $this->service->resetDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::EASY);

        $this->flashcard->difficulty = Difficulty::HARD;
        $this->service->resetDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::EASY);

        $this->flashcard->difficulty = Difficulty::BURIED;
        $this->service->resetDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::EASY);
    }

    public function testResetLastSeen()
    {
        $this->service->resetLastSeen();
        $this->assertTrue(Carbon::parse($this->flashcard->last_seen)->isCurrentYear());
    }

    // TODO: validateAnswers
    // TODO: filterValidAnswers
    // TODO: calculateCorrectness
}
