<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\Place;
use App\Models\Achievement;
use App\Models\Challenge;
use App\Models\Reward;
use App\Models\Checkin;
use App\Models\Review;
use App\Models\Post;
use App\Models\Article;
use App\Models\UserAchievement;
use App\Models\UserChallenge;
use App\Models\UserReward;
use App\Models\UserFollow;
use App\Models\CoinTransaction;
use App\Models\ExpTransaction;
use App\Models\UserLike;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('========================');
        $this->command->info('Seeding database...');

        // Create admin user if not exists
        Admin::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );

        User::factory(5)->create();
        Place::factory(10)->create();
        Achievement::factory(5)->create();
        Challenge::factory(5)->create();
        Reward::factory(5)->create();

        Checkin::factory(15)->create();
        Review::factory(15)->create();
        Post::factory(15)->create();
        Article::factory(15)->create();

        UserAchievement::factory(10)->create();
        UserChallenge::factory(10)->create();
        UserReward::factory(10)->create();
        UserFollow::factory(10)->create();
        CoinTransaction::factory(10)->create();
        ExpTransaction::factory(10)->create();
        UserLike::factory(20)->create();

        $this->command->info('Seeding complete!');
        $this->command->info('========================');

    }
}
