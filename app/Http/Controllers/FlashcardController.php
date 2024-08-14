<?php

namespace App\Http\Controllers;

use App\Enums\Correctness;
use App\Enums\QuestionType;
use App\Exceptions\AnswerMismatchException;
use App\Helpers\ApiResponse;
use App\Helpers\Score;
use App\Models\Flashcard;
use App\Models\Scorecard;
use App\Services\FlashcardService;
use App\Transformers\FlashcardTransformer;
use App\Transformers\ScorecardTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\UnauthorizedException;

class FlashcardController extends Controller
{
    protected FlashcardService $service;

    public function __construct(FlashcardService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards",
     *     summary="List all active flashcards",
     *     description="Return all flashcards that are not in the graveyard, for the current user, paginated",
     *     tags={"flashcard"},
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $flashcards = $this->service->alive();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcards, new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/all",
     *     summary="List all flashcards",
     *     description="Return all flashcards - alive or buried - for the current user, paginated",
     *     tags={"flashcard"},
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function all(Request $request): JsonResponse
    {
        try {
            $flashcards = $this->service->all();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcards, new FlashcardTransformer())->respond();
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
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $flashcardResponse = $this->service->show($id);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new FlashcardTransformer())->respond();
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
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|max:1024'
        ]);

        try {
            $flashcardResponse = $this->service->store($request->all());
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        // TODO: validation and storage

        return fractal($flashcardResponse, new FlashcardTransformer())->respond();
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
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'text' => 'required|max:1024'
        ]);
        // TODO validation

        try {
            $flashcardResponse = $this->service->update($request->all(), $id);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new FlashcardTransformer())->respond();
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
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function destroy(Request $request, int $id): Response|JsonResponse
    {
        try {
            $this->service->destroy($id);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/graveyard",
     *     summary="Get all flashcards currently in the graveyard",
     *     description="'Buried' flashcards are those which have been anwered correctly on the Hard difficulty",
     *     tags={"flashcard"},
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function graveyard(Request $request): JsonResponse
    {
        try {
            $flashcards = $this->service->buried($request->user());
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcards, new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/random",
     *     description="Gets a random flashcard, regardless of difficulty or tags",
     *     summary="Get a random flashcard",
     *     tags={"flashcard"},
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function random(Request $request): JsonResponse
    {
        try {
            $flashcardResponse = $this->service->random();
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new FlashcardTransformer())->respond();
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
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function revive(Request $request, int $id): JsonResponse
    {
        try {
            $flashcardResponse = $this->service->revive($id);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new FlashcardTransformer())->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/{id}",
     *     description="Attempt to answer the question and be judged accordingly",
     *     summary="Pass an answer or set of answers to the question",
     *     tags={"flashcard"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="Application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="answers",
     *                     type="array",
     *                     @OA\Items(type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function answer(Request $request, Flashcard $flashcard): JsonResponse
    {
        // TODO: rework this chunky boy
        if ($request->user()->cannot('answer', $flashcard)) {
            return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
        }

        $scorecard = new Scorecard($flashcard);
        $this->service->setFlashcard($flashcard);

        if (in_array($flashcard->type, [QuestionType::SINGLE, QuestionType::MULTIPLE])) {
            $filteredAnswers = $this->service->filterValidAnswers($request->input('answers'));

            try {
                $scorecard->setAnswerGiven(
                    $this->service->validateAnswers($filteredAnswers)
                );
            } catch (AnswerMismatchException $e) {
                return response()->json([
                    'title' => 'Answer mismatch',
                    'message' => $e->getMessage(),
                    'code' => 'answer_mismatch'
                ]);
            }

            $scorecard->setCorrectness($this->service->calculateCorrectness($filteredAnswers));
        } elseif ($flashcard->type === QuestionType::STATEMENT) {
            $providedAnswer = last($request->input('answers'));
            $scorecard->setAnswerGiven([$providedAnswer]);
            $scorecard->setCorrectness($this->service->calculateCorrectness(null, $providedAnswer));
        }

        $score = (new Score())->getScore($flashcard->type, $scorecard->getCorrectness(), $flashcard->difficulty);

        if ($scorecard->getCorrectness() !== Correctness::COMPLETE) {
            $this->service->resetDifficulty();
        } else {
            $request->user()->adjustPoints($score);
            $this->service->increaseDifficulty();
        }

        $this->service->resetLastSeen();
        $this->service->save();
        $scorecard->setNewDifficulty($flashcard->difficulty);

        $scorecard->setEligibleAt($flashcard->eligible_at);
        $scorecard->setScore($score);
        $scorecard->setTotalScore($request->user()->points);

        return fractal($scorecard, new ScorecardTransformer())->respond();
    }
}
