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

    public function testIncreasingDifficulty()
    {
        $this->flashcard->difficulty = Difficulty::EASY;
        $this->service->increaseDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::MEDIUM);

        $this->flashcard->difficulty = Difficulty::MEDIUM;
        $this->service->increaseDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::HARD);

        $this->flashcard->difficulty = Difficulty::HARD;
        $this->service->increaseDifficulty();
        $this->assertTrue($this->flashcard->difficulty === Difficulty::BURIED);
    }

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
