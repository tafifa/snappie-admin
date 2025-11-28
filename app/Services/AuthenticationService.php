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
    private const ACCESS_TOKEN_NAME = 'api-token';
    private const REFRESH_TOKEN_NAME = 'refresh-token';
    private const ACCESS_TOKEN_TTL_HOURS = 2;
    private const REFRESH_TOKEN_TTL_DAYS = 30;

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
            ->where('name', self::ACCESS_TOKEN_NAME)
            ->where('expires_at', '>', now())
            ->count();

        $forceLogout = (bool) ($payload['force_logout_other_devices'] ?? false);

        if ($existingTokens > 0 && !$forceLogout) {
            return [
                'status' => 409,
                'error' => 'There is already an active session. Please logout from other device first or use force_logout_other_devices=true',
                'has_active_session' => true,
            ];
        }

        $plainAccess = Str::random(80);
        $hashedAccess = hash('sha256', $plainAccess);
        $accessExpires = now()->addHours(self::ACCESS_TOKEN_TTL_HOURS);

        $plainRefresh = Str::random(80);
        $hashedRefresh = hash('sha256', $plainRefresh);
        $refreshExpires = now()->addDays(self::REFRESH_TOKEN_TTL_DAYS);

        DB::transaction(function () use ($user, $hashedAccess, $accessExpires, $hashedRefresh, $refreshExpires) {
            PersonalAccessTokens::where('tokenable_type', User::class)
                ->where('tokenable_id', $user->id)
                ->delete();

            PersonalAccessTokens::create([
                'tokenable_type' => User::class,
                'tokenable_id' => $user->id,
                'name' => self::ACCESS_TOKEN_NAME,
                'token' => $hashedAccess,
                'abilities' => ['*'],
                'expires_at' => $accessExpires,
            ]);

            PersonalAccessTokens::create([
                'tokenable_type' => User::class,
                'tokenable_id' => $user->id,
                'name' => self::REFRESH_TOKEN_NAME,
                'token' => $hashedRefresh,
                'abilities' => ['refresh'],
                'expires_at' => $refreshExpires,
            ]);
            $user->last_login_at = now();
            $user->save();
        });

        return [
            'status' => 200,
            'user' => $user,
            'token' => $plainAccess,
            'refresh_token' => $plainRefresh,
            'expires_at' => $accessExpires,
            'refresh_expires_at' => $refreshExpires,
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

    public function refreshToken(string $refreshToken): array
    {
        $hashed = hash('sha256', $refreshToken);
        $token = PersonalAccessTokens::forUsers()
            ->where('name', self::REFRESH_TOKEN_NAME)
            ->where('token', $hashed)
            ->first();

        if (!$token) {
            return [
                'status' => 401,
                'error' => 'Refresh token tidak valid.',
            ];
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();
            return [
                'status' => 401,
                'error' => 'Refresh token sudah kedaluwarsa.',
            ];
        }

        $user = User::find($token->tokenable_id);

        if (!$user) {
            $token->delete();
            return [
                'status' => 401,
                'error' => 'Pengguna tidak ditemukan untuk refresh token ini.',
            ];
        }

        $plainAccess = Str::random(80);
        $hashedAccess = hash('sha256', $plainAccess);
        $accessExpires = now()->addHours(self::ACCESS_TOKEN_TTL_HOURS);

        $plainRefresh = Str::random(80);
        $hashedRefresh = hash('sha256', $plainRefresh);
        $refreshExpires = now()->addDays(self::REFRESH_TOKEN_TTL_DAYS);

        DB::transaction(function () use ($user, $hashedAccess, $accessExpires, $token, $hashedRefresh, $refreshExpires) {
            PersonalAccessTokens::where('tokenable_type', User::class)
                ->where('tokenable_id', $user->id)
                ->where('name', self::ACCESS_TOKEN_NAME)
                ->delete();

            PersonalAccessTokens::create([
                'tokenable_type' => User::class,
                'tokenable_id' => $user->id,
                'name' => self::ACCESS_TOKEN_NAME,
                'token' => $hashedAccess,
                'abilities' => ['*'],
                'expires_at' => $accessExpires,
            ]);

            $token->token = $hashedRefresh;
            $token->expires_at = $refreshExpires;
            $token->last_used_at = now();
            $token->save();

            $user->last_login_at = now();
            $user->save();
        });

        return [
            'status' => 200,
            'user' => $user,
            'token' => $plainAccess,
            'refresh_token' => $plainRefresh,
            'expires_at' => $accessExpires,
            'refresh_expires_at' => $refreshExpires,
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
