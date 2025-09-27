<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserChallenge>
 */
class UserChallengeFactory extends Factory
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
            'challenge_id' => Challenge::inRandomOrder()->first()?->id,
            'status' => fake()->boolean(30), // 30% chance of being completed
            'additional_info' => function (array $attributes) {
                $challenge = Challenge::find($attributes['challenge_id']);
                $target = $challenge->additional_info['target_count'] ?? 5;
                $criteriaType = $challenge->additional_info['criteria_type'] ?? 'checkin_count';
                $isCompleted = $attributes['status'];

                $current = $isCompleted ? $target : fake()->numberBetween(0, $target - 1);

                return [
                    'current_count' => $current,
                    'target_count' => $target,
                    'criteria_type' => $criteriaType,
                    'completed_at' => $isCompleted ? now() : null,
                ];
            },
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}