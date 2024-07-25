<?php

namespace App\Http\Controllers;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Helpers\ApiResponse;
use App\Models\Flashcard;
use App\Models\Tag;
use App\Transformers\FlashcardTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FlashcardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/flashcards",
     *     summary="List flashcards",
     *     tags={"flashcard"},
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return fractal(Flashcard::where('user_id', $request->user()->id)
            ->get(), new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards",
     *     summary="Create flashcard",
     *     tags={"flashcard"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="text",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required|max:1024'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/{id}",
     *     summary="Show a flashcard",
     *     tags={"flashcard"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Flashcard not found"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function show(Request $request, Flashcard $flashcard): JsonResponse
    {
        if ($request->user()->cannot('show', $flashcard)) {
            return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
        }

        return fractal($flashcard, new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/random",
     *     description="Gets a random flashcard, regardless of difficulty or tags",
     *     summary="Get a random flashcard",
     *     tags={"flashcard"},
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Flashcard not found"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function random(Request $request): JsonResponse
    {
        return fractal(Flashcard::where('user_id', $request->user()->id)
            ->active()
            ->inRandomOrder()
            ->first(), new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/graveyard",
     *     description="'Buried' flashcards are those which have been anwered correctly on the Hard difficulty",
     *     summary="Get all flashcards currently in the graveyard",
     *     tags={"flashcard"},
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function graveyard(Request $request): JsonResponse
    {
        return fractal(Flashcard::where('user_id', $request->user()->id)
            ->inactive()
            ->get(), new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/{id}/revive",
     *     description="Revive a flashcard from the graveyard back to the easy difficulty",
     *     summary="Resurrect a buried flashcard",
     *     tags={"flashcard"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Flashcard not found"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function revive(Request $request, Flashcard $flashcard): JsonResponse
    {
        if ($request->user()->cannot('revive', $flashcard)) {
            return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
        }

        $flashcard->update([
            'difficulty' => Difficulty::EASY,
        ]);

        return fractal($flashcard, new FlashcardTransformer())->respond();
    }

    public function answer(Request $request, Flashcard $flashcard): JsonResponse
    {
        if ($request->user()->cannot('answer', $flashcard)) {
            return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
        }

        switch ($flashcard->type) {
            case QuestionType::STATEMENT:
                $score = $flashcard->is_true === $request->input('is_true') ? 3 : 0; // TODO: turn into a service to handle the scoring multipliers for easy = 1, medium = 3, hard = 5 points
            case QuestionType::SINGLE:
                // TODO: check given answer is correct
            case QuestionType::MULTIPLE:
                // TODO: handle scoring for multiple choice
        }

        return fractal($flashcard, new FlashcardTransformer())->respond(); // TODO: update response
    }

    /**
     * @OA\Patch(
     *     path="/api/flashcards/{id}",
     *     summary="Update flashcard",
     *     tags={"flashcard"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="text",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Flashcard not found"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function update(Request $request, Flashcard $flashcard): JsonResponse
    {
        if ($request->user()->cannot('update', $flashcard)) {
            return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
        }

        $request->validate([
            'text' => 'required|max:1024'
        ]);

        return fractal($flashcard, new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Delete(
     *     path="/api/flashcards/{id}",
     *     summary="Delete flashcard",
     *     tags={"flashcard"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="No content"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function destroy(Request $request, Flashcard $flashcard): Response|JsonResponse
    {
        if ($request->user()->cannot('delete', $flashcard)) {
            return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
        }

        $flashcard->tags()->detach();
        $flashcard->delete();

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/{id}/tags/{tag}",
     *     summary="Attach a tag to a flashcard",
     *     description="Attach a tag",
     *     tags={"flashcard"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function attachTag(Request $request, Flashcard $flashcard, Tag $tag)
    {
        if ($request->user()->cannot('show', $flashcard)) {
            // You can't see the flashcard, so you can't modify its relations
            return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
        }

        $flashcard->tags()->attach($tag);

        return fractal($flashcard, new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Delete(
     *     path="/api/flashcards/{id}/tags/{tag}",
     *     description="Detach a tag from a flashcard",
     *     summary="Detach a tag",
     *     tags={"flashcard"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function detachTag(Request $request, Flashcard $flashcard, Tag $tag)
    {
        if ($request->user()->cannot('show', $flashcard)) {
            // You can't see the flashcard, so you can't modify its relations
            return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
        }

        $flashcard->tags()->detach($tag);

        return fractal($flashcard, new FlashcardTransformer())->respond();
    }
}
