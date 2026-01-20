<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\LoginRequest;
use App\Http\Requests\Api\V2\RegisterRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticationController
{
    public function __construct(private AuthenticationService $service) {}
    public function register(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female,other'],
            'image_url' => ['required', 'url', 'max:500'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'food_type' => ['required', 'array'],
            'food_type.*' => ['string', 'distinct'],
            'place_value' => ['required', 'array'],
            'place_value.*' => ['string', 'distinct'],
        ]);
        $result = $this->service->registerUser($payload);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'id' => $result['user']->id,
                'email' => $result['user']->email,
                'name' => $result['user']->name,
                'username' => $result['user']->username,
                'image_url' => $result['user']->image_url,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
            'force_logout_other_devices' => ['sometimes', 'boolean'],
        ]);
        $result = $this->service->loginUser($payload);

        if (($result['status'] ?? 200) === 401) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Unauthorized',
            ], 401);
        }

        if (($result['status'] ?? 200) === 409) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Conflict',
                'data' => [
                    'has_active_session' => $result['has_active_session'] ?? false,
                ]
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'data' => [
                'user' => [
                'id' => $result['user']->id,
                    'email' => $result['user']->email,
                    'name' => $result['user']->name,
                    'username' => $result['user']->username,
                    'image_url' => $result['user']->image_url,
                ],
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'expires_at' => $result['expires_at']->toIso8601String(),
                'refresh_token' => $result['refresh_token'],
                'refresh_token_expires_at' => $result['refresh_expires_at']->toIso8601String(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $bearer = $request->bearerToken();
        $result = $this->service->logoutUser($bearer ?? '');

        if (($result['status'] ?? 200) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Logout gagal!',
            ], $result['status']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil!',
            'logged_out_at' => $result['logged_out_at'],
        ]);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);
        $result = $this->service->refreshToken($payload['refresh_token']);

        if (($result['status'] ?? 200) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Token refresh gagal!',
            ], $result['status']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'expires_at' => $result['expires_at']->toIso8601String(),
                'refresh_token' => $result['refresh_token'],
                'refresh_token_expires_at' => $result['refresh_expires_at']->toIso8601String(),
            ],
        ]);
    }
}
