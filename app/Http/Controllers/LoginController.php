<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user and generate JWT token",
     *     tags={"auth"},
     *     security={{"basicAuth":{}}},
     *     @OA\Response(response="200", description="Login successful"),
     *     @OA\Response(response="401", description="Invalid credentials")
     * )
     */
    public function login(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if ($authHeader) {
            $authString = explode(' ', $authHeader);
            $credentials = base64_decode($authString[1]);
            list($email, $password) = explode(':', $credentials);
            $credentials = [
                'username' => $email,
                'password' => $password,
            ];

            if (Auth::attempt($credentials)) {
                $token = Auth::user()->createToken('api_token')->plainTextToken;

                return response()->json([
                    'display_name' => Auth::user()->display_name,
                    'is_trial_user' => Auth::user()->is_trial_user,
                    'token' => $token
                ]);
            }
        }

        return response()->json([
            'title' => 'Invalid credentials',
            'message' => 'Username and/or password incorrect',
            'code' => 'invalid_credentials'
        ]);
    }
}
