<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaderboardRequest;
use App\Services\LeaderboardService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    use ApiResponseTrait;

    protected LeaderboardService $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * Get top users from leaderboard
     *
     * @param int $limit
     * @return JsonResponse
     */
    public function getTopUsers(LeaderboardRequest $request): JsonResponse
    {
        try {
            $topUsers = $this->leaderboardService->getTopUsers($request->leaderboard_id, $request->limit);
            
            return $this->successResponse($topUsers, 'Top users retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get top users', 500, $e->getMessage());
        }
    }

    /**
     * Get user's rank in leaderboard
     *
     * @param int $user_id
     * @return JsonResponse
     */
    public function getUserRank(LeaderboardRequest $request): JsonResponse
    {
        try {
            $rank = $this->leaderboardService->getUserRank($request->user_id, $request->leaderboard_id);
            
            $data = [
                'user_id' => $request->user_id,
                'rank' => $rank ?? 'unranked',
            ];
            
            return $this->successResponse($data, 'User rank retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get user rank', 500, $e->getMessage());
        }
    }

    /**
     * Get top users this week
     *
     * @param int $limit
     * @return JsonResponse
     */
    public function getTopUserThisWeek(int $limit = 10): JsonResponse
    {
        try {
            $weeklyUsers = $this->leaderboardService->getTopUserThisWeek($limit);
            
            return $this->successResponse($weeklyUsers, 'Weekly top users retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get weekly top users', 500, $e->getMessage());
        }
    }

    /**
     * Get top users this month
     *
     * @param int $limit
     * @return JsonResponse
     */
    public function getTopUsersThisMonth(int $limit = 10): JsonResponse
    {
        try {
            $monthlyUsers = $this->leaderboardService->getTopUsersThisMonth($limit);
            
            return $this->successResponse($monthlyUsers, 'Monthly top users retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get monthly top users', 500, $e->getMessage());
        }
    }

    /**
     * Refresh leaderboard data (Admin only)
     *
     * @return JsonResponse
     */
    public function refreshLeaderboard(): JsonResponse
    {
        try {
            $result = $this->leaderboardService->refreshLeaderboard();
            
            if ($result) {
                return $this->successResponse(
                    ['refreshed' => true, 'timestamp' => now()->toISOString()], 
                    'Leaderboard refreshed successfully'
                );
            }
            
            return $this->errorResponse('Failed to refresh leaderboard', 500);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to refresh leaderboard', 500, $e->getMessage());
        }
    }

    /**
     * Clear leaderboard cache (Admin only)
     *
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->leaderboardService->clearLeaderboardCache();
            
            return $this->successResponse(
                ['cache_cleared' => true, 'timestamp' => now()->toISOString()], 
                'Leaderboard cache cleared successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to clear cache', 500, $e->getMessage());
        }
    }
}