<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
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
            'title' => fake()->sentence(6),
            'category' => fake()->randomElement(['Kuliner', 'Wisata', 'Budaya', 'Tips', 'Event Pontianak']),
            'content' => fake()->paragraphs(fake()->numberBetween(8, 20), true),
            'image_urls' => '',
            'created_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'updated_at' => fn(array $attributes) => $attributes['created_at'],
        ];
    }
}
