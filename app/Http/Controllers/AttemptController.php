<?php

namespace App\Http\Controllers;

use App\Services\AttemptService;
use App\Transformers\AttemptTransformer;
use Illuminate\Validation\UnauthorizedException;

class AttemptController extends Controller
{
    protected AttemptService $service;

    public function __construct(AttemptService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get (
     *     path="/api/attempt",
     *     summary="List attempts",
     *     description="Return all attempts for the current user, paginated",
     *     tags={"attempt"},
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="403", description="Not permitted"),
     *     security={{"bearerAuth":{}}}
     )
     */
    public function index()
    {
        try {
            $attempts = $this->service->all()->paginate(25);
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($attempts, new AttemptTransformer())->respond();
    }

    public function show()
    {
        // TODO: This should show a history of all attempts _for that question_
    }

}
