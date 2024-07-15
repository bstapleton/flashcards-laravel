<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class CheckAuthed extends EnsureFrontendRequestsAreStateful
{
    public function handle($request, $next): Response
    {
        $response = parent::handle($request, $next);

        if ($response->getStatusCode() === 401) {
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
