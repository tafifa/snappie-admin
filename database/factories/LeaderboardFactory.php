<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Leaderboard>
 */
class LeaderboardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::instance(fake()->dateTimeBetween('-3 months', '-1 month'));
        $endDate = $startDate->clone()->addWeek();

        // Generate sample leaderboard data
        $users = User::inRandomOrder()->limit(10)->get();
        $leaderboardData = [];

        foreach ($users as $index => $user) {
            $leaderboardData[] = [
                'rank' => $index + 1,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_exp' => fake()->numberBetween(1000, 10000),
                'total_coin' => fake()->numberBetween(500, 5000),
                'total_checkin' => fake()->numberBetween(10, 100),
            ];
        }

        return [
            'status' => fake()->boolean(80), // 80% active
            'leaderboard' => $leaderboardData,
            'started_at' => $startDate,
            'ended_at' => $endDate,
            'created_at' => $startDate,
            'updated_at' => $startDate,
        ];
    }
}