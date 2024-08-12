<?php

namespace Tests\Unit;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Services\FlashcardService;
use Carbon\Carbon;
use App\Models\Flashcard;
use Tests\TestCase;

class FlashcardTest extends TestCase
{
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
     * Scenario: Increasing the difficulty of a question from easy to medium
     * GIVEN a flashcard
     * AND its difficulty is easy
     * WHEN I trigger an increase in difficulty for it
     * THEN it should increase to medium difficulty
     * AND the eligibility datetime should match what is configured for the medium difficulty
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
                ->equalTo(Carbon::parse($this->flashcard->last_seen)->addMinutes(config('flashcard.difficulty_minutes.medium')))
        );
    }

    /**
     * Scenario: Increasing the difficulty of a question from medium to hard
     * GIVEN a flashcard
     * AND its difficulty is medium
     * WHEN I trigger an increase in difficulty for it
     * THEN it should increase to hard difficulty
     * AND the eligibility datetime should match what is configured for the hard difficulty
     *
     * @return void
     */
    public function testIncreasingDifficultyForMedium()
    {
        $this->flashcard->difficulty = Difficulty::MEDIUM;
        $this->service->increaseDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::HARD);
        $this->assertTrue(
            Carbon::parse($this->flashcard->eligible_at)
                ->equalTo(Carbon::parse($this->flashcard->last_seen)->addMinutes(config('flashcard.difficulty_minutes.hard')))
        );
    }

    /**
     * Scenario: Sending a question to the graveyard by answering correctly when on hard difficulty
     * GIVEN a flashcard
     * AND its difficulty is hard
     * WHEN I trigger an increase in difficulty for it
     * THEN it should go to the graveyard by setting the difficulty to buried
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

    /**
     * Scenario: Resetting the difficulty to easy from medium
     * GIVEN a flashcard that is answered incorrectly
     * WHEN the difficulty is reset
     * THEN it should always be returned to the easiest difficulty
     * AND the eligibility datetime should match what is configured for the easy difficulty
     *
     * @return void
     */
    public function testResetMediumDifficulty()
    {
        $this->flashcard->difficulty = Difficulty::MEDIUM;
        $this->assertFalse($this->flashcard->difficulty === Difficulty::EASY);
        $this->assertFalse(
            Carbon::parse($this->flashcard->eligible_at)
                ->equalTo(Carbon::parse($this->flashcard->last_seen)->addMinutes(config('flashcard.difficulty_minutes.easy')))
        );

        $this->service->resetDifficulty();

        $this->assertTrue($this->flashcard->difficulty === Difficulty::EASY);
        $this->assertTrue(
            Carbon::parse($this->flashcard->eligible_at)
                ->equalTo(Carbon::parse($this->flashcard->last_seen)->addMinutes(config('flashcard.difficulty_minutes.easy')))
        );
    }

    /**
     * Scenario: Resetting the difficulty to easy from hard
     * GIVEN a flashcard that is answered incorrectly
     * WHEN the difficulty is reset
     * THEN it should always be returned to the easiest difficulty
     * AND the eligibility datetime should match what is configured for the easy difficulty
     *
     * @return void
     */
    public function testResetHardDifficulty()
    {
        $this->flashcard->difficulty = Difficulty::HARD;
        $this->assertFalse($this->flashcard->difficulty === Difficulty::EASY);
        $this->assertFalse(
            Carbon::parse($this->flashcard->eligible_at)
                ->equalTo(Carbon::parse($this->flashcard->last_seen)->addMinutes(config('flashcard.difficulty_minutes.easy')))
        );

        $this->service->resetDifficulty();

        $this->assertTrue($this->flashcard->difficulty === Difficulty::EASY);
        $this->assertTrue(
            Carbon::parse($this->flashcard->eligible_at)
                ->equalTo(Carbon::parse($this->flashcard->last_seen)->addMinutes(config('flashcard.difficulty_minutes.easy')))
        );
    }

    /**
     * Scenario: Resetting the difficulty to easy from buried
     * GIVEN a flashcard that is answered incorrectly
     * WHEN the difficulty is reset
     * THEN it should always be returned to the easiest difficulty
     * AND the eligibility datetime should match what is configured for the easy difficulty
     *
     * @return void
     */
    public function testResetBuriedDifficulty()
    {
        $this->flashcard->difficulty = Difficulty::BURIED;
        $this->assertFalse($this->flashcard->difficulty === Difficulty::EASY);
        $this->assertFalse(
            Carbon::parse($this->flashcard->eligible_at)
                ->equalTo(Carbon::parse($this->flashcard->last_seen)->addMinutes(config('flashcard.difficulty_minutes.easy')))
        );

        $this->service->resetDifficulty();

        $this->assertTrue($this->flashcard->difficulty === Difficulty::EASY);
        $this->assertTrue(
            Carbon::parse($this->flashcard->eligible_at)
                ->equalTo(Carbon::parse($this->flashcard->last_seen)->addMinutes(config('flashcard.difficulty_minutes.easy')))
        );
    }

    /**
     * Scenario: Resetting the last_seen datetime
     * GIVEN a flashcard
     * WHEN its last-seen datetime is reset
     * THEN it should be the same as 'now'
     *
     * @return void
     */
    public function testResetLastSeen()
    {
        $this->assertFalse(Carbon::parse($this->flashcard->last_seen)->isCurrentYear());
        $this->service->resetLastSeen();
        $this->assertTrue(Carbon::parse($this->flashcard->last_seen)->isCurrentYear());
    }

    // TODO: validateAnswers
    // TODO: filterValidAnswers
    // TODO: calculateCorrectness
}
