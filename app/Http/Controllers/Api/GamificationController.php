<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GamificationRequest;
use App\Services\GamificationService;
use App\Traits\ApiResponseTrait;
use App\Models\User;
use App\Models\Place;
use App\Models\Achievement;
use App\Models\Challenge;
use App\Models\Reward;
use Illuminate\Http\JsonResponse;

class GamificationController extends Controller
{
    use ApiResponseTrait;

    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Perform checkin at a place
     */
    public function performCheckin(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $place = Place::findOrFail($request->place_id);
            
            $checkin = $this->gamificationService->performCheckin($user, $place, $request->only(['latitude', 'longitude', 'image_url', 'additional_info']));
            
            return $this->successResponse($checkin, 'Checkin performed successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to perform checkin', 500, $e->getMessage());
        }
    }

    /**
     * Create a review for a place
     */
    public function createReview(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $place = Place::findOrFail($request->place_id);
            
            $review = $this->gamificationService->createReview($user, $place, $request->only(['content', 'rating', 'image_urls', 'additional_info']));
            
            return $this->successResponse($review, 'Review created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create review', 500, $e->getMessage());
        }
    }

    /**
     * Grant achievement to user
     */
    public function grantAchievement(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $achievement = Achievement::findOrFail($request->achievement_id);
            
            $userAchievement = $this->gamificationService->grantAchievement($user, $achievement);
            
            return $this->successResponse($userAchievement, 'Achievement granted successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to grant achievement', 500, $e->getMessage());
        }
    }

    /**
     * Complete challenge
     */
    public function completeChallenge(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $challenge = Challenge::findOrFail($request->challenge_id);
            
            $userChallenge = $this->gamificationService->completeChallenge($user, $challenge);
            
            return $this->successResponse($userChallenge, 'Challenge completed successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to complete challenge', 500, $e->getMessage());
        }
    }

    /**
     * Redeem reward
     */
    public function redeemReward(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $reward = Reward::findOrFail($request->reward_id);
            
            $result = $this->gamificationService->redeemReward($user, $reward);
            
            return $this->successResponse($result, 'Reward redeemed successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to redeem reward', 500, $e->getMessage());
        }
    }

    /**
     * Add coins to user
     */
    public function addCoins(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            
            $coinTransaction = $this->gamificationService->addCoins($user, $request->amount, $request->related_to_type, $request->related_to_id, $request->description);
            
            return $this->successResponse($coinTransaction, 'Coins added successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add coins', 500, $e->getMessage());
        }
    }

    /**
     * Use coins
     */
    public function useCoins(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            
            $result = $this->gamificationService->useCoins($user, $request->amount, $request->related_to_type, $request->related_to_id, $request->description);
            
            return $this->successResponse($result, 'Coins used successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to use coins', 500, $e->getMessage());
        }
    }

    /**
     * Add experience points to user
     */
    public function addExp(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            
            $result = $this->gamificationService->addExp($user, $request->amount, $request->related_to_type, $request->related_to_id, $request->description);
            
            return $this->successResponse($result, 'Experience points added successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add experience points', 500, $e->getMessage());
        }
    }

    /**
     * Get user coin transactions
     */
    public function getCoinTransactions(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $perPage = $request->get('per_page', 10);
            
            $transactions = $user->coinTransactions()
                ->with(['relatedTo'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            return $this->successResponse($transactions, 'Coin transactions retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get coin transactions', 500, $e->getMessage());
        }
    }

    /**
     * Get user exp transactions
     */
    public function getExpTransactions(GamificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $perPage = $request->get('per_page', 10);
            
            $transactions = $user->expTransactions()
                ->with(['relatedTo'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            return $this->successResponse($transactions, 'Experience transactions retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get experience transactions', 500, $e->getMessage());
        }
    }
}