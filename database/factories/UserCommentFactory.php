<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\UserComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserComment>
 */
class UserCommentFactory extends Factory
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
            'post_id' => Post::inRandomOrder()->first()?->id,
            'comment' => fake()->sentence(fake()->numberBetween(5, 20)),
            'total_like' => fake()->numberBetween(0, 50),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}