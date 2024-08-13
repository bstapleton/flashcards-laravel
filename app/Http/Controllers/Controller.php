<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *    title="Flashcards API",
 *    version="1.0.0",
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    protected function handleNotFound(): JsonResponse
    {
        return ApiResponse::error('Not found', 'Flashcard not found', 'not_found', 404);
    }

    protected function handleForbidden(): JsonResponse
    {
        return ApiResponse::error('Forbidden', 'You do not have permission to perform this action', 'unauthorized', 403);
    }
}
