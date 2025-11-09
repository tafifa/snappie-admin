<?php

namespace Database\Factories;

use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
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
            'place_id' => Place::inRandomOrder()->first()?->id,
            'content' => fake()->paragraph(fake()->numberBetween(2, 6)),
            'image_urls' => [
                fake()->imageUrl(640, 480, 'nature', true),
                fake()->imageUrl(640, 480, 'city', true),
                fake()->imageUrl(640, 480, 'food', true),
            ],
            'rating' => fake()->numberBetween(1, 5),
            'status' => fake()->boolean(95), // 95% chance of being approved
            'additional_info' => [
                'review_type' => fake()->randomElement(['positive', 'negative', 'neutral']),
            ],
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}