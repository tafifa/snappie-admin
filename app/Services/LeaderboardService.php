<?php

namespace App\Services;

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
  public function getTopUsers(int $limit = 10): Collection
  {
    $cacheKey = "leaderboard:top_users:{$limit}";

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
      $activeLeaderboard = Leaderboard::active()->first();

      if (!$activeLeaderboard || !$activeLeaderboard->leaderboard) {
        return collect();
      }

      // Get data from JSON column and limit results
      $leaderboardData = collect($activeLeaderboard->leaderboard)
        ->take($limit);

      return $leaderboardData;
    });
  }

  /**
   * Get top users with pagination.
   *
   * @param int $perPage
   * @return LengthAwarePaginator
   */
  public function getTopUsersPaginated(int $perPage = self::DEFAULT_LIMIT): LengthAwarePaginator
  {
    return User::select(['id', 'name', 'total_exp'])
      ->where('total_exp', '>', 0)
      ->orderBy('total_exp', 'desc')
      ->orderBy('total_checkin', 'desc')
      ->orderBy('created_at', 'asc')
      ->paginate($perPage);
  }

  /**
   * Get top users for this week from active leaderboard
   */
  public function getTopUserThisWeek(int $limit = 10): Collection
  {
    $cacheKey = "leaderboard:weekly:{$limit}";

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
      $startOfWeek = now()->startOfWeek();

      // Get users with EXP gained this week
      $weeklyUsers = User::select('id', 'name', 'username', 'image_url')
        ->selectRaw('COALESCE(SUM(exp_transactions.exp), 0) as weekly_exp')
        ->leftJoin('exp_transactions', function ($join) use ($startOfWeek) {
          $join->on('users.id', '=', 'exp_transactions.user_id')
            ->where('exp_transactions.created_at', '>=', $startOfWeek);
        })
        ->groupBy('users.id', 'users.name', 'users.username', 'users.image_url')
        ->having('weekly_exp', '>', 0)
        ->orderBy('weekly_exp', 'desc')
        ->limit($limit)
        ->get();

      // Add ranking
      $rank = 1;
      return $weeklyUsers->map(function ($user) use (&$rank) {
        return [
          'rank' => $rank++,
          'user_id' => $user->id,
          'name' => $user->name,
          'username' => $user->username,
          'exp' => $user->weekly_exp,
          'image_url' => $user->image_url,
          'period' => 'weekly'
        ];
      });
    });
  }

  /**
   * Get top users this month based on EXP gained.
   *
   * @param int $limit
   * @return Collection
   */
  public function getTopUsersThisMonth(int $limit = self::DEFAULT_LIMIT): array
  {
    $cacheKey = "leaderboard_monthly_{$limit}";

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
      // Get the top users from the database, sorted by total_exp and total_checkin (tiebreaker)
      $leaderboard = User::select(['name', 'image_url', 'total_exp', 'total_checkin'])
        ->orderBy('total_exp', 'desc')
        ->orderBy('total_checkin', 'desc') // Tiebreaker
        ->limit($limit)
        ->get()
        ->toArray(); // Convert to array

      // Add rank to each user in the leaderboard based on the sorted order
      foreach ($leaderboard as $index => $user) {
        $leaderboard[$index]['rank'] = $index + 1; // Add rank starting from 1
      }

      // Return as array with rank
      return $leaderboard;
    });
  }

  /**
   * Get user's current rank from active leaderboard
   */
  public function getUserRank(int $userId): ?int
  {
    $cacheKey = "leaderboard:user_rank:{$userId}";

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
      $activeLeaderboard = Leaderboard::active()->first();

      if (!$activeLeaderboard || !$activeLeaderboard->leaderboard) {
        return null;
      }

      // Search for user in leaderboard JSON data
      $leaderboardData = collect($activeLeaderboard->leaderboard);
      $userEntry = $leaderboardData->firstWhere('user_id', $userId);

      return $userEntry ? $userEntry['rank'] : null;
    });
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
   */
  public function clearLeaderboardCache(): void
  {
    $patterns = [
      'leaderboard:top_users:*',
      'leaderboard:weekly:*',
      'leaderboard:monthly:*',
      'leaderboard:user_rank:*',
    ];

    foreach ($patterns as $pattern) {
      Cache::forget($pattern);
    }

    Log::info('Leaderboard cache cleared');
  }
}
