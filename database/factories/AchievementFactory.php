<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Achievement>
 */
class AchievementFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name' => 'Achievement ' . fake()->words(2, true),
      'description' => fake()->sentence(),
      'image_url' => fake()->imageUrl(640, 480, 'achievement', true),
      'coin_reward' => fake()->numberBetween(50, 250),
      'status' => true,
      'additional_info' => [
        'criteria_type' => fake()->randomElement(['checkin_count', 'review_count', 'post_count', 'follow_count']),
        'target_value' => fake()->numberBetween(5, 20),
      ],
    ];
  }
}
