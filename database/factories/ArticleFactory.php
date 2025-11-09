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
            'author' => fake()->name(),
            'title' => fake()->sentence(6),
            'category' => fake()->randomElement(['Kuliner', 'Wisata', 'Budaya', 'Tips', 'Event Pontianak']),
            'description' => fake()->paragraphs(fake()->numberBetween(8, 20), true),
            'image_url' => fake()->imageUrl(640, 480, 'nature', true),
            'link' => fake()->url(),
            'created_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'updated_at' => fn(array $attributes) => $attributes['created_at'],
        ];
    }
}
