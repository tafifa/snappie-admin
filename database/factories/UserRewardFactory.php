<?php

namespace Database\Factories;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserReward>
 */
class UserRewardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'reward_id' => Reward::inRandomOrder()->first()?->id,
            'status' => fake()->boolean(60), // 60% chance the reward has been used/finalized
            'additional_info' => [
                'redemption_code' => Str::upper(Str::random(8)),
                'redeemed_at' => now(),
            ],
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}