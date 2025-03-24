<?php

namespace Tests\Feature;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Enums\Status;
use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FlashcardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->newerQuestion = Flashcard::factory()->publishedStatus()->easyDifficulty()->create([
            'user_id' => $this->user->id,
            'last_seen_at' => now(),
        ]);
        $this->newerQuestion->answers = Answer::factory()->count(3)->create([
            'flashcard_id' => $this->newerQuestion->id,
        ]);
        $tags = Tag::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $this->newerQuestion->tags()->sync($tags->pluck('id')->toArray());

        $this->olderQuestion = Flashcard::factory()->publishedStatus()->hardDifficulty()->create([
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
                    'colour' => strtolower($tag->colour->name),
                ];
            })->toArray() === $questions[0]['tags']);
        $this->assertTrue($this->newerQuestion->answers->map(
            function (Answer $answer) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text,
                ];
            })->toArray() === $questions[0]['answers']);
    }

    public function test_index_unauthorized()
    {
        $response = $this->getJson('/api/flashcards');

        $response->assertJsonFragment([
            'code' => 'unauthenticated',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_index_pagination()
    {
        $user = User::factory()->create([
            'page_limit' => 5,
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

        $response->assertJsonFragment([
            'code' => 'unauthenticated',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_all_pagination()
    {
        $user = User::factory()->create([
            'page_limit' => 5,
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

        $response = $this->getJson('/api/flashcards/'.$this->newerQuestion->id);

        $response->assertSuccessful();

        $responseData = json_decode($response->getContent(), true);
        $question = $responseData['data'];

        $this->assertTrue($this->newerQuestion->id === $question['id']);
    }

    public function test_show_unauthorized()
    {
        $response = $this->getJson('/api/flashcards/'.$this->newerQuestion->id);

        $response->assertJsonFragment([
            'code' => 'unauthenticated',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_show_not_found()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/'. 999999999);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_store_success_multiple_choice_single_correct()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'What is the capital of France?',
            'answers' => [
                ['text' => 'Paris', 'is_correct' => true],
                ['text' => 'London'],
                ['text' => 'Berlin'],
            ],
            'tags' => ['history', 'geography'],
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
                    'text' => $answer->text,
                    'explanation' => $answer->explanation,
                    'is_correct' => $answer->is_correct,
                ];
            })->toArray());
        $this->assertTrue($question['tags'] === Flashcard::find($question['id'])->tags->map(
            function (Tag $tag) {
                return [
                    'name' => $tag->name,
                    'colour' => $tag->colour,
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
                ['text' => 'Ice cream'],
            ],
            'tags' => ['food'],
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
                    'text' => $answer->text,
                    'explanation' => $answer->explanation,
                    'is_correct' => $answer->is_correct,
                ];
            })->toArray());
        $this->assertTrue($question['tags'] === Flashcard::find($question['id'])->tags->map(
            function (Tag $tag) {
                return [
                    'name' => $tag->name,
                    'colour' => $tag->colour,
                ];
            })->toArray());
    }

    public function test_store_success_statement()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'Iceland is in the northern hemisphere',
            'is_true' => true,
            'tags' => ['geography'],
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
                    'colour' => $tag->colour,
                ];
            })->toArray());
    }

    public function test_store_multiple_choice_answer_limit()
    {
        $this->actingAs($this->user);

        $answers = [];

        for ($i = 0; $i < (config('flashcard.answer_per_question_limit') + 5); $i++) {
            $answers[] = [
                'text' => fake()->text(50),
                'is_correct' => true,
            ];
        }

        $response = $this->postJson('/api/flashcards', [
            'text' => 'Which of these are savoury foods?',
            'answers' => $answers,
        ]);

        $responseData = json_decode($response->getContent(), true);
        $question = $responseData['data'];

        $this->assertCount(config('flashcard.answer_per_question_limit'), $question['answers']);
    }

    public function test_store_free_limit()
    {
        $this->actingAs($this->user);

        Flashcard::factory()->count(config('flashcard.free_account_limit'))->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'Iceland is in the northern hemisphere',
            'is_true' => true,
            'tags' => ['geography'],
        ]);

        $response->assertJsonFragment([
            'code' => 'free_account_limit',
        ]);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_store_advanced_unlimited()
    {
        $this->actingAs($this->user);

        $this->user->roles()->attach(Role::where('code', 'advanced_user')->first()->id);

        Flashcard::factory()->count(config('flashcard.free_account_limit'))->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'Iceland is in the northern hemisphere',
            'is_true' => true,
            'tags' => ['geography'],
        ]);

        $response->assertSuccessful();
    }

    public function test_store_unauthorized()
    {
        $response = $this->postJson('/api/flashcards', [
            'text' => 'Iceland is in the northern hemisphere',
            'is_true' => true,
            'tags' => ['geography'],
        ]);

        $response->assertJsonFragment([
            'code' => 'unauthenticated',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_store_validation_error()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards', [
            'is_true' => true,
            'tags' => ['geography'],
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
                    'is_correct' => true,
                ],
                [
                    'text' => 'Banana',
                ],
            ],
            'tags' => ['food'],
        ]);

        $response->assertJsonFragment([
            'code' => 'undetermined_question_type',
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
                    'text' => 'Banana',
                ],
            ],
            'tags' => ['food'],
        ]);

        $response->assertJsonFragment([
            'code' => 'less_than_one_correct_answer',
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_update_success()
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'explanation' => fake()->sentence(),
            'is_true' => false,
        ]);

        $originalText = $flashcard->text;
        $originalExplanation = $flashcard->explanation;

        $this->actingAs($this->user);

        $response = $this->patchJson('/api/flashcards/'.$flashcard->id, [
            'text' => 'Something new',
            'explanation' => 'Extensive waffle',
            'is_true' => true,
        ]);

        $this->assertTrue($response['data']['text'] !== $originalText);
        $this->assertTrue($response['data']['explanation'] !== $originalExplanation);
        $this->assertTrue($response['data']['is_true']);

        $updated = Flashcard::find($flashcard->id);

        $this->assertTrue($updated->is_true);
    }

    public function test_update_unauthorized()
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'explanation' => fake()->sentence(),
            'is_true' => false,
        ]);

        $response = $this->patchJson('/api/flashcards/'.$flashcard->id, [
            'text' => fake()->text(20),
        ]);

        $response->assertJsonFragment([
            'code' => 'unauthenticated',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_update_not_found()
    {
        $this->actingAs($this->user);

        $response = $this->patchJson('/api/flashcards/'. 999999999, [
            'text' => fake()->text(20),
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * If the question was never set up as a statement type, you cannot modify its truthiness, so it just ignores it
     *
     * @return void
     */
    public function test_update_truthiness_idempotency()
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->patchJson('/api/flashcards/'.$flashcard->id, [
            'is_true' => true,
        ]);

        $response->assertJsonFragment([
            'is_true' => null,
        ]);

        $response = $this->patchJson('/api/flashcards/'.$flashcard->id, [
            'is_true' => false,
        ]);

        $response->assertJsonFragment([
            'is_true' => null,
        ]);
    }

    public function test_destroy_flashcard_returns_204()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson('/api/flashcards/'.$flashcard->id);

        $response->assertNoContent();
    }

    public function test_destroy_flashcard_deletes_flashcard()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->deleteJson('/api/flashcards/'.$flashcard->id);

        $this->assertDatabaseMissing('flashcards', ['id' => $flashcard->id]);
    }

    public function test_destroy_flashcard_returns_404_if_flashcard_not_found()
    {
        $this->actingAs($this->user);

        $response = $this->deleteJson('/api/flashcards/'. 999999999);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_destroy_another_users_flashcard_returns_not_found()
    {
        $newUser = User::factory()->create();
        $this->actingAs($newUser);

        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson('/api/flashcards/'.$flashcard->id);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertNotNull(Flashcard::find($flashcard->id));
    }

    public function test_destroy_flashcard_returns_401_if_not_authenticated()
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson('/api/flashcards/'.$flashcard->id);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_graveyard_returns_200()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/graveyard');

        $response->assertSuccessful();
    }

    public function test_graveyard_returns_buried_flashcards()
    {
        $this->actingAs($this->user);

        $buriedFlashcard = Flashcard::factory()->buriedDifficulty()->create([
            'user_id' => $this->user->id,
        ]);

        $easyFlashcard = Flashcard::factory()->easyDifficulty()->create([
            'user_id' => $this->user->id,
        ]);

        $mediumFlashcard = Flashcard::factory()->mediumDifficulty()->create([
            'user_id' => $this->user->id,
        ]);

        $hardFlashcard = Flashcard::factory()->hardDifficulty()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/flashcards/graveyard');

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($buriedFlashcard->id, $responseData['data'][0]['id']);
        $this->assertCount(1, $responseData['data']);
    }

    public function test_graveyard_returns_empty_array_if_no_buried_flashcards()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/graveyard');

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals([], $responseData['data']);
    }

    public function test_graveyard_returns_401_if_not_authenticated()
    {
        $response = $this->getJson('/api/flashcards/graveyard');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_graveyard_paginates_results()
    {
        $this->actingAs($this->user);

        Flashcard::factory($this->user->page_limit + 10)->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::BURIED,
        ]);

        $response = $this->getJson('/api/flashcards/graveyard');

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount($this->user->page_limit, $responseData['data']); // assuming page limit is 10
    }

    public function test_random_flashcard_returns_200()
    {
        Flashcard::factory()->count(10)->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/random');

        $response->assertSuccessful();
    }

    public function test_random_flashcard_returns_flashcard_data()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/random');

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('id', $responseData['data']);
        $this->assertArrayHasKey('text', $responseData['data']);
    }

    public function test_random_flashcard_with_no_flashcards_published()
    {
        // Arrange: create a user with no flashcards
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/flashcards/random');

        $response->assertSuccessful();
        $this->assertTrue($response['data']['code'] === 'nothing_eligible');
        $this->assertNull($response['data']['next_eligible_at']);
    }

    public function test_random_flashcard_but_nothing_currently_eligible()
    {
        // Arrange: create a user with no eligible flashcards
        $user = User::factory()->create(['easy_time' => 60]);
        Flashcard::factory()->publishedStatus()->create([
            'user_id' => $user->id,
            'last_seen_at' => Carbon::now()->subMinutes(2),
        ]);
        $this->actingAs($user);

        $response = $this->getJson('/api/flashcards/random');

        $response->assertSuccessful();
        $this->assertTrue($response['data']['code'] === 'nothing_eligible');
        $this->assertNotNull($response['data']['next_eligible_at']);
    }

    public function test_random_flashcard_returns_401_if_not_authenticated()
    {
        $response = $this->getJson('/api/flashcards/random');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_revive_returns_200()
    {
        $this->actingAs($this->user);

        $buriedFlashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::BURIED,
        ]);

        $response = $this->postJson('/api/flashcards/'.$buriedFlashcard->id.'/revive');

        $response->assertSuccessful();
    }

    public function test_revive_revives_buried_flashcard()
    {
        $this->actingAs($this->user);

        $buriedFlashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::BURIED,
        ]);

        $this->postJson('/api/flashcards/'.$buriedFlashcard->id.'/revive');

        $revivedFlashcard = Flashcard::find($buriedFlashcard->id);
        $this->assertEquals(Difficulty::EASY, $revivedFlashcard->difficulty);
    }

    public function test_revive_returns_404_if_flashcard_not_found()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/'. 999999999 .'/revive');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_revive_returns_401_if_not_authenticated()
    {
        $buriedFlashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::BURIED,
        ]);

        $response = $this->postJson('/api/flashcards/'.$buriedFlashcard->id.'/revive');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_revive_unhides_flashcard_if_it_was_hidden()
    {
        $this->actingAs($this->user);

        $buriedFlashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::BURIED,
            'status' => Status::HIDDEN,
        ]);

        $this->postJson('/api/flashcards/'.$buriedFlashcard->id.'/revive');

        $revivedFlashcard = Flashcard::find($buriedFlashcard->id);
        $this->assertEquals(Status::PUBLISHED, $revivedFlashcard->status);
    }

    public function test_hide_returns_200()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->publishedStatus()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id.'/hide');

        $response->assertSuccessful();
    }

    public function test_hide_hides_flashcard()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->easyDifficulty()->publishedStatus()->create([
            'user_id' => $this->user->id,
        ]);

        $this->postJson('/api/flashcards/'.$flashcard->id.'/hide');

        $hiddenFlashcard = Flashcard::find($flashcard->id);
        $this->assertEquals(Status::HIDDEN, $hiddenFlashcard->status);
        $this->assertEquals(Difficulty::EASY, $hiddenFlashcard->difficulty);
    }

    public function test_hide_returns_404_if_flashcard_not_found()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/'. 999999999 .'/hide');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_hide_returns_401_if_not_authenticated()
    {
        $flashcard = Flashcard::factory()->publishedStatus()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id.'/hide');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_hide_cannot_change_status_if_draft()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->draftStatus()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id.'/hide');

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_unhide_returns_200()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'status' => Status::HIDDEN,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id.'/unhide');

        $response->assertSuccessful();
    }

    public function test_unhide_unhides_flashcard()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'status' => Status::HIDDEN,
        ]);

        $this->postJson('/api/flashcards/'.$flashcard->id.'/unhide');

        $unhiddenFlashcard = Flashcard::find($flashcard->id);
        $this->assertEquals(Status::PUBLISHED, $unhiddenFlashcard->status);
    }

    public function test_unhide_returns_404_if_flashcard_not_found()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/'. 999999999 .'/unhide');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_unhide_returns_401_if_not_authenticated()
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'status' => Status::HIDDEN,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id.'/unhide');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_unhide_cannot_change_status_if_draft()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'status' => Status::DRAFT,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id.'/unhide');

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * A question with one correct answer but the consumer gets the answer wrong
     *
     * @return void
     */
    public function test_answering_incorrectly_multiple_choice_single_correct_answer()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $answers = Answer::factory()->count(2)->create(['flashcard_id' => $flashcard->id]);

        $answers->first()->update([
            'is_correct' => true,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                $answers->reverse()->first()->id,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::NONE,
            'type' => QuestionType::SINGLE,
            'score' => 0,
        ]);
    }

    /**
     * A question with one correct answer and the consumer selects it correctly
     *
     * @return void
     */
    public function test_answering_correctly_multiple_choice_single_correct_answer()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $answers = Answer::factory()->count(2)->create(['flashcard_id' => $flashcard->id]);

        $answers->first()->update([
            'is_correct' => true,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                $answers->first()->id,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::COMPLETE,
            'type' => QuestionType::SINGLE,
        ]);

        $this->assertTrue($response['data']['score'] > 0);
    }

    /**
     * A question with more than one correct answer, but the consumer does not select any of them
     *
     * @return void
     */
    public function test_answering_multiple_choice_multiple_correct_answers_consumer_selects_none_of_them()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $answers = Answer::factory()->count(3)->create(['flashcard_id' => $flashcard->id, 'is_correct' => true]);

        $answers->first()->update([
            'is_correct' => false,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                $answers->first()->id,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::NONE,
            'type' => QuestionType::MULTIPLE,
            'score' => 0,
        ]);
    }

    /**
     * A question with more than one correct answer, but the consumer does not select all of them
     *
     * @return void
     */
    public function test_answering_multiple_choice_multiple_correct_answers_user_selects_some_of_them()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $answers = Answer::factory()->count(3)->create(['flashcard_id' => $flashcard->id, 'is_correct' => true]);

        $answers->first()->update([
            'is_correct' => false,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                $answers->first()->id,
                $answers->reverse()->first()->id,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::PARTIAL,
            'type' => QuestionType::MULTIPLE,
            'score' => 0,
        ]);
    }

    /**
     * A question with more than one correct answer, and all the correct ones are selected
     *
     * @return void
     */
    public function test_answering_completely_correct_multiple_choice_multiple_correct_answers()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $answers = Answer::factory()->count(3)->create(['flashcard_id' => $flashcard->id, 'is_correct' => false]);

        $answers->first()->update([
            'is_correct' => true,
        ]);

        $answers->reverse()->first()->update([
            'is_correct' => true,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                $answers->first()->id,
                $answers->reverse()->first()->id,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::COMPLETE,
            'type' => QuestionType::MULTIPLE,
        ]);

        $this->assertTrue($response['data']['score'] > 0);
    }

    /**
     * A question with more than one correct answer, and all the correct ones are selected AND at least one is incorrect
     * as well
     *
     * @return void
     */
    public function test_multiple_choice_multiple_correct_answers_user_selects_some_wrong_as_well()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $answers = Answer::factory()->count(3)->create(['flashcard_id' => $flashcard->id, 'is_correct' => false]);

        $answers->first()->update([
            'is_correct' => true,
        ]);

        $answers->reverse()->first()->update([
            'is_correct' => true,
        ]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => $answers->pluck('id')->toArray(),
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::PARTIAL,
            'type' => QuestionType::MULTIPLE,
            'score' => 0,
        ]);
    }

    public function test_answering_true_statement_incorrectly()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->trueStatement()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                false,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::NONE,
            'type' => QuestionType::STATEMENT,
            'score' => 0,
        ]);
    }

    public function test_answering_true_statement_correctly()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->trueStatement()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                true,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::COMPLETE,
            'type' => QuestionType::STATEMENT,
        ]);

        $this->assertTrue($response['data']['score'] > 0);
    }

    public function test_answering_false_statement_incorrectly()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->falseStatement()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                true,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::NONE,
            'type' => QuestionType::STATEMENT,
            'score' => 0,
        ]);
    }

    public function test_answering_false_statement_correctly()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->falseStatement()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                false,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::COMPLETE,
            'type' => QuestionType::STATEMENT,
        ]);

        $this->assertTrue($response['data']['score'] > 0);
    }

    public function test_answering_another_user_question()
    {
        $this->actingAs($this->user);

        $newUser = User::factory()->create();

        $flashcard = Flashcard::factory()->falseStatement()->create(['user_id' => $newUser->id]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                false,
            ],
        ]);

        $response->assertStatus(404);
    }

    public function test_answering_unauthenticated()
    {
        $flashcard = Flashcard::factory()->falseStatement()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                false,
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_answering_handles_malformed_data_array()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::factory()->falseStatement()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [
                fake()->sentence(3),
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'correctness' => Correctness::NONE,
            'type' => QuestionType::STATEMENT,
            'score' => 0,
        ]);
    }

    public function test_drafts_success()
    {
        $newUser = User::factory()->create();
        $this->actingAs($newUser);

        Flashcard::factory()->draftStatus()->count(2)->create(['user_id' => $newUser->id]);
        $hidden = Flashcard::factory()->hiddenStatus()->count(2)->create(['user_id' => $newUser->id]);
        $published = Flashcard::factory()->publishedStatus()->count(2)->create(['user_id' => $newUser->id, 'is_true' => true]);

        $response = $this->getJson('/api/flashcards/drafts');

        $response->assertSuccessful();
        $this->assertCount(2, $response['data']);
        $this->assertNotContains($hidden->pluck('id')->toArray(), collect($response['data'])->pluck('id')->toArray());
        $this->assertNotContains($published->pluck('id')->toArray(), collect($response['data'])->pluck('id')->toArray());
    }

    public function test_drafts_unauthorised()
    {
        $response = $this->getJson('/api/flashcards/drafts');

        $response->assertStatus(401);
    }

    public function test_hidden_success()
    {
        $newUser = User::factory()->create();
        $this->actingAs($newUser);

        Flashcard::factory()->hiddenStatus()->count(2)->create(['user_id' => $newUser->id]);
        $draft = Flashcard::factory()->draftStatus()->count(2)->create(['user_id' => $newUser->id]);
        $published = Flashcard::factory()->publishedStatus()->count(2)->create(['user_id' => $newUser->id, 'is_true' => true]);

        $response = $this->getJson('/api/flashcards/hidden');

        $response->assertSuccessful();
        $this->assertCount(2, $response['data']);
        $this->assertNotContains($draft->pluck('id')->toArray(), collect($response['data'])->pluck('id')->toArray());
        $this->assertNotContains($published->pluck('id')->toArray(), collect($response['data'])->pluck('id')->toArray());
    }

    public function test_hidden_unauthorised()
    {
        $response = $this->getJson('/api/flashcards/hidden');

        $response->assertStatus(401);
    }

    public function test_expired_trial_users_cannot_store()
    {
        $user = User::factory()->twoMonthsOld()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/flashcards', [
            'text' => 'Iceland is in the northern hemisphere',
            'is_true' => true,
            'tags' => ['geography'],
        ]);

        $response->assertUnauthorized();
    }

    public function test_expired_trial_users_cannot_update()
    {
        $user = User::factory()->twoMonthsOld()->create();

        $this->actingAs($user);
        $flashcard = Flashcard::factory()->trueStatement()->create(['user_id' => $user->id]);

        $response = $this->patchJson('/api/flashcards/'.$flashcard->id, [
            'text' => 'Something new',
            'explanation' => 'Extensive waffle',
            'is_true' => false,
        ]);

        $response->assertUnauthorized();
        $this->assertTrue($flashcard->is_true);
        $this->assertFalse($flashcard->text === 'Something new');
        $this->assertFalse($flashcard->explanation === 'Extensive waffle');
    }

    public function test_expired_trial_users_cannot_answer()
    {
        $user = User::factory()->twoMonthsOld()->create();
        $this->actingAs($user);

        $flashcard = Flashcard::factory()->trueStatement()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/flashcards/'.$flashcard->id, [
            'answers' => [true],
        ]);

        $response->assertUnauthorized();
    }

    public function test_import_returns_200_for_literature()
    {
        $this->actingAs($this->user);
        $existingCount = $this->user->flashcards->count();

        $response = $this->postJson('/api/flashcards/import', ['topic' => 'literature']);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                'count',
                'imported',
                'remaining',
            ],
        ]);

        $this->assertTrue($response['data']['count'] === 10 + $existingCount);
        $this->assertTrue($response['data']['imported'] === 10);
        $this->assertTrue($response['data']['remaining'] === config('flashcard.free_account_limit') - $existingCount - 10);
    }

    public function test_import_returns_200_for_physics()
    {
        $this->actingAs($this->user);
        $existingCount = $this->user->flashcards->count();

        $response = $this->postJson('/api/flashcards/import', ['topic' => 'physics']);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                'count',
                'imported',
                'remaining',
            ],
        ]);

        $this->assertTrue($response['data']['count'] === 10 + $existingCount);
        $this->assertTrue($response['data']['imported'] === 10);
        $this->assertTrue($response['data']['remaining'] === config('flashcard.free_account_limit') - $existingCount - 10);
    }

    public function test_import_returns_200_for_dogs()
    {
        $this->actingAs($this->user);
        $existingCount = $this->user->flashcards->count();

        $response = $this->postJson('/api/flashcards/import', ['topic' => 'dogs']);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                'count',
                'imported',
                'remaining',
            ],
        ]);

        $this->assertTrue($response['data']['count'] === 10 + $existingCount);
        $this->assertTrue($response['data']['imported'] === 10);
        $this->assertTrue($response['data']['remaining'] === config('flashcard.free_account_limit') - $existingCount - 10);
    }

    public function test_import_idempotency()
    {
        $this->actingAs($this->user);
        $existingCount = $this->user->flashcards->count();
        $questionText = 'Which of the following is the strongest fundamental force?';

        $response = $this->postJson('/api/flashcards/import', ['topic' => 'physics']);
        $question = Flashcard::where('text', $questionText)->where('user_id', $this->user->id)->first();

        if (! $question) {
            $this->markTestSkipped('Question mismatch against the import. Did you change the data? Expected text was: "'.$questionText.'"');
        }

        $answerCount = $question->answers()->count();

        // Run it again to try to duplicate stuff
        $this->postJson('/api/flashcards/import', ['topic' => 'physics']);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                'count',
                'imported',
                'remaining',
            ],
        ]);

        // Then just ensure everything is present and correct, but not duplicated
        $this->assertCount($answerCount, Flashcard::where('text', $questionText)
            ->where('user_id', $this->user->id)
            ->first()
            ->answers);
        $this->assertTrue($response['data']['count'] === 10 + $existingCount);
        $this->assertTrue($response['data']['imported'] === 10);
        $this->assertTrue($response['data']['remaining'] === config('flashcard.free_account_limit') - $existingCount - 10);
    }

    public function test_import_requires_topic_parameter()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/import');

        $response->assertStatus(422);
    }

    public function test_import_throws_file_not_found_exception_if_topic_file_does_not_exist()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/import', ['topic' => 'non-existent-topic']);

        $response->assertStatus(404);
    }

    public function test_import_returns_401_if_user_is_not_authenticated()
    {
        $response = $this->postJson('/api/flashcards/import', ['topic' => 'literature']);

        $response->assertStatus(401);
    }
}
