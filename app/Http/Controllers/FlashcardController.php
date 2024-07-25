<?php

namespace App\Http\Controllers;

use App\Enums\Difficulty;
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
     *     description="List flashcards",
     *     tags={"flashcard"},
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(): JsonResponse
    {
        return fractal(Flashcard::all(), new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards",
     *     description="Create flashcard",
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
     *     description="Show a flashcard",
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
     *     description="Get a random flashcard",
     *     summary="Gets a random flashcard, regardless of difficulty or tags",
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
     * @OA\Post(
     *     path="/api/flashcards/{id}/promote",
     *     description="Promote a flashcard from the graveyard back to the easy difficulty",
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
    public function promote(Request $request, Flashcard $flashcard): JsonResponse
    {
        if ($request->user()->cannot('promote', $flashcard)) {
            return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
        }

        $flashcard->update([
            'difficulty' => Difficulty::EASY,
        ]);

        return fractal($flashcard, new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Patch(
     *     path="/api/flashcards/{id}",
     *     description="Update flashcard",
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
     *     description="Delete flashcard",
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

        $flashcard->lessons()->detach();
        $flashcard->tags()->detach();
        $flashcard->delete();

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/{id}/tags/{tag}",
     *     summary="Attach a tag",
     *     description="Attach a tag to a flashcard",
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
     *     description="Detach a tag",
     *     summary="Detach a tag from a flashcard",
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
