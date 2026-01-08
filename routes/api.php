<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\HealthController;
use App\Http\Controllers\Api\V2\AuthenticationController;
use App\Http\Controllers\Api\V2\UsersController;
use App\Http\Controllers\Api\V2\PlacesController;
use App\Http\Controllers\Api\V2\ArticlesController;
use App\Http\Controllers\Api\V2\GamificationController;
use App\Http\Controllers\Api\V2\LeaderboardController;
use App\Http\Controllers\Api\V2\SocialController;
use App\Http\Controllers\Api\V2\AppUpdaterController;

Route::middleware("api")
    ->prefix("v2")
    ->group(function () {
        // Health endpoint
        Route::get("/health", [HealthController::class, "index"]);

        Route::middleware("public")->group(function () {
            // Authentication (register and login only)
            Route::prefix("auth")->group(function () {
                Route::post("/register", [
                    AuthenticationController::class,
                    "register",
                ]);
                Route::post("/login", [
                    AuthenticationController::class,
                    "login",
                ]);
                Route::post("/refresh", [
                    AuthenticationController::class,
                    "refreshToken",
                ]);
            });

            Route::prefix("app")->group(function () {
                Route::get("/update", [
                    AppUpdaterController::class,
                    "checkAndUpdate"
                ]);
            });
        });

        // Routes that require auth middleware
        Route::middleware("private")->group(function () {
            // Authentication (logout only)
            Route::prefix("auth")->group(function () {
                Route::post("/logout", [
                    AuthenticationController::class,
                    "logout",
                ]);
            });

            // Users
            Route::prefix("users")->group(function () {
                Route::get("/search", [UsersController::class, "search"]);
                Route::get("/profile", [UsersController::class, "profile"]);
                Route::post("/profile", [
                    UsersController::class,
                    "updateProfile",
                ]);
                Route::get("/saved", [UsersController::class, "getSaved"]);
                Route::post("/saved", [UsersController::class, "updateSaved"]);

                Route::get("/id/{user_id}", [UsersController::class, "show"]);
                Route::get("/id/{user_id}/activities", [
                    UsersController::class,
                    "activities",
                ]);
                Route::get("/id/{user_id}/stats", [
                    UsersController::class,
                    "stats",
                ]);
                Route::get("/id/{user_id}/checkins", [
                    UsersController::class,
                    "checkins",
                ]);
                Route::get("/id/{user_id}/rewards", [
                    UsersController::class,
                    "rewards",
                ]);
                Route::get("/id/{user_id}/reviews", [
                    UsersController::class,
                    "reviews",
                ]);
                Route::get("/id/{user_id}/posts", [
                    UsersController::class,
                    "posts",
                ]);
                Route::get("/id/{user_id}/achievements", [
                    UsersController::class,
                    "achievements",
                ]);
                Route::get("/id/{user_id}/challenges", [
                    UsersController::class,
                    "challenges",
                ]);
            });

            // Places
            Route::prefix("places")->group(function () {
                Route::get("/", [PlacesController::class, "index"]);
                Route::get("/id/{place_id}", [PlacesController::class, "show"]);
                Route::get("/id/{place_id}/reviews", [
                    PlacesController::class,
                    "reviews",
                ]);
                Route::get("/id/{place_id}/checkins", [
                    PlacesController::class,
                    "checkins",
                ]);
                Route::get("/id/{place_id}/posts", [
                    PlacesController::class,
                    "posts",
                ]);
            });

            // Articles
            Route::prefix("articles")->group(function () {
                Route::get("/", [ArticlesController::class, "index"]);
                Route::get("/id/{article_id}", [
                    ArticlesController::class,
                    "show",
                ]);
            });

            // Gamification
            Route::prefix("gamification")->group(function () {
                // Get achievements/challenges/rewards with user progress
                Route::get("/achievements", [
                    GamificationController::class,
                    "achievements",
                ]);
                Route::get("/achievements/progress", [
                    GamificationController::class,
                    "achievements",
                ]); // Alias for progress
                Route::get("/challenges", [
                    GamificationController::class,
                    "challenges",
                ]);
                Route::get("/challenges/active", [
                    GamificationController::class,
                    "challenges",
                ]); // Alias for active challenges
                Route::get("/rewards", [
                    GamificationController::class,
                    "rewards",
                ]);

                // Transaction history
                Route::get("/coins/transactions", [
                    GamificationController::class,
                    "coinTransactions",
                ]);
                Route::get("/exp/transactions", [
                    GamificationController::class,
                    "expTransactions",
                ]);

                // User actions that trigger achievements
                Route::post("/checkin", [
                    GamificationController::class,
                    "checkin",
                ]);
                Route::post("/review", [
                    GamificationController::class,
                    "review",
                ]);
                Route::put("/review/{review_id}", [
                    GamificationController::class,
                    "updateReview",
                ]);

                // Manual grant/complete/redeem endpoints
                Route::post("/achievements/{achievement_id}/grant", [
                    GamificationController::class,
                    "grantAchievement",
                ]);
                Route::post("/achievements/{achievement_id}/claim", [
                    GamificationController::class,
                    "grantAchievement",
                ]); // Alias
                Route::post("/challenges/{challenge_id}/complete", [
                    GamificationController::class,
                    "completeChallenge",
                ]);
                Route::post("/rewards/{reward_id}/redeem", [
                    GamificationController::class,
                    "redeemReward",
                ]);
            });

            // Social
            Route::prefix("social")->group(function () {
                Route::get("/posts", [SocialController::class, "posts"]);
                Route::get("/posts/id/{post_id}", [
                    SocialController::class,
                    "postDetail",
                ]);
                Route::post("/posts", [SocialController::class, "createPost"]);
                Route::delete("/posts/id/{post_id}", [
                    SocialController::class,
                    "deletePost",
                ]);

                Route::get("/follow", [SocialController::class, "followData"]);
                Route::post("/follow/id/{user_id}", [
                    SocialController::class,
                    "follow",
                ]);

                Route::get("/posts/id/{post_id}/likes", [
                    SocialController::class,
                    "likes",
                ]);
                Route::post("/posts/id/{post_id}/like", [
                    SocialController::class,
                    "likePost",
                ]);

                Route::get("/posts/id/{post_id}/comments", [
                    SocialController::class,
                    "comments",
                ]);
                Route::post("/posts/id/{post_id}/comment", [
                    SocialController::class,
                    "commentPost",
                ]);

                Route::get("/comments/id/{comment_id}/likes", [
                    SocialController::class,
                    "commentLikes",
                ]);
                Route::post("/comments/id/{comment_id}/like", [
                    SocialController::class,
                    "likeComment",
                ]);
            });

            // Leaderboard
            Route::prefix("leaderboard")->group(function () {
                Route::get("/weekly", [LeaderboardController::class, "weekly"]);
                Route::get("/monthly", [
                    LeaderboardController::class,
                    "monthly",
                ]);
                Route::get("/id/{leaderboard_id}", [
                    LeaderboardController::class,
                    "getLeaderboardById",
                ]);
                Route::get("/rank", [LeaderboardController::class, "userRank"]);
            });
        });
    });
