<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProfileUpdateRequest;
use App\Services\UsersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController
{
    public function __construct(private UsersService $service) {}
    public function show(int $user_id): JsonResponse
    {
        $user = $this->service->getById($user_id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'image_url' => $user->image_url,
                'total_coin' => $user->total_coin,
                'total_exp' => $user->total_exp,
                'total_following' => $user->total_following,
                'total_follower' => $user->total_follower,
                'total_checkin' => $user->total_checkin,
                'total_post' => $user->total_post,
                'total_article' => $user->total_article,
                'total_review' => $user->total_review,
                'total_achievement' => $user->total_achievement,
                'total_challenge' => $user->total_challenge,
                'status' => $user->status,
                'last_login_at' => optional($user->last_login_at)->toIso8601String(),
                'additional_info' => $user->additional_info,
                'created_at' => optional($user->created_at)->toIso8601String(),
                'updated_at' => optional($user->updated_at)->toIso8601String(),
            ],
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
        $summary = $this->service->getProfileSummary($user->id);
        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diambil',
            'data' => $summary,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
        
        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'gender' => ['sometimes', 'string'],
            'image_url' => ['sometimes', 'url'],
            'phone' => ['sometimes', 'string'],
            'date_of_birth' => ['sometimes', 'date', 'before:today'],
            'bio' => ['sometimes', 'string'],
            'privacy_settings' => ['sometimes', 'array'],
            'privacy_settings.profile_visibility' => ['required_with:privacy_settings', 'string'],
            'privacy_settings.location_sharing' => ['required_with:privacy_settings', 'boolean'],
            'notification_preferences' => ['sometimes', 'array'],
            'notification_preferences.email_notifications' => ['required_with:notification_preferences', 'boolean'],
            'notification_preferences.push_notifications' => ['required_with:notification_preferences', 'boolean'],
        ]);
        $updated = $this->service->updateProfile($user->id, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui!',
            'data' => $updated?->toArray(),
        ]);
    }

    public function activities(int $user_id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Activities retrieved successfully',
            'data' => $this->service->getActivities($user_id),
        ]);
    }

    public function stats(int $user_id): JsonResponse
    {
        $stats = $this->service->getStats($user_id);
        if (!$stats) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Stats retrieved successfully',
            'data' => $stats,
        ]);
    }
}
