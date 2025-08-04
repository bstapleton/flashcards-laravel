<?php

namespace App\Http\Controllers;

use App\Exceptions\DraftQuestionsCannotChangeStatusException;
use App\Exceptions\FreeUserFlashcardLimitException;
use App\Exceptions\LessThanOneCorrectAnswerException;
use App\Exceptions\NoEligibleQuestionsException;
use App\Exceptions\UndeterminedQuestionTypeException;
use App\Helpers\ApiResponse;
use App\Helpers\Boolean;
use App\Http\Requests\SuggestionRequest;
use App\Models\Flashcard;
use App\Services\FlashcardService;
use App\Transformers\QuestionTransformer;
use App\Transformers\UnattemptedQuestionTransformer;
use App\Transformers\ScorecardTransformer;
use GeminiAPI\Resources\Parts\TextPart;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use GeminiAPI\Client as Gemini;
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
     *     tags={"flashcard"},
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
     *     tags={"flashcard"},
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
     *     tags={"flashcard"},
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
     *     tags={"flashcard"},
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
     *             @OA\Property(property="answers", type="array", @OA\Items(ref="#/components/schemas/Answer")),
     *             @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/Tag"))
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
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|max:1024',
            'is_true' => 'nullable|required_without:answers',
            'answers' => 'nullable|required_without:is_true',
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
        } catch (UndeterminedQuestionTypeException $e) {
            return ApiResponse::error(
                'Undetermined question type',
                $e->getMessage(),
                'undetermined_question_type',
                422
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
     *     tags={"flashcard"},
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
     *     tags={"flashcard"},
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
     *     tags={"flashcard"},
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
     *     description="Gets a random flashcard, regardless of difficulty or tags",
     *     summary="Get a random flashcard",
     *     tags={"flashcard"},
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
     * @OA\Post(
     *     path="/api/flashcards/{flashcard}/revive",
     *     description="Revive a flashcard from the graveyard back to the easy difficulty. This will additionally remove it's hidden status if it had one.",
     *     summary="Resurrect a buried flashcard",
     *     tags={"flashcard"},
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
     *     path="/api/flashcards/{flashcard}/hide",
     *     description="Hide a flashcard from the pool, even if it's eligible",
     *     summary="Stop a flashcard from showing up when drawing a random question to answer",
     *     tags={"flashcard"},
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
     *     tags={"flashcard"},
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
     *     tags={"flashcard"},
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
    public function answer(Request $request, Flashcard $flashcard): JsonResponse
    {
        try {
            $scorecardResponse = $this->service->answer($flashcard, $request->input('answers'), $request->user());
        } catch (ModelNotFoundException) {
            return $this->handleNotFound();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($scorecardResponse, new ScorecardTransformer)->respond();
    }

    /**
     * @OA\Get(
     *     path="/api/flashcards/drafts",
     *     summary="Get all flashcards that are missing some data that stops them from being published",
     *     tags={"flashcard"},
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
     *     tags={"flashcard"},
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
     * @OA\Get(
     *     path="/api/flashcards/suggest",
     *     summary="Suggest a question based on the type and topic",
     *     tags={"flashcard"},
     *     @OA\Parameter(name="statement", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="topic", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     @OA\Response(response="422", description="Validation error"),
     * )
     */
    public function suggest(SuggestionRequest $request): JsonResponse
    {
        $topic = $request->input('topic');
        $isStatement = (new Boolean)->handle($request->input('statement'));
        $formatting = ' but as raw text without the surrounding backticks and language denotation.';

        if ($isStatement) {
            $prompt = 'Give me one statement on the topic of ' . $topic . '. The statement can be either true or ' .
                'false. If the statement is false, include an explanation of why it is false. Return the response ' .
                'with the following JSON structure: '.
                '{"text":"statement","is_true": true,"explanation":"explanation"}' . $formatting;
        } else {
            $prompt = 'Give me one multiple choice question on the topic of ' . $topic . '. There must be at least ' .
                'two possible answers in your response, but no more than five. One or more of the answers must be ' .
                'correct. Return the response with the following JSON structure: '.
                '{"text":"question","answers":[{"text":"answer","is_correct":true}]}' . $formatting;
        }

        $client = new Gemini(env('GEMINI_API_KEY'));
        $result = $client->generativeModel('gemini-2.0-flash-001')->generateContent(
            new TextPart($prompt),
        );
        $response = new JsonResponse();
        $response->setData([
            'data' => json_decode($result->text())
        ]);

        return $response;
    }



    /**
     * @OA\Post(
     *     path="/api/flashcards/import",
     *     summary="Import flashcards",
     *     tags={"flashcard"},
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
