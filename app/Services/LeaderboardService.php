<?php

namespace App\Services;

use App\Models\ExpTransaction;
use App\Models\User;
use App\Models\Leaderboard;
use Illuminate\Support\Carbon;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LeaderboardService
{
  private const CACHE_TTL = 300; // 5 minutes
  private const DEFAULT_LIMIT = 10;

  /**
   * Get top users from active leaderboard JSON data
   */
  public function getTopUsers(int $leaderboardId): Collection
  {
    $cacheKey = "leaderboard:top_users:{$leaderboardId}";

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($leaderboardId) {
      $leaderboardData = Leaderboard::where('id', $leaderboardId)->first();

      if (!$leaderboardData || !$leaderboardData->leaderboard) {
        return collect();
      }

      // Get data from JSON column and limit results
      $leaderboardData = collect($leaderboardData->leaderboard);

      return $leaderboardData;
    });
  }

  /**
   * Get user's current rank from active leaderboard
   */
  public function getUserRank(array $payload)
  {
    $userId = $payload['user_id'];
    $leaderboardId = $payload['leaderboard_id'];

    $leaderboardData = Leaderboard::find($leaderboardId);

    if (!$leaderboardData || !$leaderboardData->leaderboard) {
      throw new \Exception('Leaderboard not found');
    }

    // Search for user in leaderboard JSON data
    $leaderboardData = collect($leaderboardData->leaderboard);
    $userEntry = $leaderboardData->firstWhere('user_id', $userId);

    return $userEntry ?? null;
  }

  /**
   * Get top users this month based on EXP gained.
   *
   * @param int $limit
   * @return Collection
   */
  public function getTopUsersThisMonth(int $limit = 10): array
  {
    $leaderboard = ExpTransaction::select('user_id', DB::raw('SUM(amount) as total_exp'))
      // ->where('created_at', '>=', Carbon::now()->startOfMonth())
      ->where('created_at', '>=', Carbon::now()->startOfYear())
      ->where('created_at', '<=', Carbon::now()->endOfMonth())
      ->groupBy('user_id')
      ->orderBy('total_exp', 'desc')
      ->limit($limit)
      ->get()
      ->toArray(); // Convert to array

    // Add ranking
    $rank = 1;
    return array_map(function ($user) use (&$rank) {
      // Find user data by user_id
      $userData = User::find($user['user_id']);
      return [
        'rank' => $rank++,
        'user_id' => $user['user_id'],
        'name' => $userData ? $userData->name : null,
        'username' => $userData ? $userData->username : null,
        'image_url' => $userData ? $userData->image_url : null,
        'total_exp' => (int) $user['total_exp'],
        'total_checkin' => $userData ? $userData->total_checkin : null,
        'period' => 'monthly'
      ];
    }, $leaderboard);
  }

  public function getTopUserThisWeek(int $limit = 10): array
  {
    $leaderboard = ExpTransaction::select('user_id', DB::raw('SUM(amount) as total_exp'))
      ->where('created_at', '>=', Carbon::now()->startOfWeek())
      ->where('created_at', '<=', Carbon::now()->endOfWeek())
      ->groupBy('user_id')
      ->orderBy('total_exp', 'desc')
      ->limit($limit)
      ->get()
      ->toArray(); // Convert to array

    // Add ranking
    $rank = 1;
    return array_map(function ($user) use (&$rank) {
      // Find user data by user_id
      $userData = User::find($user['user_id']);
      return [
        'rank' => $rank++,
        'user_id' => $user['user_id'],
        'name' => $userData ? $userData->name : null,
        'username' => $userData ? $userData->username : null,
        'image_url' => $userData ? $userData->image_url : null,
        'total_exp' => (int) $user['total_exp'],
        'total_checkin' => $userData ? $userData->total_checkin : null,
        'period' => 'weekly'
      ];
    }, $leaderboard);
  }

  /**
   * Refresh leaderboard data untuk periode aktif
   * Mengupdate kolom JSON leaderboard dengan ranking terbaru
   */
  public function refreshLeaderboard(): bool
  {
    try {
      DB::beginTransaction();

      // Get active leaderboard
      $activeLeaderboard = Leaderboard::active()->first();

      if (!$activeLeaderboard) {
        // Create new leaderboard if none exists
        $activeLeaderboard = Leaderboard::create([
          'status' => true,
          'started_at' => now(),
          'ended_at' => now()->addMonth(),
          'leaderboard' => []
        ]);
      }

      // Get top users with their current ranking
      $topUsers = User::select('id', 'name', 'username', 'total_exp', 'image_url')
        ->where('total_exp', '>', 0)
        ->orderBy('total_exp', 'desc')
        ->limit(100) // Top 100 users
        ->get();

      // Build leaderboard data with ranking
      $leaderboardData = [];
      $rank = 1;

      foreach ($topUsers as $user) {
        $leaderboardData[] = [
          'rank' => $rank,
          'user_id' => $user->id,
          'name' => $user->name,
          'username' => $user->username,
          'exp' => $user->total_exp,
          'image_url' => $user->image_url,
          'updated_at' => now()->toISOString()
        ];
        $rank++;
      }

      // Update leaderboard JSON data
      $activeLeaderboard->update([
        'leaderboard' => $leaderboardData,
        'updated_at' => now()
      ]);

      // Clear related cache
      $this->clearLeaderboardCache();

      DB::commit();

      Log::info('Leaderboard refreshed successfully', [
        'total_users' => count($leaderboardData),
        'leaderboard_id' => $activeLeaderboard->id
      ]);

      return true;
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Failed to refresh leaderboard: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Clear all leaderboard related cache
   * Note: File cache driver doesn't support pattern matching, so we clear specific keys
   */
  public function clearLeaderboardCache(): void
  {
    // Clear specific cache keys (file cache doesn't support wildcards)
    // These will be regenerated on next request
    $keysToClear = [
      'leaderboard_monthly_10',
      'leaderboard:weekly:10',
    ];

    foreach ($keysToClear as $key) {
      Cache::forget($key);
    }

    // Note: For top_users and user_rank caches, they use dynamic keys with leaderboardId
    // These will expire naturally or can be cleared when leaderboard is refreshed

    Log::info('Leaderboard cache cleared');
  }
}
