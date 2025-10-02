<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserService
{
  /**
   * Get the user profile.
   *
   * @param  int  $userId
   * @return User
   */
  public function getById(int $userId): User
  {
    $user = User::findOrFail($userId);
    if (!$user) {
      throw new \Exception('User not found');
    }
    return $user;
  }

  /**
   * Get comprehensive user profile with statistics
   *
   * @param User $user
   * @return array
   */
  public function getProfile(User $user): array
  {
    try {
      // Load user with relationships
      $userWithRelations = $user->load([
        'achievements' => function ($query) {
          $query->wherePivot('status', true)->latest('pivot_created_at');
        },
        'rewards' => function ($query) {
          $query->wherePivot('status', true)->latest('pivot_created_at');
        },
        'challenges' => function ($query) {
          $query->latest('pivot_created_at');
        }
      ]);

      // Calculate comprehensive statistics
      $stats = [
        'activity' => [
          'total_checkins' => $user->total_checkin,
          'total_reviews' => $user->total_review,
          'total_posts' => $user->total_post,
          'total_articles' => $user->total_article,
        ],
        'social' => [
          'total_followers' => $user->total_follower,
          'total_following' => $user->total_following,
        ],
        'gamification' => [
          'total_coins' => $user->total_coin,
          'total_exp' => $user->total_exp,
          'total_achievements' => $user->total_achievement,
          'total_challenges' => $user->total_challenge,
        ],
        'additional information' => [
          'join_date' => $user->created_at->toDateString(),
          'last_login' => $user->last_login_at?->toDateTimeString(),
          'days_active' => $user->created_at->diffInDays(now()),
        ]
      ];

      return [
        'user' => $userWithRelations,
        'stats' => $stats,
        'preferences' => $user->additional_info['user_preferences'] ?? [],
        'settings' => $user->additional_info['user_settings'] ?? []
      ];
    } catch (\Exception $e) {
      Log::error('Failed to get user profile', [
        'user_id' => $user->id,
        'error' => $e->getMessage()
      ]);
      throw $e;
    }
  }

  /**
   * Update user profile with validation
   *
   * @param User $user
   * @param array $data
   * @return array
   */
  public function updateProfile(User $user, array $data): array
  {
    DB::beginTransaction(); // Memulai transaksi database

    try {
      $additionalInfo = $user->additional_info ?? [];

      $additionalInfo['user_detail'] = [
        'bio' => Arr::get($data, 'bio', Arr::get($additionalInfo, 'user_detail.bio', '')),
        'gender' => Arr::get($data, 'gender', Arr::get($additionalInfo, 'user_detail.gender')),
        'date_of_birth' => Arr::get($data, 'date_of_birth', Arr::get($additionalInfo, 'user_detail.date_of_birth', '')),
        'phone' => Arr::get($data, 'phone', Arr::get($additionalInfo, 'user_detail.phone', '')),
      ];

      $foodTypes = Arr::wrap(Arr::get($data, 'food_type', Arr::get($additionalInfo, 'user_preferences.food_type', [])));
      $placeValues = Arr::wrap(Arr::get($data, 'place_value', Arr::get($additionalInfo, 'user_preferences.place_value', [])));

      $additionalInfo['user_preferences'] = [
        'food_type' => $foodTypes,
        'place_value' => $placeValues,
      ];

      unset($additionalInfo['food_type'], $additionalInfo['place_value']);

      $additionalInfo['user_settings'] = [
        'language' => Arr::get($data, 'language', Arr::get($additionalInfo, 'user_settings.language', 'id')),
        'theme' => Arr::get($data, 'theme', Arr::get($additionalInfo, 'user_settings.theme', 'light')),
      ];

      $additionalInfo['user_notification'] = [
        'push_notification' => Arr::get($data, 'push_notification', Arr::get($additionalInfo, 'user_notification.push_notification', true)),
      ];

      // Pastikan blok lain dipertahankan apa adanya jika belum ada
      $additionalInfo['user_saved'] = Arr::get($additionalInfo, 'user_saved', [
        'saved_places' => [],
        'saved_posts' => [],
        'saved_articles' => [],
      ]);

      // Update user profile dengan fallback ke nilai lama jika field tidak dikirim
      $user->update([
        'name' => Arr::get($data, 'name', $user->name),
        'username' => Arr::get($data, 'username', $user->username),
        'email' => Arr::get($data, 'email', $user->email),
        'image_url' => Arr::get($data, 'image_url', $user->image_url),
        'status' => Arr::get($data, 'status', $user->status),
        'additional_info' => $additionalInfo,
      ]);

      // Log successful update
      Log::info('User profile updated successfully', [
        'user_id' => $user->id,
        'updated_fields' => array_keys($data)
      ]);

      DB::commit(); // Melakukan commit transaksi jika berhasil

      return $this->getProfile($user);
    } catch (ValidationException $e) {
      DB::rollBack(); // Melakukan rollback jika terjadi ValidationException
      Log::warning('Failed to update user profile', [
        'user_id' => $user->id,
        'errors' => $e->errors()
      ]);
      throw $e;
    } catch (\Exception $e) {
      DB::rollBack(); // Melakukan rollback jika terjadi Exception lain
      Log::error('Update profile error in service', [
        'user_id' => $user->id,
        'error' => $e->getMessage()
      ]);
      throw $e;
    }
  }

}
