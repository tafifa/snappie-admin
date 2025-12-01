<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Leaderboard;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController
{
    public function __construct(private LeaderboardService $service) {}

    public function weekly(): JsonResponse
    {
        $data = $this->service->getTopUserThisWeek();
        return response()->json(['success' => true, 'message' => 'Weekly leaderboard', 'data' => $data]);
    }

    public function monthly(): JsonResponse
    {
        $data = $this->service->getTopUsersThisMonth();
        return response()->json(['success' => true, 'message' => 'Monthly leaderboard', 'data' => $data]);
    }

    public function getLeaderboardById(int $leaderboardId): JsonResponse
    {
        try {
            $leaderboard = $this->service->getTopUsers($leaderboardId);
            if ($leaderboard->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leaderboard not found',
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Leaderboard detail', 
                'data' => $leaderboard,
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'not found')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leaderboard not found',
                ], 404);
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve leaderboard detail',
                'error' => $errorMessage,
            ], 500);
        }
    }

    public function userRank(Request $request): JsonResponse
    {
        try {
            $payload = $request->validate([
                'leaderboard_id' => 'nullable|integer',
                'user_id' => 'nullable|integer',
            ]);

            // Set default values if not provided
            if (!isset($payload['user_id'])) {
                $payload['user_id'] = $request->user()->id;
            }

            if (!isset($payload['leaderboard_id'])) {
                $payload['leaderboard_id'] = Leaderboard::latest()->value('id');
            }

            if (!$payload['leaderboard_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No leaderboard available',
                ], 404);
            }

            $rank = $this->service->getUserRank($payload);
            return response()->json([
                'success' => true, 
                'message' => 'Latest leaderboard retrieved successfully', 
                'data' => $rank ?? null,
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'not found')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leaderboard not found',
                ], 404);
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user rank',
                'error' => $errorMessage,
            ], 500);
        }
    }
}
