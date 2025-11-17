<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessTokens;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuthenticate
{
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

        return $next($request);
    }
}