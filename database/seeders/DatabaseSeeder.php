<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\Place;
use App\Models\Achievement;
use App\Models\Reward;
use App\Models\Checkin;
use App\Models\Review;
use App\Models\Post;
use App\Models\Article;
use App\Models\UserAchievement;
use App\Models\UserReward;
use App\Models\UserFollow;
use App\Models\CoinTransaction;
use App\Models\ExpTransaction;
use App\Models\UserComment;
use App\Models\UserLike;
use App\Models\UserActionLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info("========================");
        $this->command->info("Seeding database...");

        // Create admin user if not exists
        Admin::firstOrCreate(
            ["email" => env("ADMIN_EMAIL")],
            [
                "name" => env("ADMIN_NAME"), 
                "password" => Hash::make(env("ADMIN_PASSWORD"))
            ],
        );

        // Seed places data
        $this->call(PlaceSeeder::class);

        // Seed gamification achievements data
        $this->call(GamificationSeeder::class);

        // Seed using factories for testing only
        // User::factory(10)->create();
        // Reward::factory(5)->create();

        // Article::factory(15)->create();
        // Checkin::factory(15)->create();
        // Review::factory(15)->create();
        // Post::factory(15)->create();

        // UserAchievement::factory(20)->create();
        // UserReward::factory(20)->create();

        // CoinTransaction::factory(20)->create();
        // ExpTransaction::factory(20)->create();

        // UserFollow::factory(20)->create();
        // UserComment::factory(20)->create();
        // UserLike::factory(20)->create();

        // // Seed user action logs for gamification testing
        // UserActionLog::factory(50)->create();

        $this->command->info("Seeding complete!");
        $this->command->info("========================");
    }
}
