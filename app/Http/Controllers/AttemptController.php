<?php

namespace App\Http\Controllers;

use App\Services\AttemptService;
use App\Transformers\AttemptTransformer;
use Illuminate\Http\Request;
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
     * )
     */
    public function index()
    {
        try {
            $attempts = $this->service->all();
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($attempts, new AttemptTransformer())->respond();
    }

    /**
     * @OA\Get (
     *     path="/api/attempt/{id}",
     *     summary="Show an attempt",
     *     description="Return an attempt",
     *     tags={"attempt"},
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
    public function show(Request $request, int $id)
    {
        try {
            $attempt = $this->service->show($id);
        } catch (UnauthorizedException) {
            return $this->handleForbidden();
        }

        return fractal($attempt, new AttemptTransformer())->respond();
    }

}
