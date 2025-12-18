<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessTokens;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Response as HttpResponse;

class ApiTokenAuthenticate
{
    /**
     * The rate limiter instance.
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors' => ['token' => ['Invalid token']],
            ], 401);
        }

        $hashed = hash('sha256', $token);
        $accessToken = PersonalAccessTokens::with('tokenable')
            ->where('token', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors' => ['token' => ['Invalid token']],
            ], 401);
        }

        // Set the user on the request
        $user = $accessToken->tokenable;
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors' => ['token' => ['Invalid token']],
            ], 401);
        }
        
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Check rate limit (user-based for authenticated routes)
        if (!$this->checkRateLimit($request, $user)) {
            return $this->rateLimitExceededResponse($request, $user);
        }

        $response = $next($request);
        $illuminateResponse = new HttpResponse(
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->all()
        );

        return $illuminateResponse->withHeaders($this->getRateLimitHeaders($request, $user));
    }

    /**
     * Check if request exceeds rate limit based on endpoint.
     */
    protected function checkRateLimit(Request $request, $user): bool
    {
        $key = 'private:rate-limit:' . $user->id;
        $path = $request->path();
        
        // Different limits for different endpoints
        if (str_contains($path, 'auth/register') || str_contains($path, 'auth/login')) {
            $limit = 5; // 5 attempts per minute
            $decay = 60;
        } elseif (str_contains($path, 'gamification/checkin') || str_contains($path, 'gamification/review')) {
            $limit = 10; // 10 attempts per hour
            $decay = 3600;
        } elseif (str_contains($path, 'social/posts') || str_contains($path, 'social/comment') || str_contains($path, 'social/like')) {
            $limit = 20; // 20 attempts per minute
            $decay = 60;
        } else {
            $limit = 60; // 60 requests per minute (default)
            $decay = 60;
        }

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            return false;
        }

        $this->limiter->hit($key, $decay);
        return true;
    }

    /**
     * Get rate limit headers.
     */
    protected function getRateLimitHeaders(Request $request, $user): array
    {
        $key = 'private:rate-limit:' . $user->id;
        $path = $request->path();

        if (str_contains($path, 'auth/register') || str_contains($path, 'auth/login')) {
            $limit = 5;
        } elseif (str_contains($path, 'gamification/checkin') || str_contains($path, 'gamification/review')) {
            $limit = 10;
        } elseif (str_contains($path, 'social/posts') || str_contains($path, 'social/comment') || str_contains($path, 'social/like')) {
            $limit = 20;
        } else {
            $limit = 60;
        }

        return [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $this->limiter->remaining($key, $limit),
            'X-RateLimit-Reset' => now()->timestamp + $this->limiter->availableIn($key),
        ];
    }

    /**
     * Create a too many requests response.
     */
    protected function rateLimitExceededResponse(Request $request, $user): Response
    {
        $key = 'private:rate-limit:' . $user->id;
        $path = $request->path();

        if (str_contains($path, 'auth/register') || str_contains($path, 'auth/login')) {
            $limit = 5;
        } elseif (str_contains($path, 'gamification/checkin') || str_contains($path, 'gamification/review')) {
            $limit = 10;
        } elseif (str_contains($path, 'social/posts') || str_contains($path, 'social/comment') || str_contains($path, 'social/like')) {
            $limit = 20;
        } else {
            $limit = 60;
        }

        $retryAfterInSeconds = $this->limiter->availableIn($key);

        return response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'errors' => [
                'rate_limit' => ['Rate limit exceeded']
            ]
        ], 429)->withHeaders([
            'Retry-After' => $retryAfterInSeconds,
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => now()->timestamp + $retryAfterInSeconds,
        ]);
    }
}