<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicTokenAuthenticate
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
        // Check rate limit first (IP-based for public routes)
        if (!$this->checkRateLimit($request)) {
            return $this->rateLimitExceededResponse($request);
        }

        $token = $request->bearerToken();
        $apiKey = config('services.api.public_key');
        
        if (!$token || !$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($token !== $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $response = $next($request);
        foreach ($this->getRateLimitHeaders($request) as $key => $value) {
            $response->headers->set($key, $value);
        }
        return $response;
    }

    /**
     * Check if request exceeds rate limit.
     */
    protected function checkRateLimit(Request $request): bool
    {
        $key = 'public:rate-limit:' . $request->ip();
        $limit = 60; // 60 requests per minute for public endpoints
        $decay = 60;

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            return false;
        }

        $this->limiter->hit($key, $decay);
        return true;
    }

    /**
     * Get rate limit headers.
     */
    protected function getRateLimitHeaders(Request $request): array
    {
        $key = 'public:rate-limit:' . $request->ip();
        $limit = 60;

        return [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $this->limiter->remaining($key, $limit),
            'X-RateLimit-Reset' => now()->addSeconds($this->limiter->availableIn($key))->timestamp,
        ];
    }

    /**
     * Create a too many requests response.
     */
    protected function rateLimitExceededResponse(Request $request): Response
    {
        $key = 'public:rate-limit:' . $request->ip();
        $limit = 60;
        $retryAfter = now()->addSeconds($this->limiter->availableIn($key));

        return response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'errors' => [
                'rate_limit' => ['Rate limit exceeded']
            ]
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter->timestamp - now()->timestamp,
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => $retryAfter->timestamp,
        ]);
    }
}