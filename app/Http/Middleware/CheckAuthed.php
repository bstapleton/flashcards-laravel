<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class CheckAuthed extends EnsureFrontendRequestsAreStateful
{
    public function handle($request, $next): Response
    {
        $response = parent::handle($request, $next);

        $apiKey = $request->header('X-API-KEY');

        if (!$apiKey || $apiKey !== env('API_KEY')) {
            return response()->json([
                'data' => [
                    'title' => 'Unauthorized',
                    'message' => 'Missing or invalid API key',
                    'code' => 'invalid_key'
                ]
            ], 401);
        }

        if (!$request->bearerToken()) {
            return response()->json([
                'data' => [
                    'title' => 'Unauthenticated',
                    'message' => 'You need to be logged in to use this',
                    'code' => 'unauthenticated'
                ]
            ], 401);
        }

        return $response;
    }
}
