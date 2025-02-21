<?php

namespace Tests\Feature;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlashcardControllerTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->newerQuestion = Flashcard::factory()->easyDifficulty()->create([
            'user_id' => $this->user->id,
            'last_seen_at' => now(),
        ]);
        $this->newerQuestion->answers = Answer::factory()->count(3)->create([
            'flashcard_id' => $this->newerQuestion->id
        ]);
        $tags = Tag::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $this->newerQuestion->tags()->sync($tags->pluck('id')->toArray());

        $this->olderQuestion = Flashcard::factory()->hardDifficulty()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subMinutes(2),
        ]);
    }

    public function test_index_method_returns_all_flashcards()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards');

        $response->assertSuccessful();

        // Assert that the response contains all flashcards
        $response->assertJsonCount(2, 'data');

        // Assert that the response contains the expected data - it's in descending order of last_seen_at then created_at
        $responseData = json_decode($response->getContent(), true);
        $questions = $responseData['data'];

        $this->assertArrayHasKey('id', $questions[0]);
        $this->assertArrayHasKey('type', $questions[0]);
        $this->assertArrayHasKey('text', $questions[0]);
        $this->assertArrayHasKey('difficulty', $questions[0]);
        $this->assertArrayHasKey('eligible_at', $questions[0]);
        $this->assertArrayHasKey('tags', $questions[0]);
        $this->assertArrayHasKey('answers', $questions[0]);

        $this->assertTrue($this->newerQuestion->id === $questions[0]['id']);
        $this->assertTrue($this->newerQuestion->type->value === $questions[0]['type']);
        $this->assertTrue($this->newerQuestion->text === $questions[0]['text']);
        $this->assertTrue($this->newerQuestion->difficulty->value === $questions[0]['difficulty']);
        $this->assertTrue($this->newerQuestion->eligible_at->toIso8601String() === $questions[0]['eligible_at']);
        $this->assertTrue($this->newerQuestion->tags->map(
            function (Tag $tag) {
                return [
                    'name' => $tag->name,
                    'colour' => strtolower($tag->colour->name)
                ];
            })->toArray() === $questions[0]['tags']);
        $this->assertTrue($this->newerQuestion->answers->map(
            function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text
                ];
            })->toArray() === $questions[0]['answers']);
    }

    public function test_index_unauthorized()
    {
        $response = $this->getJson('/api/flashcards');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_index_pagination()
    {
        $user = User::factory()->create([
            'page_limit' => 5
        ]);
        $flashcards = Flashcard::factory()->count(10)->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->getJson('/api/flashcards');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(5, $response['data']);
        $this->assertEquals($flashcards->sortByDesc('created_at')->pluck('id')->take(5)->toArray(), collect($response['data'])->pluck('id')->toArray());
    }

    public function test_all_unauthorized()
    {
        $response = $this->getJson('/api/flashcards/all');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_all_pagination()
    {
        $user = User::factory()->create([
            'page_limit' => 5
        ]);
        $flashcards = Flashcard::factory()->count(10)->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->getJson('/api/flashcards/all');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(5, $response['data']);
        $this->assertEquals($flashcards->sortByDesc('created_at')->pluck('id')->take(5)->toArray(), collect($response['data'])->pluck('id')->toArray());
    }

    public function test_show_success()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/' . $this->newerQuestion->id);

        $response->assertSuccessful();

        $responseData = json_decode($response->getContent(), true);
        $question = $responseData['data'];

        $this->assertTrue($this->newerQuestion->id === $question['id']);
    }

    public function test_show_unauthorized()
    {
        $response = $this->getJson('/api/flashcards/' . $this->newerQuestion->id);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_store_success_multiple_choice_single_correct()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'What is the capital of France?',
            'answers' => [
                ['text' => 'Paris', 'is_correct' => true],
                ['text' => 'London'],
                ['text' => 'Berlin']
            ],
            'tags' => ['history', 'geography']
        ]);

        $response->assertSuccessful();

        $responseData = json_decode($response->getContent(), true);
        $question = $responseData['data'];

        $this->assertTrue($question['text'] === 'What is the capital of France?');
        $this->assertTrue($question['type'] === QuestionType::SINGLE->value);
        $this->assertTrue($question['difficulty'] === Difficulty::EASY->value);
        $this->assertTrue($question['answers'] === Flashcard::find($question['id'])->answers->map(
            function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text
                ];
            })->toArray());
        $this->assertTrue($question['tags'] === Flashcard::find($question['id'])->tags->map(
            function (Tag $tag) {
                return [
                    'name' => $tag->name,
                    'colour' => $tag->colour
                ];
            })->toArray());
    }

    public function test_store_success_multiple_choice_multiple_correct()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'Which of these are savoury foods?',
            'answers' => [
                ['text' => 'Cheese', 'is_correct' => true],
                ['text' => 'Bread', 'is_correct' => true],
                ['text' => 'Ice cream']
            ],
            'tags' => ['food']
        ]);

        $response->assertSuccessful();

        $responseData = json_decode($response->getContent(), true);
        $question = $responseData['data'];

        $this->assertTrue($question['text'] === 'Which of these are savoury foods?');
        $this->assertTrue($question['type'] === QuestionType::MULTIPLE->value);
        $this->assertTrue($question['difficulty'] === Difficulty::EASY->value);
        $this->assertTrue($question['answers'] === Flashcard::find($question['id'])->answers->map(
            function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text
                ];
            })->toArray());
        $this->assertTrue($question['tags'] === Flashcard::find($question['id'])->tags->map(
            function (Tag $tag) {
                return [
                    'name' => $tag->name,
                    'colour' => $tag->colour
                ];
            })->toArray());
    }

    public function test_store_success_statement()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'Iceland is in the northern hemisphere',
            'is_true' => true,
            'tags' => ['geography']
        ]);

        $response->assertSuccessful();

        $responseData = json_decode($response->getContent(), true);
        $question = $responseData['data'];

        $this->assertTrue($question['text'] === 'Iceland is in the northern hemisphere');
        $this->assertTrue($question['type'] === QuestionType::STATEMENT->value);
        $this->assertTrue($question['difficulty'] === Difficulty::EASY->value);
        $this->assertCount(0, $question['answers']);
        $this->assertTrue($question['tags'] === Flashcard::find($question['id'])->tags->map(
            function (Tag $tag) {
                return [
                    'name' => $tag->name,
                    'colour' => $tag->colour
                ];
            })->toArray());
    }

    public function test_store_unauthorized()
    {
        $response = $this->postJson('/api/flashcards', [
            'text' => 'Iceland is in the northern hemisphere',
            'is_true' => true,
            'tags' => ['geography']
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_store_validation_error()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards', [
            'is_true' => true,
            'tags' => ['geography']
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_undetermined_question_type()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'Some weird mutant question',
            'is_true' => true,
            'answers' => [
                [
                    'text' => 'Apple',
                    'is_correct' => true
                ],
                [
                    'text' => 'Banana'
                ]
            ],
            'tags' => ['food']
        ]);

        $response->assertJsonFragment([
            'code' => 'undetermined_question_type'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_less_than_one_correct_answer()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'Some weird mutant question',
            'answers' => [
                [
                    'text' => 'Apple',
                ],
                [
                    'text' => 'Banana'
                ]
            ],
            'tags' => ['food']
        ]);

        $response->assertJsonFragment([
            'code' => 'less_than_one_correct_answer'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }



    // TODO: test update
    // TODO: test destroy
    // TODO: test graveyard
    // TODO: test random
    // TODO: test revive
    // TODO: test hide
    // TODO: test unhide
    // TODO: test answer
    // TODO: test drafts
    // TODO: test hidden
}
