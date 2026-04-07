<?php

namespace App\Http\Controllers;

use App\Enums\Difficulty;
use App\Exceptions\DraftQuestionsCannotChangeStatusException;
use App\Exceptions\FreeUserFlashcardLimitException;
use App\Exceptions\LessThanOneCorrectAnswerException;
use App\Exceptions\NoEligibleQuestionsException;
use App\Helpers\ApiResponse;
use App\Models\Flashcard;
use App\Services\FlashcardService;
use App\Transformers\QuestionTransformer;
use App\Transformers\ScorecardTransformer;
use App\Transformers\UnattemptedQuestionTransformer;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\UnauthorizedException;
use OpenApi\Annotations as OA;

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
     *     subjects={"flashcard"},
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $flashcards = $this->service->alive()->paginate(Auth::user()->page_limit);
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcards, new UnattemptedQuestionTransformer)->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/all",
     *     summary="List all flashcards",
     *     description="Return all flashcards - alive or buried - for the current user, paginated",
     *     subjects={"flashcard"},
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function all(Request $request): JsonResponse
    {
        try {
            $flashcards = $this->service->all()->paginate(Auth::user()->page_limit);
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcards, new UnattemptedQuestionTransformer)->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/{flashcard}",
     *     summary="Show a flashcard",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="flashcard", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function show(Request $request, Flashcard $flashcard): JsonResponse
    {
        try {
            $flashcardResponse = $this->service->show($flashcard);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new QuestionTransformer)->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards",
     *     summary="Create flashcard",
     *     subjects={"flashcard"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"text"},
     *
     *             @OA\Property(property="text", type="string", example="What colour is the sky?"),
     *             @OA\Property(property="is_true", type="boolean"),
     *             @OA\Property(property="explanation", type="string"),
     *             @OA\Property(property="subjects", type="array", @OA\Items(ref="#/components/schemas/Tag"))
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="400", description="Free account limitation reached"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     @OA\Response(response="422", description="Validation error"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function storeStatement(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|max:1024',
            'is_true' => 'required|boolean',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        try {
            $flashcardResponse = $this->service->store($request->all());
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        } catch (FreeUserFlashcardLimitException $e) {
            return ApiResponse::error(
                'Free user account limitation',
                $e->getMessage(),
                'free_account_limit'
            );
        } catch (LessThanOneCorrectAnswerException $e) {
            return ApiResponse::error(
                'Less than one correct answer',
                $e->getMessage(),
                'less_than_one_correct_answer',
                422
            );
        }

        return fractal($flashcardResponse, new QuestionTransformer)->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards",
     *     summary="Create flashcard",
     *     subjects={"flashcard"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"text"},
     *
     *             @OA\Property(property="text", type="string", example="What colour is the sky?"),
     *             @OA\Property(property="explanation", type="string"),
     *             @OA\Property(property="answers", type="array", @OA\Items(ref="#/components/schemas/Answer")),
     *             @OA\Property(property="subjects", type="array", @OA\Items(ref="#/components/schemas/Tag"))
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="400", description="Free account limitation reached"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     @OA\Response(response="422", description="Validation error"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function storeMultipleChoice(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|max:1024',
            'explanation' => 'nullable|max:1024',
            'answers' => 'required|array',
            'answers.*.text' => 'required|max:1024',
            'answers.*.is_correct' => 'required|boolean',
            'answers.*.explanation' => 'nullable|max:1024',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        try {
            $flashcardResponse = $this->service->store($request->all());
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        } catch (FreeUserFlashcardLimitException $e) {
            return ApiResponse::error(
                'Free user account limitation',
                $e->getMessage(),
                'free_account_limit'
            );
        } catch (LessThanOneCorrectAnswerException $e) {
            return ApiResponse::error(
                'Less than one correct answer',
                $e->getMessage(),
                'less_than_one_correct_answer',
                422
            );
        }

        return fractal($flashcardResponse, new QuestionTransformer)->respond();
    }

    /**
     * @OA\Patch(
     *     path="/api/flashcards/{flashcard}",
     *     summary="Update flashcard",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="flashcard", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="text", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function update(Request $request, Flashcard $flashcard): JsonResponse
    {
        $request->validate([
            'text' => 'string|max:1024',
            'explanation' => 'string|max:1024',
            'is_true' => 'boolean',
        ]);

        try {
            $flashcardResponse = $this->service->update($request->all(), $flashcard);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new QuestionTransformer)->respond();
    }

    /**
     * @OA\Delete(
     *     path="/api/flashcards/{flashcard}",
     *     summary="Delete flashcard",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="flashcard", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response="204", description="No content"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function destroy(Request $request, Flashcard $flashcard): Response|JsonResponse
    {
        try {
            $this->service->destroy($flashcard);
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
     *     subjects={"flashcard"},
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function graveyard(Request $request): JsonResponse
    {
        try {
            $flashcards = $this->service->buried()->paginate(Auth::user()->page_limit);
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcards, new UnattemptedQuestionTransformer)->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/random",
     *     description="Gets a random flashcard, regardless of difficulty or subjects",
     *     summary="Get a random flashcard",
     *     subjects={"flashcard"},
     *
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
        } catch (NoEligibleQuestionsException $e) {
            return response()->json([
                'data' => [
                    'title' => 'No eligible questions',
                    'message' => $e->getMessage(),
                    'code' => 'nothing_eligible',
                    'next_eligible_at' => $e->getEligibleAt()
                        ? $e->getEligibleAt()->diffForHumans()
                        : null,
                ],
            ]);
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new UnattemptedQuestionTransformer)
            ->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/subjects",
     *     description="Get flashcards that have these subjects assigned. Additive filter."
     *     summary="Get flashcards by subjects",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="subjects", in="query", @OA\Schema(type="array", @OA\Items(type="string"))),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function bySubjects(Request $request): JsonResponse
    {
        $request->validate(['subjects' => 'required|array']);

        $tags = $request->input('subjects');

        $flashcards = $this->service->subjects($tags);
        $selected = $this->service->getRandom($flashcards);

        return fractal($selected, new UnattemptedQuestionTransformer)
            ->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/{flashcard}/revive",
     *     description="Revive a flashcard from the graveyard back to the easy difficulty.",
     *     summary="Resurrect a buried flashcard",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="flashcard", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function revive(Request $request, Flashcard $flashcard): JsonResponse
    {
        try {
            $flashcardResponse = $this->service->revive($flashcard);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new UnattemptedQuestionTransformer)->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/revive",
     *     description="Revive all the flashcards of a specific difficulty, resetting them to easy. Will ignore operations if told to revive easy -> easy.",
     *     summary="Reset a difficulty level of flashcards  back to easy",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="difficulty", in="query", @OA\Schema(type="string", enum={"easy", "medium", "hard", "buried"})),
     *
     *     @OA\Response(response="204", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     @OA\Response(response="422", description="Invalid difficulty level"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function reviveDifficulty(Request $request)
    {
        $request->validate(['difficulty' => ['required', Rule::enum(Difficulty::class)]]);

        try {
            $this->service->reviveDifficulty(Difficulty::tryFrom($request->input('difficulty')));
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/{flashcard}/hide",
     *     description="Hide a flashcard from the pool, even if it's eligible",
     *     summary="Stop a flashcard from showing up when drawing a random question to answer",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="flashcard", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="400", description="Cannot change status"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function hide(Request $request, Flashcard $flashcard): JsonResponse
    {
        try {
            $flashcardResponse = $this->service->hide($flashcard);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (DraftQuestionsCannotChangeStatusException $e) {
            return ApiResponse::error(
                'Cannot change status',
                $e->getMessage(),
                'draft_status_cannot_change'
            );
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new UnattemptedQuestionTransformer)->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/{flashcard}/unhide",
     *     description="Re-enable a flashcard for eligibility to be drawn from the pool",
     *     summary="Allow the flashcard to be drawn again when drawing a random question to answer",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="flashcard", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="400", description="Cannot change status"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function unhide(Request $request, Flashcard $flashcard): JsonResponse
    {
        try {
            $flashcardResponse = $this->service->unhide($flashcard);
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (DraftQuestionsCannotChangeStatusException $e) {
            return ApiResponse::error(
                'Cannot change status',
                $e->getMessage(),
                'draft_status_cannot_change'
            );
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcardResponse, new UnattemptedQuestionTransformer)->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/{flashcard}",
     *     description="Attempt to answer the question and be judged accordingly",
     *     summary="Pass an answer or set of answers to the question",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="flashcard", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="answers", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Model not found"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function answer(Request $request, $flashcardId): JsonResponse
    {
        try {
            // Simple ownership check without Gates/Policies
            $flashcard = Flashcard::find($flashcardId);

            if (! $flashcard) {
                return $this->notFoundResponse('Flashcard not found');
            }

            if ($flashcard->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('You can only answer your own flashcards');
            }

            $scorecardResponse = $this->service->answer($flashcard, $request->input('answers'), $request->user());

            return fractal($scorecardResponse, new ScorecardTransformer)->respond();

        } catch (ModelNotFoundException $e) {
            return $this->handleApiError($e, 'Model not found');
        } catch (UnauthorizedException $e) {
            return $this->handleApiError($e, 'Unauthorized');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/drafts",
     *     summary="Get all flashcards that are missing some data that stops them from being published",
     *     subjects={"flashcard"},
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function draft(Request $request): JsonResponse
    {
        try {
            $flashcards = $this->service->draft()->paginate(Auth::user()->page_limit);
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcards, new UnattemptedQuestionTransformer)->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/hidden",
     *     summary="Get all flashcards that have been hidden by the user",
     *     subjects={"flashcard"},
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function hidden(Request $request): JsonResponse
    {
        try {
            $flashcards = $this->service->hidden()->paginate(Auth::user()->page_limit);
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($flashcards, new UnattemptedQuestionTransformer)->respond();
    }

    /**
     * @OA\Post(
     *     path="/api/flashcards/import",
     *     summary="Import flashcards",
     *     subjects={"flashcard"},
     *
     *     @OA\Parameter(name="topic", in="query", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Not authenticated"),
     *     @OA\Response(response="404", description="Import file not found"),
     *     @OA\Response(response="422", description="Validation error"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function import(Request $request): JsonResponse
    {
        if ($request->input('topic') === null) {
            return ApiResponse::error(
                'Validation error',
                'Topic parameter is required',
                'validation_error',
                422
            );
        }

        try {
            $importCount = $this->service->import($request->input('topic'));
        } catch (FileNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return response()->json([
            'data' => [
                'count' => $request->user()->total_questions,
                'imported' => $importCount,
                'remaining' => $request->user()->roles()->where('code', 'advanced_user')->exists()
                    ? null
                    : config('flashcard.free_account_limit') - $request->user()->flashcards()->count(),
            ],
        ], 200);
    }
}
