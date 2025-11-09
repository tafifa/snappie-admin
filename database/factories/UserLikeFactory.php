<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserLike>
 */
class UserLikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $likeableModels = [
            \App\Models\Post::class,
            \App\Models\UserComment::class,
        ];

        $likeable = null;

        do {
            $selectedModel = fake()->randomElement($likeableModels);
            
            $likeable = $selectedModel::inRandomOrder()->first();
        } while (!$likeable);

        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'related_to_type' => get_class($likeable),
            'related_to_id' => $likeable->id,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}