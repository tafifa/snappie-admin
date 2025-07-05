<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set default headers for API responses
        $response = $next($request);
        
        // Add CORS headers for mobile app
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        
        // Add API versioning header
        $response->headers->set('X-API-Version', '1.0.0');
        
        // Add rate limit headers if available
        if ($request->route() && $request->route()->middleware()) {
            $response->headers->set('X-API-Rate-Limit', 'See rate limit headers');
        }
        
        return $response;
    }
}
