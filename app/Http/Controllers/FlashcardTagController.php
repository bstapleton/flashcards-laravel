<?php

namespace App\Http\Controllers;

use App\Services\FlashcardService;
use App\Transformers\FlashcardTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;

class FlashcardTagController extends Controller
{
    protected FlashcardService $service;

    public function __construct(FlashcardService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/{flashcard}/tags/{tag}",
     *     summary="Attach a tag to a flashcard",
     *     description="Attach a tag",
     *     tags={"flashcard"},
     *     @OA\Parameter(name="flashcard", in="path", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="tag", in="path", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function attachTag(Request $request, int $id, int $tagId): JsonResponse
    {
        try {
            $flashcardResponse = $this->service->attachTag($id, $tagId);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Delete(
     *     path="/api/flashcards/{flashcard}/tags/{tag}",
     *     description="Detach a tag from a flashcard",
     *     summary="Detach a tag",
     *     tags={"flashcard"},
     *     @OA\Parameter(name="flashcard", in="path", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="tag", in="path", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function detachTag(Request $request, int $id, int $tagId)
    {
        try {
            $flashcardResponse = $this->service->detachTag($id, $tagId);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new FlashcardTransformer())->respond();
    }
}
