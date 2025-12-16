<?php

namespace App\Http\Controllers\Api\V2;

use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamificationController
{
    public function __construct(private GamificationService $service) {}

    /**
     * Get all achievements with user progress.
     */
    public function achievements(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $data = $this->service->getAchievements($user);
            return response()->json([
                "success" => true,
                "message" => "Achievements retrieved",
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to retrieve achievements",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get active challenges with user progress.
     */
    public function challenges(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $data = $this->service->getChallenges($user);
            return response()->json([
                "success" => true,
                "message" => "Challenges retrieved",
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to retrieve challenges",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get available rewards.
     */
    public function rewards(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $data = $this->service->getRewards($user);
            return response()->json([
                "success" => true,
                "message" => "Rewards retrieved",
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to retrieve rewards",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Perform a checkin action.
     * Returns checkin result with achievements unlocked and challenges updated.
     */
    public function checkin(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $payload = $request->validate([
                "place_id" => "required|integer",
                "latitude" => "nullable|numeric",
                "longitude" => "nullable|numeric",
                "image_url" => "nullable|string",
                "additional_info" => "nullable|array",
            ]);

            $result = $this->service->createCheckin($user, $payload);

            return response()->json(
                [
                    "success" => true,
                    "message" => "Checkin created successfully",
                    "data" => $result,
                ],
                201,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                400,
            );
        } catch (\InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();
            $status = str_contains($errorMessage, "not found") ? 404 : 409;
            return response()->json(
                [
                    "success" => false,
                    "message" => $errorMessage,
                ],
                $status,
            );
        } catch (\Exception $e) {
            $status =
                $e instanceof
                \Symfony\Component\HttpKernel\Exception\HttpException
                    ? $e->getStatusCode()
                    : 500;
            $errorMessage = $e->getMessage();

            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        $status === 500
                            ? "Internal server error"
                            : $errorMessage,
                    "error" => $errorMessage,
                ],
                $status,
            );
        }
    }

    /**
     * Create a review.
     * Returns review result with achievements unlocked and challenges updated.
     */
    public function review(Request $request): JsonResponse
    {
        try {
            $payload = $request->validate([
                "place_id" => "required|integer",
                "rating" => "required|integer|min:1|max:5",
                "content" => "nullable|string",
                "image_urls" => "nullable|array",
                "image_urls.*" => "string",
                "additional_info" => "nullable|array",
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $result = $this->service->createReview($user, $payload);

            return response()->json(
                [
                    "success" => true,
                    "message" => "Review created successfully",
                    "data" => $result,
                ],
                201,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                400,
            );
        } catch (\InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();
            $status = str_contains($errorMessage, "not found") ? 404 : 409;
            return response()->json(
                [
                    "success" => false,
                    "message" => $errorMessage,
                ],
                $status,
            );
        } catch (\Exception $e) {
            $status =
                $e instanceof
                \Symfony\Component\HttpKernel\Exception\HttpException
                    ? $e->getStatusCode()
                    : 500;
            $errorMessage = $e->getMessage();

            if ($errorMessage === "Place not found") {
                return response()->json(
                    [
                        "success" => false,
                        "message" => $errorMessage,
                        "error" => $errorMessage,
                    ],
                    404,
                );
            }

            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        $status === 500
                            ? "Internal server error"
                            : $errorMessage,
                    "error" => $errorMessage,
                ],
                $status,
            );
        }
    }

    /**
     * Update an existing review.
     */
    public function updateReview(Request $request, int $review_id): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $payload = $request->validate([
                "rating" => "sometimes|integer|min:1|max:5",
                "content" => "sometimes|nullable|string",
                "image_urls" => "sometimes|nullable|array",
                "image_urls.*" => "string",
                "additional_info" => "sometimes|nullable|array",
            ]);

            $result = $this->service->updateReview($user, $review_id, $payload);

            return response()->json([
                "success" => true,
                "message" => "Review updated successfully",
                "data" => $result,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                400,
            );
        } catch (\InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();
            $status = str_contains($errorMessage, "not found") ? 404 : 403;
            return response()->json(
                [
                    "success" => false,
                    "message" => $errorMessage,
                ],
                $status,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Internal server error",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Grant an achievement to the user (manual grant).
     */
    public function grantAchievement(
        Request $request,
        int $achievement_id,
    ): JsonResponse {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $payload = $request->validate([
                "additional_info" => "nullable|array",
            ]);

            $result = $this->service->grantAchievement(
                $user,
                $achievement_id,
                $payload["additional_info"] ?? [],
            );

            return response()->json(
                [
                    "success" => true,
                    "message" => "Achievement granted",
                    "data" => $result,
                ],
                201,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                400,
            );
        } catch (\InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, "not found")) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Achievement not found",
                        "error" => $errorMessage,
                    ],
                    404,
                );
            }

            if (str_contains($errorMessage, "already have")) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => $errorMessage,
                    ],
                    409,
                );
            }

            return response()->json(
                [
                    "success" => false,
                    "message" => $errorMessage,
                ],
                400,
            );
        } catch (\Exception $e) {
            $status =
                $e instanceof
                \Symfony\Component\HttpKernel\Exception\HttpException
                    ? $e->getStatusCode()
                    : 500;
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        $status === 500
                            ? "Internal server error"
                            : $e->getMessage(),
                    "error" => $e->getMessage(),
                ],
                $status,
            );
        }
    }

    /**
     * Complete a challenge (manual completion).
     */
    public function completeChallenge(
        Request $request,
        int $challenge_id,
    ): JsonResponse {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $payload = $request->validate([
                "additional_info" => "nullable|array",
            ]);

            $result = $this->service->completeChallenge(
                $user,
                $challenge_id,
                $payload["additional_info"] ?? [],
            );

            return response()->json(
                [
                    "success" => true,
                    "message" => "Challenge completed",
                    "data" => $result,
                ],
                201,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                400,
            );
        } catch (\InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, "not found")) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Challenge not found",
                        "error" => $errorMessage,
                    ],
                    404,
                );
            }

            if (str_contains($errorMessage, "already completed")) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => $errorMessage,
                    ],
                    409,
                );
            }

            return response()->json(
                [
                    "success" => false,
                    "message" => $errorMessage,
                ],
                400,
            );
        } catch (\Exception $e) {
            $status =
                $e instanceof
                \Symfony\Component\HttpKernel\Exception\HttpException
                    ? $e->getStatusCode()
                    : 500;
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        $status === 500
                            ? "Internal server error"
                            : $e->getMessage(),
                    "error" => $e->getMessage(),
                ],
                $status,
            );
        }
    }

    /**
     * Redeem a reward.
     */
    public function redeemReward(Request $request, int $reward_id): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $payload = $request->validate([
                "additional_info" => "nullable|array",
            ]);

            $result = $this->service->redeemReward(
                $user,
                $reward_id,
                $payload["additional_info"] ?? [],
            );

            return response()->json(
                [
                    "success" => true,
                    "message" => "Reward redeemed",
                    "data" => $result,
                ],
                201,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                400,
            );
        } catch (\InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, "not found")) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Reward not found",
                        "error" => $errorMessage,
                    ],
                    404,
                );
            }

            if (
                str_contains($errorMessage, "Koin tidak mencukupi") ||
                str_contains($errorMessage, "not enough coins")
            ) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Insufficient coins",
                        "error" => $errorMessage,
                    ],
                    400,
                );
            }

            if (
                str_contains($errorMessage, "Stok hadiah habis") ||
                str_contains($errorMessage, "stock")
            ) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Reward out of stock",
                    ],
                    409,
                );
            }

            if (
                str_contains($errorMessage, "tidak aktif") ||
                str_contains($errorMessage, "not active")
            ) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Reward is not active",
                        "error" => $errorMessage,
                    ],
                    400,
                );
            }

            return response()->json(
                [
                    "success" => false,
                    "message" => $errorMessage,
                ],
                400,
            );
        } catch (\Exception $e) {
            $status =
                $e instanceof
                \Symfony\Component\HttpKernel\Exception\HttpException
                    ? $e->getStatusCode()
                    : 500;
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        $status === 500
                            ? "Internal server error"
                            : $e->getMessage(),
                    "error" => $e->getMessage(),
                ],
                $status,
            );
        }
    }

    /**
     * Get coin transactions for the authenticated user.
     */
    public function coinTransactions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $limit = (int) $request->query("limit", 20);
            $offset = (int) $request->query("offset", 0);

            $data = $this->service->getCoinTransactions($user, $limit, $offset);

            return response()->json([
                "success" => true,
                "message" => "Coin transactions retrieved",
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to retrieve coin transactions",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get EXP transactions for the authenticated user.
     */
    public function expTransactions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized",
                    ],
                    401,
                );
            }

            $limit = (int) $request->query("limit", 20);
            $offset = (int) $request->query("offset", 0);

            $data = $this->service->getExpTransactions($user, $limit, $offset);

            return response()->json([
                "success" => true,
                "message" => "EXP transactions retrieved",
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to retrieve EXP transactions",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
