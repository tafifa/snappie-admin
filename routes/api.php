<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\GamificationController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SocialMediaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
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
    });

    // Authentication routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication routes (protected)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    
    // Places routes
    Route::prefix('places')->group(function () {
        Route::get('/', [PlaceController::class, 'index']);
        Route::get('/id/{place_id}', [PlaceController::class, 'show']);
        Route::get('/id/{place_id}/reviews', [PlaceController::class, 'getPlaceReviews']);
    });
    
    // Articles routes
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::get('/id/{article_id}', [ArticleController::class, 'show']);
    });
    
    // Leaderboard routes
    Route::prefix('leaderboard')->group(function () {
        Route::get('/id/{leaderboard_id}/top-users', [LeaderboardController::class, 'getTopUsers']);
        Route::get('/id/{leaderboard_id}/user/id/{user_id}', [LeaderboardController::class, 'getUserRank']);
        Route::get('/weekly', [LeaderboardController::class, 'getTopUserThisWeek']);
        Route::get('/monthly', [LeaderboardController::class, 'getTopUsersThisMonth']);
    });
    
    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/id/{user_id}', [UserController::class, 'show']);
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/profile', [UserController::class, 'update']);
    });
    
    // Gamification routes
    Route::prefix('gamification')->group(function () {
        Route::post('/checkin', [GamificationController::class, 'performCheckin']);
        Route::post('/review', [GamificationController::class, 'createReview']);
        Route::post('/achievement', [GamificationController::class, 'grantAchievement']);
        Route::post('/challenge/complete', [GamificationController::class, 'completeChallenge']);
        Route::post('/reward/redeem', [GamificationController::class, 'redeemReward']);
        
        // Coin and Experience management
        Route::post('/coins/add', [GamificationController::class, 'addCoins']);
        Route::post('/coins/use', [GamificationController::class, 'useCoins']);
        Route::post('/exp/add', [GamificationController::class, 'addExp']);
        
        // Transaction history
        Route::get('/coins/transactions', [GamificationController::class, 'getCoinTransactions']);
        Route::get('/exp/transactions', [GamificationController::class, 'getExpTransactions']);
    });
    
    // Social Media routes
    Route::prefix('social')->group(function () {
        // Follow system
        Route::post('/follow', [SocialMediaController::class, 'follow']);
        Route::delete('/unfollow', [SocialMediaController::class, 'unfollow']);
        Route::get('/followers', [SocialMediaController::class, 'getFollowers']);
        Route::get('/following', [SocialMediaController::class, 'getFollowing']);
        Route::get('/is-following', [SocialMediaController::class, 'isFollowing']);
        
        // User profiles and posts
        Route::get('/profile/{user_id}', [SocialMediaController::class, 'getUserProfile']);
        Route::get('/feed', [SocialMediaController::class, 'getFeedPosts']);
        Route::get('/posts/user/{user_id}', [SocialMediaController::class, 'getPostsByUser']);
        
        // Post management
        Route::post('/posts', [SocialMediaController::class, 'createPost']);
        Route::get('/posts/trending', [SocialMediaController::class, 'getTrendingPosts']);
        Route::get('/posts/id/{post_id}', [SocialMediaController::class, 'getPostById']);
        Route::put('/posts/id/{post_id}', [SocialMediaController::class, 'updatePost']);
        Route::delete('/posts/id/{post_id}', [SocialMediaController::class, 'deletePost']);
        
        // Post interactions
        Route::post('/posts/id/{target_id}/like', [SocialMediaController::class, 'likePost']);
        Route::delete('/posts/id/{target_id}/like', [SocialMediaController::class, 'unlikePost']);
        Route::get('/posts/id/{target_id}/comments', [SocialMediaController::class, 'getPostComments']);
        Route::post('/posts/id/{target_id}/comments', [SocialMediaController::class, 'commentOnPost']);
        Route::delete('/posts/id/{comment_id}/comments', [SocialMediaController::class, 'deleteComment']);
    });
});
