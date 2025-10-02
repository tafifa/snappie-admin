<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
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
        // Get the response from the next middleware
        $response = $next($request);
        
        // Add standard headers
        $this->addStandardHeaders($response);
        
        // Format the response if it's a JSON response
        if ($response instanceof JsonResponse) {
            $this->formatJsonResponse($response);
        }
        
        return $response;
    }

    /**
     * Add standard headers to the response.
     *
     * @param Response $response
     */
    protected function addStandardHeaders(Response $response): void
    {
        // Add CORS headers for mobile app
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        
        // Add API versioning header
        $response->headers->set('X-API-Version', '1.0.0');
        
        // Add timestamp header
        $response->headers->set('X-Response-Time', now()->toISOString());
    }

    /**
     * Format the JSON response to a standard structure.
     *
     * @param JsonResponse $response
     */
    protected function formatJsonResponse(JsonResponse $response): void
    {
        $originalData = $response->getData(true);
        
        // Check if the response is already in the standard format
        if (isset($originalData['success']) && isset($originalData['message'])) {
            return;
        }
        
        // Determine success based on status code
        $isSuccess = $response->isSuccessful();
        
        // Create the new structured response
        $formattedData = [
            'success' => $isSuccess,
            'message' => $this->getMessage($originalData, $isSuccess),
        ];
        
        // Handle data and errors
        if ($isSuccess) {
            $formattedData['data'] = $originalData;
        } else {
            $formattedData['errors'] = $this->getErrors($originalData);
        }
        
        // Set the new data to the response
        $response->setData($formattedData);
    }

    /**
     * Get the message from the original data.
     *
     * @param array $originalData
     * @param bool $isSuccess
     * @return string
     */
    protected function getMessage(array $originalData, bool $isSuccess): string
    {
        if (isset($originalData['message'])) {
            return $originalData['message'];
        }
        
        return $isSuccess ? 'Success' : 'An error occurred';
    }

    /**
     * Get the errors from the original data.
     *
     * @param array $originalData
     * @return mixed
     */
    protected function getErrors(array $originalData)
    {
        if (isset($originalData['errors'])) {
            return $originalData['errors'];
        }
        
        // If no specific errors, return the original data as error details
        return $originalData;
    }
}
