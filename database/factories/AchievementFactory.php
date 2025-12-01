<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Smknstd\FakerPicsumImages\FakerPicsumImagesProvider;

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
    $faker = fake();
    $faker->addProvider(new FakerPicsumImagesProvider($faker));

    return [
      'name' => 'Achievement ' . fake()->words(2, true),
      'description' => fake()->sentence(),
      'image_url' => $faker->imageUrl(640, 480),
      'coin_reward' => fake()->numberBetween(50, 250),
      'status' => true,
      'additional_info' => [
        'criteria_type' => fake()->randomElement(['checkin_count', 'review_count', 'post_count', 'follow_count']),
        'target_value' => fake()->numberBetween(5, 20),
      ],
    ];
  }
}
