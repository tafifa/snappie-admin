<?php

namespace App\Http\Controllers\Api\V2;

use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamificationController
{
    public function __construct(private GamificationService $service) {}
    public function achievements(): JsonResponse
    {
        try {
            $data = $this->service->listAchievements();
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

    public function challenges(): JsonResponse
    {
        try {
            $data = $this->service->listChallenges();
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

    public function rewards(): JsonResponse
    {
        try {
            $data = $this->service->listRewards();
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
            // Return 404 for not found, 409 for conflict/duplicate
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

            // Return detailed error information
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

    public function review(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $payload = $request->validate([
                "place_id" => "required|integer",
                "rating" => "required|integer|min:1|max:5",
                "content" => "nullable|string",
                "image_urls" => "nullable|array",
                "image_urls.*" => "string",
                "additional_info" => "nullable|array",
            ]);

            // Ambil user dari request
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

            // Panggil service
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
            // Return 404 for not found, 409 for conflict/duplicate
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

            // Handle specific exceptions
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

            // Return detailed error information
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
                $payload["additional_info"],
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
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Handle specific exceptions
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
                            : $errorMessage,
                    "error" => $errorMessage,
                ],
                $status,
            );
        }
    }

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
                $payload["additional_info"],
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
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Handle specific exceptions
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
                            : $errorMessage,
                    "error" => $errorMessage,
                ],
                $status,
            );
        }
    }

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
                $payload["additional_info"],
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
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Handle specific exceptions
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
                            : $errorMessage,
                    "error" => $errorMessage,
                ],
                $status,
            );
        }
    }

    public function coinTransactions(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $filters = [];
            if ($request->query("per_page")) {
                $filters["per_page"] = (int) $request->query("per_page");
            }
            if ($request->query("page")) {
                $filters["page"] = (int) $request->query("page");
            }

            $data = $this->service->getCoinTransactions(
                (int) $userId,
                $filters,
            );
            return response()->json([
                "success" => true,
                "message" => "Coin transactions",
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

    public function expTransactions(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $filters = [];
            if ($request->query("per_page")) {
                $filters["per_page"] = (int) $request->query("per_page");
            }
            if ($request->query("page")) {
                $filters["page"] = (int) $request->query("page");
            }

            $data = $this->service->getExpTransactions((int) $userId, $filters);

            return response()->json([
                "success" => true,
                "message" => "EXP transactions",
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
