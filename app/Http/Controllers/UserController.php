<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @property int id
 * @property string username
 * @property string display_name
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password", "password_confirmation", "display_name"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="password", type="password"),
     *             @OA\Property(property="password_confirmation", type="password"),
     *             @OA\Property(property="display_name", type="string"),
     *         )
     *     ),
     *     @OA\Response(response="201", description="User registered successfully"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'username' => 'required|string|unique:users|max:255',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
            'display_name' => 'required|string|max:255',
        ]);

        User::create([
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
            'display_name' => $validatedData['display_name'],
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/user/{user}",
     *     summary="Get logged-in user details",
     *     tags={"auth"},
     *     @OA\Parameter(name="user", in="path", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('Not found', 'User not found', 'not_found', 404);
        }

        return fractal($user, new UserTransformer())->respond();
    }
}
