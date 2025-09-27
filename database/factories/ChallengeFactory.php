<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge>
 */
class ChallengeFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $started = Carbon::instance(fake()->dateTimeThisMonth());

    return [
      'name' => 'Challenge: ' . fake()->words(3, true),
      'description' => fake()->sentence(10),
      'image_url' => 'https://example.com/images/challenges/default.png',
      'exp_reward' => fake()->numberBetween(200, 1000),
      'started_at' => $started,
      'ended_at' => $started->clone()->addWeeks(fake()->numberBetween(1, 4)),
      'challenge_type' => fake()->randomElement(['daily', 'weekly', 'special']),
      'status' => true,
      'additional_info' => [
        'criteria_type' => fake()->randomElement(['checkin_unique', 'review', 'checkin_partner']),
        'target_count' => fake()->numberBetween(3, 10),
      ],
    ];
  }
}
