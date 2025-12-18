<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Checkin;
use App\Models\Review;
use App\Models\Post;
use App\Models\Place;
use App\Observers\CheckinObserver;
use App\Observers\ReviewObserver;
use App\Observers\PostObserver;
use App\Observers\PlaceObserver;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for Cloudinary uploads
        Checkin::observe(CheckinObserver::class);
        Review::observe(ReviewObserver::class);
        Post::observe(PostObserver::class);
        Place::observe(PlaceObserver::class);

        RateLimiter::for('api', function (Request $request) {
            return [
                Limit::perMinute(15)->by(optional($request->user())->id ?: $request->ip()),
            ];
        });

        URL::forceScheme('https');
    }
}
