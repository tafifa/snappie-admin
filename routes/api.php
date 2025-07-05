<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\CheckinController;
use App\Http\Controllers\Api\ReviewController;

/*
|--------------------------------------------------------------------------
| API Routes - Snappie API
|--------------------------------------------------------------------------
|
| API endpoints contains:
| - Authentication (register, login, logout)
| - User profile management
| - Places discovery & nearby search
| - Check-ins with GPS verification
| - Reviews with rating system
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1'); // 5 requests per minute
        
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1'); // 5 requests per minute
        
        Route::post('refresh', [AuthController::class, 'refresh'])
            ->middleware('throttle:10,1'); // 10 requests per minute
    });
    
    // Protected routes (authentication required)
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Authentication (protected)
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });
        
        // User Profile
        Route::prefix('user')->group(function () {
            Route::get('profile', [UserController::class, 'profile'])
                ->middleware('throttle:60,1');
            
            Route::put('profile', [UserController::class, 'updateProfile'])
                ->middleware('throttle:20,1');
            
            Route::post('avatar', [UserController::class, 'uploadAvatar'])
                ->middleware('throttle:10,1');
        });
        
        // Places
        Route::prefix('places')->group(function () {
            Route::get('/', [PlaceController::class, 'index'])
                ->middleware('throttle:100,1');
            
            Route::get('nearby', [PlaceController::class, 'nearby'])
                ->middleware('throttle:100,1');
            
            Route::get('{id}', [PlaceController::class, 'show'])
                ->middleware('throttle:100,1');
        });
        
        // Categories
        Route::get('categories', [PlaceController::class, 'categories'])
            ->middleware('throttle:60,1');
        
        // Check-ins
        Route::prefix('checkins')->group(function () {
            Route::post('/', [CheckinController::class, 'store'])
                ->middleware('throttle:10,1');
            
            Route::get('history', [CheckinController::class, 'history'])
                ->middleware('throttle:60,1');
        });
        
        // Reviews
        Route::prefix('reviews')->group(function () {
            Route::post('/', [ReviewController::class, 'store'])
                ->middleware('throttle:20,1');
            
            Route::get('/', [ReviewController::class, 'index'])
                ->middleware('throttle:100,1');
        });
    });
});

// API Info endpoint
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Snappie Mobile API - MVP',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
        'endpoints' => [
            'auth' => '/api/v1/auth/*',
            'user' => '/api/v1/user/*',
            'places' => '/api/v1/places/*',
            'checkins' => '/api/v1/checkins/*',
            'reviews' => '/api/v1/reviews/*'
        ]
    ]);
})->middleware('throttle:60,1');