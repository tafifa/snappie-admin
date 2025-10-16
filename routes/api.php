<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GamificationController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\SocialMediaController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes - Snappie API
|--------------------------------------------------------------------------
|
| Production API routes mirrored from the development build with per-endpoint
| throttling to protect the service under load.
|
*/

Route::middleware(['auth:sanctum', 'throttle:60,1'])->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'Snappie API',
            'version' => '2.0.0',
            'timestamp' => now()->toISOString(),
            'endpoints' => [
                'health' => '/api/v1/health',
                'auth' => '/api/v1/auth/*',
                'user' => '/api/v1/user/*',
                'places' => '/api/v1/places/*',
                'leaderboard' => '/api/v1/leaderboard/*',
                'articles' => '/api/v1/articles/*',
                'social-media' => '/api/v1/social-media/*',
            ]
        ]);
    })->middleware('throttle:60,1');

    // Authentication routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1');
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication routes (protected)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('throttle:20,1');
    });

    // Image/Video upload routes
    Route::prefix('upload')->group(function () {
        Route::post('/file', [App\Http\Controllers\Api\ImageUploadController::class, 'upload'])
            ->middleware('throttle:30,1'); // Single file (image or video)
        Route::post('/multiple', [App\Http\Controllers\Api\ImageUploadController::class, 'uploadMultiple'])
            ->middleware('throttle:20,1'); // Multiple files (max 5)
        Route::delete('/delete', [App\Http\Controllers\Api\ImageUploadController::class, 'delete'])
            ->middleware('throttle:30,1');
    });

    // Places routes
    Route::prefix('places')->group(function () {
        Route::get('/', [PlaceController::class, 'index'])
            ->middleware('throttle:100,1');
        Route::get('/id/{place_id}', [PlaceController::class, 'show'])
            ->middleware('throttle:100,1');
        Route::get('/id/{place_id}/reviews', [PlaceController::class, 'getPlaceReviews'])
            ->middleware('throttle:60,1');
    });

    // Articles routes
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index'])
            ->middleware('throttle:80,1');
        Route::get('/id/{article_id}', [ArticleController::class, 'show'])
            ->middleware('throttle:80,1');
    });

    // Leaderboard routes
    Route::prefix('leaderboard')->group(function () {
        Route::get('/id/{leaderboard_id}/top-users', [LeaderboardController::class, 'getTopUsers'])
            ->middleware('throttle:60,1');
        Route::get('/id/{leaderboard_id}/user/id/{user_id}', [LeaderboardController::class, 'getUserRank'])
            ->middleware('throttle:60,1');
        Route::get('/weekly', [LeaderboardController::class, 'getTopUserThisWeek'])
            ->middleware('throttle:60,1');
        Route::get('/monthly', [LeaderboardController::class, 'getTopUsersThisMonth'])
            ->middleware('throttle:60,1');
    });

    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/id/{user_id}', [UserController::class, 'show'])
            ->middleware('throttle:60,1');
        Route::get('/profile', [UserController::class, 'profile'])
            ->middleware('throttle:60,1');
        Route::post('/profile', [UserController::class, 'update'])
            ->middleware('throttle:30,1');
    });

    // Gamification routes
    Route::prefix('gamification')->group(function () {
        Route::post('/checkin', [GamificationController::class, 'performCheckin'])
            ->middleware('throttle:20,1');
        Route::post('/review', [GamificationController::class, 'createReview'])
            ->middleware('throttle:20,1');
        Route::post('/achievement', [GamificationController::class, 'grantAchievement'])
            ->middleware('throttle:20,1');
        Route::post('/challenge/complete', [GamificationController::class, 'completeChallenge'])
            ->middleware('throttle:20,1');
        Route::post('/reward/redeem', [GamificationController::class, 'redeemReward'])
            ->middleware('throttle:20,1');

        // Coin and Experience management
        Route::post('/coins/add', [GamificationController::class, 'addCoins'])
            ->middleware('throttle:20,1');
        Route::post('/coins/use', [GamificationController::class, 'useCoins'])
            ->middleware('throttle:20,1');
        Route::post('/exp/add', [GamificationController::class, 'addExp'])
            ->middleware('throttle:20,1');

        // Transaction history
        Route::get('/coins/transactions', [GamificationController::class, 'getCoinTransactions'])
            ->middleware('throttle:60,1');
        Route::get('/exp/transactions', [GamificationController::class, 'getExpTransactions'])
            ->middleware('throttle:60,1');
    });

    // Social Media routes
    Route::prefix('social')->group(function () {
        // Follow system
        Route::post('/follow', [SocialMediaController::class, 'follow'])
            ->middleware('throttle:30,1');
        Route::delete('/unfollow', [SocialMediaController::class, 'unfollow'])
            ->middleware('throttle:30,1');
        Route::get('/followers', [SocialMediaController::class, 'getFollowers'])
            ->middleware('throttle:60,1');
        Route::get('/following', [SocialMediaController::class, 'getFollowing'])
            ->middleware('throttle:60,1');
        Route::get('/is-following', [SocialMediaController::class, 'isFollowing'])
            ->middleware('throttle:60,1');

        // User profiles and posts
        Route::get('/profile/{user_id}', [SocialMediaController::class, 'getUserProfile'])
            ->middleware('throttle:60,1');
        Route::get('/posts/user/{user_id}', [SocialMediaController::class, 'getPostsByUser'])
            ->middleware('throttle:60,1');

        // Post management
        Route::post('/posts', [SocialMediaController::class, 'createPost'])
            ->middleware('throttle:20,1');
        Route::get('/posts', [SocialMediaController::class, 'getDefaultFeedPosts'])
            ->middleware('throttle:60,1');
        Route::get('/posts/feed', [SocialMediaController::class, 'getFeedPosts'])
            ->middleware('throttle:60,1');
        Route::get('/posts/trending', [SocialMediaController::class, 'getTrendingPosts'])
            ->middleware('throttle:60,1');
        Route::get('/posts/id/{post_id}', [SocialMediaController::class, 'getPostById'])
            ->middleware('throttle:60,1');
        Route::put('/posts/id/{post_id}', [SocialMediaController::class, 'updatePost'])
            ->middleware('throttle:20,1');
        Route::delete('/posts/id/{post_id}', [SocialMediaController::class, 'deletePost'])
            ->middleware('throttle:20,1');

        // Post interactions
        Route::post('/posts/id/{target_id}/like', [SocialMediaController::class, 'likePost'])
            ->middleware('throttle:30,1');
        Route::delete('/posts/id/{target_id}/like', [SocialMediaController::class, 'unlikePost'])
            ->middleware('throttle:30,1');
        Route::get('/posts/id/{target_id}/comments', [SocialMediaController::class, 'getPostComments'])
            ->middleware('throttle:60,1');
        Route::post('/posts/id/{target_id}/comments', [SocialMediaController::class, 'commentOnPost'])
            ->middleware('throttle:30,1');
        Route::delete('/posts/id/{comment_id}/comments', [SocialMediaController::class, 'deleteComment'])
            ->middleware('throttle:30,1');
    });
});
