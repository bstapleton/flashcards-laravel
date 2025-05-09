<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Flashcard;
use App\Transformers\AnswerTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class AnswerController extends Controller
{
    // TODO: add policy-based error handling
    /**
     * @OA\Post(
     *     path="/api/answers",
     *     summary="Create an answer",
     *     tags={"answer"},
     *
     *     @OA\Parameter(name="flashcardId", in="query", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="text", type="string"),
     *             @OA\Property(property="explanation", type="string"),
     *             @OA\Property(property="is_correct", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Flashcard not found"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function store(FormRequest $request)
    {
        $flashcard = Flashcard::find($request->get('flashcardId'));
        if (! $flashcard) {
            return response()->json(ModelNotFoundException::class, 404);
        }

        $answer = new Answer;
        $answer->text = $request->input('text');
        $answer->explanation = $request->input('explanation');
        $answer->is_correct = $request->input('is_correct');
        $answer->flashcard()->associate($flashcard);
        $answer->save();

        return fractal($answer, new AnswerTransformer)->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/answers/{id}",
     *     summary="Get an answer",
     *     tags={"answer"},
     *
     *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Answer not found"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function show(Answer $answer): JsonResponse
    {
        return fractal($answer, new AnswerTransformer)->respond();
    }

    /**
     * @OA\Patch(
     *     path="/api/answers/{id}",
     *     summary="Update an answer",
     *     tags={"answer"},
     *
     *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="text", type="string"),
     *             @OA\Property(property="explanation", type="string"),
     *             @OA\Property(property="is_correct", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function update(FormRequest $request, Answer $answer): JsonResponse
    {
        $answer->update($request->all());

        return fractal($answer, new AnswerTransformer)->respond();
    }

    /**
     * @OA\Delete(
     *     path="/api/answers/{id}",
     *     summary="Delete an answer",
     *     tags={"answer"},
     *
     *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response="204", description="No content"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function destroy(Answer $answer)
    {
        $answer->delete();

        return response()->noContent();
    }
}
