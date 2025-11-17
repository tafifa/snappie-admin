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
            return response()->json([
                'success' => true,
                'message' => 'Leaderboard detail', 
                'data' => $leaderboard,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve leaderboard detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function userRank(Request $request): JsonResponse
    {
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

        $rank = $this->service->getUserRank($payload);
        return response()->json([
            'success' => true, 
            'message' => 'Latest leaderboard retrieved successfully', 
            'data' => $rank ?? "User not found",
        ]);
    }
}
