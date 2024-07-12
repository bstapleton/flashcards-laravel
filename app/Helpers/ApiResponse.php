<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Creates a standardised response for errors
     *
     * @param string $title
     * @param string $message
     * @param string $code
     * @param int $status
     * @return JsonResponse
     */
    public static function error(string $title, string $message, string $code = 'undefined_error', int $status = 400): JsonResponse
    {
        return response()->json([
            'data' => [
                'title' => $title,
                'message' => $message,
                'code' => $code
            ]
        ], $status);
    }
}
