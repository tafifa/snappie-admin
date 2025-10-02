<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use App\Traits\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ApiResponseTrait;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get user profile by ID
     */
    public function show(int $user_id): JsonResponse
    {
        try {
            $user = $this->userService->getById($user_id);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            return $this->successResponse($user, 'User profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get user profile', 404, $e->getMessage());
        }
    }

    /**
     * Get user profile
     *
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function profile(UserRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->userService->getProfile($user);

            return $this->successResponse(
                $result,
                'Profil berhasil diambil'
            );
        } catch (\Exception $e) {
            Log::error('Get profile error in controller', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Terjadi kesalahan saat mengambil profil',
                500
            );
        }
    }

    /**
     * Update user profile
     */
    public function update(UserRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->userService->updateProfile($user, $request->validated());

            return $this->successResponse(
                $result,
                'Profil berhasil diperbarui'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Perbarui profil gagal',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            Log::error('Update profile error in controller', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Terjadi kesalahan saat memperbarui profil',
                500
            );
        }
    }

}