<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register new user
     *
     * @param AuthRequest $request
     * @return JsonResponse
     */
    public function register(AuthRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return $this->successResponse(
                $result,
                $result['message'],
                201
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Registrasi gagal',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            Log::error('Registration error in controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Terjadi kesalahan saat registrasi',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Login user (simplified for MVP - email only)
     *
     * @param AuthRequest $request
     * @return JsonResponse
     */
    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();
            $remember = $credentials['remember'] ?? false;

            $result = $this->authService->login($credentials, $remember);

            return $this->successResponse(
                $result,
                $result['message']
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Login gagal',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            // Log::error('Login error in controller', [
            //     'error' => $e->getMessage(),
            //     'email' => $request->input('email'),
            //     'trace' => $e->getTraceAsString()
            // ]);

            return $this->errorResponse(
                'Terjadi kesalahan saat login',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Logout user
     *
     * @param AuthRequest $request
     * @return JsonResponse
     */
    public function logout(AuthRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $result = $this->authService->logout($user);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logout berhasil!',
                    'logged_out_at' => now()->toISOString()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Logout gagal!'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat logout'
            ], 500);
        }
    }
}
