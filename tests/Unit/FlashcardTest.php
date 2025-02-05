<?php

namespace Tests\Unit;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Exceptions\AnswerMismatchException;
use App\Models\Answer;
use App\Models\User;
use App\Repositories\FlashcardRepository;
use App\Services\FlashcardService;
use App\Models\Flashcard;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlashcardTest extends TestCase
{
    use RefreshDatabase;
    const int ANSWER_COUNT = 3;
    protected Flashcard $flashcard;
    protected Flashcard $otherFlashcard;
    protected User $user;
    protected FlashcardService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $answers = Answer::factory()->count(self::ANSWER_COUNT)->create([
            'flashcard_id' => $this->flashcard->id,
        ]);
        foreach ($answers as $answer) {
            $answer->flashcard()->associate($this->flashcard);
        }
        $this->flashcard->answers->first()->update([
            'is_correct' => true,
        ]);

        $this->otherFlashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $moreAnswers = Answer::factory()->count(self::ANSWER_COUNT)->create([
            'flashcard_id' => $this->otherFlashcard->id,
        ]);
        foreach ($moreAnswers as $answer) {
            $answer->flashcard()->associate($this->otherFlashcard);
        }

        $this->service = new FlashcardService(new FlashcardRepository());
        $this->service->setFlashcard($this->flashcard);

        $this->flashcard->difficulty = Difficulty::HARD->value;
        $this->flashcard->is_true = true;
        $this->flashcard->explanation = 'test explanation';
    }

    public function tearDown(): void
    {
        $this->flashcard->answers->each(function ($answer) {
            $answer->delete();
        });
        $this->flashcard->delete();
        $this->otherFlashcard->answers->each(function ($answer) {
            $answer->delete();
        });
        $this->otherFlashcard->delete();
        $this->user->delete();

        parent::tearDown();
    }

    public function testFlashcardHasAttributes(): void
    {
        $this->assertTrue($this->flashcard->hasAttribute('text'));
        $this->assertIsString($this->flashcard->text);
        $this->assertTrue($this->flashcard->hasAttribute('difficulty'));
        $this->assertEquals(Difficulty::HARD, $this->flashcard->difficulty);
        $this->assertTrue($this->flashcard->hasAttribute('type'));
        $this->assertEquals(QuestionType::SINGLE, $this->flashcard->type);
        $this->assertTrue($this->flashcard->hasAttribute('is_true'));
        $this->assertTrue($this->flashcard->is_true);
        $this->assertTrue($this->flashcard->hasAttribute('explanation'));
        $this->assertEquals('test explanation', $this->flashcard->explanation);
        $this->assertTrue($this->flashcard->hasAttribute('eligible_at'));
        $this->assertIsString(Carbon::parse($this->flashcard->eligible_at)->toIso8601String());
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
        $this->service->increaseDifficulty($this->user);
        $this->assertTrue($this->flashcard->difficulty === Difficulty::MEDIUM);
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
        $this->service->increaseDifficulty($this->user);
        $this->assertTrue($this->flashcard->difficulty === Difficulty::HARD);
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
        $this->service->increaseDifficulty($this->user);
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
        $this->service->increaseDifficulty($this->user);
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

        $this->service->resetDifficulty();

        $this->assertTrue($this->flashcard->difficulty === Difficulty::EASY);
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

        $this->service->resetDifficulty();

        $this->assertTrue($this->flashcard->difficulty === Difficulty::EASY);
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

        $this->service->resetDifficulty();

        $this->assertTrue($this->flashcard->difficulty === Difficulty::EASY);
    }

    public function testFlashcardHasAnswers()
    {
        $this->assertCount(self::ANSWER_COUNT, $this->flashcard->answers);
    }

    /**
     * Scenario: User passes answers to a different question
     * GIVEN a consumer passing answers to a question
     * WHEN some of those answers do not belong to the question being answered
     * THEN they should be filtered out
     *
     * @return void
     */
    public function testFilterValidAnswers()
    {
        $actualAnswers = $this->flashcard->answers->pluck('id')->toArray();
        $answers = array_merge(
            $actualAnswers,
            $this->otherFlashcard->answers->pluck('id')->toArray()
        );
        $this->assertCount((self::ANSWER_COUNT * 2), $answers);
        $filtered = $this->service->filterValidAnswers($answers);
        $this->assertCount(self::ANSWER_COUNT, $filtered);
        $this->assertTrue($actualAnswers === $filtered);
    }

    /**
     * Test to ensure that some answers have actually been passed
     *
     * @return void
     * @throws AnswerMismatchException
     */
    public function testValidAnswers()
    {
        $this->assertNotEmpty($this->service->validateAnswers([1, 2]));
    }

    /**
     * Test to ensure that an exception is thrown if no answers are passed
     *
     * @return void
     * @throws AnswerMismatchException
     */
    public function testInvalidAnswers()
    {
        $this->expectException(AnswerMismatchException::class);
        $this->service->validateAnswers([]);
    }

    // TODO: calculateCorrectness
}
