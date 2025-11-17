<?php

namespace App\Services;

use App\Http\Requests\Api\V2\LoginRequest;
use App\Http\Requests\Api\V2\RegisterRequest;
use App\Models\PersonalAccessTokens;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthenticationService
{
    public function registerUser(array $payload): array
    {
        $additional = [
            'user_detail' => [
                'gender' => $payload['gender'] ?? null,
            ],
            'user_preferences' => [
                'food_type' => $payload['food_type'] ?? [],
                'place_value' => $payload['place_value'] ?? [],
            ],
        ];

        $user = User::create([
            'email' => $payload['email'],
            'name' => $payload['name'],
            'username' => $payload['username'],
            'image_url' => $payload['image_url'] ?? null,
            'status' => true,
            'additional_info' => $additional,
        ]);

        return [
            'user' => $user,
        ];
    }

    public function loginUser(array $payload): array
    {
        $user = User::where('email', $payload['email'])
            ->orWhere('username', $payload['email'])
            ->first();

        if (!$user) {
            return [
                'status' => 401,
                'error' => 'Email/username tidak valid.',
            ];
        }

        $existingTokens = PersonalAccessTokens::where('tokenable_type', User::class)
            ->where('tokenable_id', $user->id)
            ->where('expires_at', '>', now())
            ->count();

        if ($existingTokens > 0) {
            return [
                'status' => 409,
                'error' => 'There is already an active session. Please logout from other device first or use force_logout_other_devices=true',
                'has_active_session' => true,
            ];
        }

        $plain = Str::random(80);
        $hashed = hash('sha256', $plain);
        $expires = now()->addHours(2);

        DB::transaction(function () use ($user, $hashed, $expires) {
            PersonalAccessTokens::where('tokenable_type', User::class)
                ->where('tokenable_id', $user->id)
                ->delete();

            PersonalAccessTokens::create([
                'tokenable_type' => User::class,
                'tokenable_id' => $user->id,
                'name' => 'api-token',
                'token' => $hashed,
                'abilities' => ['*'],
                'expires_at' => $expires,
            ]);
            $user->last_login_at = now();
            $user->save();
        });

        return [
            'status' => 200,
            'user' => $user,
            'token' => $plain,
            'expires_at' => $expires,
        ];
    }

    public function logoutUser(string $bearer): array
    {
        if (!$bearer) {
            return [
                'status' => 500,
                'error' => 'Logout gagal!',
            ];
        }

        $hashed = hash('sha256', $bearer);
        PersonalAccessTokens::where('token', $hashed)->delete();

        return [
            'status' => 200,
            'logged_out_at' => now()->toIso8601String(),
        ];
    }

    public function getActiveSessions(int $userId): array
    {
        $tokens = PersonalAccessTokens::forUsers()
            ->where('tokenable_id', $userId)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'last_used_at', 'expires_at', 'created_at']);
        return $tokens->map(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'last_used_at' => optional($t->last_used_at)->toIso8601String(),
                'expires_at' => optional($t->expires_at)->toIso8601String(),
                'created_at' => optional($t->created_at)->toIso8601String(),
            ];
        })->all();
    }

    public function validateToken(string $bearer): array
    {
        $hashed = hash('sha256', $bearer);
        $token = PersonalAccessTokens::forUsers()->where('token', $hashed)->first();
        if (!$token) {
            return ['valid' => false];
        }
        $expired = $token->expires_at && $token->expires_at->isPast();
        return [
            'valid' => !$expired,
            'expires_at' => optional($token->expires_at)->toIso8601String(),
        ];
    }

    public function me(string $bearer): ?array
    {
        $hashed = hash('sha256', $bearer);
        $token = PersonalAccessTokens::forUsers()->where('token', $hashed)->first();
        if (!$token) {
            return null;
        }
        $user = User::find($token->tokenable_id);
        if (!$user) {
            return null;
        }
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'image_url' => $user->image_url,
            'last_login_at' => optional($user->last_login_at)->toIso8601String(),
        ];
    }

    public function findUserByCredential(string $emailOrUsername): ?User
    {
        return User::where('email', $emailOrUsername)
            ->orWhere('username', $emailOrUsername)
            ->first();
    }
}
