<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class AuthService
{
  /**
   * Register a new user with comprehensive validation and setup
   *
   * @param array $data User registration data
   * @return array
   * @throws ValidationException
   */
  public function register(array $data): array
  {
    DB::beginTransaction();

    try {
      // Check if email already exists
      if (User::where('email', $data['email'])->exists()) {
        throw ValidationException::withMessages([
          'email' => ['Email sudah terdaftar.']
        ]);
      }

      // Check if username already exists (if provided)
      if (isset($data['username']) && User::where('username', $data['username'])->exists()) {
        throw ValidationException::withMessages([
          'username' => ['Username sudah digunakan.']
        ]);
      }

      // Create new user with default values
      $user = User::create([
        'name' => $data['name'],
        'username' => $data['username'],
        'email' => $data['email'],
        'image_url' => $data['image_url'],
        'total_coin' => 100, // Welcome bonus
        'total_exp' => 0,
        'total_following' => 0,
        'total_follower' => 0,
        'total_checkin' => 0,
        'total_post' => 0,
        'total_article' => 0,
        'total_review' => 0,
        'total_achievement' => 0,
        'total_challenge' => 0,
        'status' => true,
        'additional_info' => [
          'user_detail' => [
            'bio' => $data['bio'] ?? null,
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'phone' => $data['phone'] ?? null,
          ],
          'user_preferences' => [
            'food_type' => $data['food_type'],
            'place_value' => $data['place_value'],
          ],
          'user_saved' => [
            'saved_places' => [],
            'saved_posts' => [],
            'saved_articles' => []
          ],
          'user_settings' => [
            'language' => 'id',
            'theme' => 'light'
          ],
          'user_notification' => [
            'push_notification' => true
          ]
        ]
      ]);

      // Log successful registration
      Log::info('User registered successfully', [
        'user_id' => $user->id,
        'email' => $user->email,
        'username' => $user->username
      ]);

      DB::commit();

      return [
        'user' => $user->fresh(),
        'message' => 'Registrasi berhasil! Selamat datang di Snappie!'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Registration failed', [
        'error' => $e->getMessage(),
        'email' => $data['email'] ?? null
      ]);
      throw $e;
    }
  }

  /**
   * Login user with enhanced security and tracking
   *
   * @param array $credentials Login credentials
   * @param bool $remember Remember login session
   * @return array
   * @throws ValidationException
   */
  public function login(array $credentials, bool $remember = false): array
  {
    try {
      // Find user by email or username
      $user = User::where(function ($query) use ($credentials) {
        $query->where('email', $credentials['email'])
          ->orWhere('username', $credentials['email']);
      })->first();

      // Validate credentials
      if (!$user) {
        throw ValidationException::withMessages([
          'email' => ['Email/username tidak valid.']
        ]);
      }

      // Check if user is active
      if (!$user->status) {
        throw ValidationException::withMessages([
          'email' => ['Akun Anda telah dinonaktifkan. Silakan hubungi admin.']
        ]);
      }

      // Check if user already has active token (prevent multiple login)
      $hasActiveToken = $user->tokens()
        ->where('expires_at', '>', now())
        ->exists();

      if ($hasActiveToken) {
        throw ValidationException::withMessages([
          'email' => ['Anda sudah login di perangkat lain. Silakan logout terlebih dahulu atau tunggu token expired.']
        ]);
      }

      // Update login statistics
      $user->update(['last_login_at' => now()]);

      // Create token with appropriate expiration
      $expiresAt = $remember ? now()->addDays(30) : now()->addHours(24);
      $token = $user->createToken('auth-token', ['*'], $expiresAt)->plainTextToken;

      // Log successful login
      Log::info('User logged in successfully', [
        'user_id' => $user->id,
        'email' => $user->email,
        'remember' => $remember
      ]);

      return [
        'user' => $user->fresh(),
        'token' => $token,
        'token_type' => 'Bearer',
        'expires_at' => $expiresAt->toISOString(),
        'message' => 'Login berhasil!'
      ];
    } catch (ValidationException $e) {
      // Log failed login attempt
      Log::warning('Failed login attempt', [
        'email' => $credentials['email'] ?? null,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent()
      ]);
      throw $e;
    }
  }

  /**
   * Logout user by revoking current token
   *
   * @param User $user
   * @return bool
   */
  public function logout(User $user): bool
  {
    try {
      // Revoke current access token
      $user->tokens()->delete();

      // Log successful logout
      Log::info('User logged out successfully', [
        'user_id' => $user->id,
        'email' => $user->email
      ]);

      return true;
    } catch (\Exception $e) {
      Log::error('Logout failed', [
        'user_id' => $user->id,
        'error' => $e->getMessage()
      ]);
      return false;
    }
  }

  /**
   * Get comprehensive user profile with statistics
   *
   * @param User $user
   * @return array
   */ 
  public function getUserProfile(User $user): array
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
        'engagement' => [
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
}
