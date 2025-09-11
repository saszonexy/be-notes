<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success'      => true,
            'message'      => 'User registered successfully',
            'user'         => $user,
            'token'        => $token,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60
        ], 201);
    }

    public function login(Request $request)
    {
        Log::info('Login attempt', ['email' => $request->email]);

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                Log::info('Login failed - invalid credentials', ['email' => $request->email]);
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid credentials'
                ], 401);
            }
        } catch (JWTException $e) {
            Log::error('JWT Exception during login', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Could not create token'
            ], 500);
        }

        $user = JWTAuth::user();

        $response = [
            'success'      => true,
            'message'      => 'Login successful',
            'token'        => $token,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60,
            'user'         => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ]
        ];

        Log::info('Login successful', ['user_id' => $user->id, 'email' => $user->email]);

        return response()->json($response, 200);
    }

    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => $user
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Token is invalid'
            ], 401);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to logout'
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh();
            return response()->json([
                'success' => true,
                'token' => $token,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Could not refresh token'
            ], 401);
        }
    }
}
