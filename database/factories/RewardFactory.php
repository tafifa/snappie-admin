<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reward>
 */
class RewardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Reward: ' . fake()->words(3, true),
            'description' => fake()->sentence(12),
            'image_url' => fake()->imageUrl(640, 480, 'nature', true),
            'coin_requirement' => fake()->numberBetween(500, 5000),
            'stock' => fake()->numberBetween(10, 100),
            'started_at' => Carbon::now(),
            'ended_at' => Carbon::now()->addMonth(),
            'status' => true,
            'additional_info' => [
                'reward_type' => fake()->randomElement(['voucher', 'discount', 'merchandise']),
                'tnc' => 'Syarat dan ketentuan berlaku.',
            ],
        ];
    }
}