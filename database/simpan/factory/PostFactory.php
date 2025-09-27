<?php

namespace Database\Factories;

use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
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
            'content' => fake()->paragraph(fake()->numberBetween(1, 4)),
            'image_urls' => fake()->optional(0.9)->randomElements( // 90% chance of having 1 to 4 images
                [
                    fake()->imageUrl(640, 480, 'place', true),
                    fake()->imageUrl(640, 480, 'people', true),
                    fake()->imageUrl(640, 480, 'event', true),
                    fake()->imageUrl(640, 480, 'nightlife', true),
                ],
                rand(1, 4)
            ),
            'total_like' => fake()->numberBetween(0, 500),
            'total_comment' => fake()->numberBetween(0, 50),
            'status' => fake()->boolean(98), // 98% chance of being approved
            'additional_info' => null,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}