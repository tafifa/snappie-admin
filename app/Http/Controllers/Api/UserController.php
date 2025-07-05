<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Get user profile with stats
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get user statistics
            $totalCheckins = $user->checkins()->count();
            $totalReviews = $user->reviews()->count();
            $placesVisited = $user->checkins()->distinct('place_id')->count();

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'image_url' => $user->image_url,
                    'total_coin' => $user->total_coin,
                    'total_exp' => $user->total_exp,
                    'level' => $user->level,
                    'exp_to_next_level' => $user->exp_to_next_level,
                    'status' => $user->status,
                    'last_login_at' => $user->last_login_at?->toISOString(),
                    'statistics' => [
                        'total_checkins' => $totalCheckins,
                        'total_reviews' => $totalReviews,
                        'places_visited' => $placesVisited,
                    ],
                    'additional_info' => $user->additional_info,
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|min:2|max:255',
                'username' => 'sometimes|string|min:3|max:20|unique:users,username,' . $user->id . '|regex:/^[a-zA-Z0-9_]+$/',
                'additional_info' => 'sometimes|array',
                'additional_info.bio' => 'sometimes|string|max:500',
                'additional_info.phone' => 'sometimes|string|max:20',
                'additional_info.location' => 'sometimes|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only(['name', 'username']);
            
            if ($request->has('additional_info')) {
                $additionalInfo = $user->additional_info ?? [];
                $updateData['additional_info'] = array_merge($additionalInfo, $request->additional_info);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'image_url' => $user->image_url,
                    'total_coin' => $user->total_coin,
                    'total_exp' => $user->total_exp,
                    'level' => $user->level,
                    'exp_to_next_level' => $user->exp_to_next_level,
                    'additional_info' => $user->additional_info,
                    'updated_at' => $user->updated_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            
            // Delete old avatar if exists
            if ($user->image_url) {
                $oldPath = str_replace('/storage/', '', $user->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new avatar
            $file = $request->file('avatar');
            $filename = 'avatars/' . $user->id . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public', $filename);
            
            $imageUrl = '/storage/' . $filename;
            
            // Update user
            $user->update(['image_url' => $imageUrl]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'data' => [
                    'image_url' => $imageUrl,
                    'updated_at' => $user->updated_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
