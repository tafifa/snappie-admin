<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
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
        $commentableModels = [
            \App\Models\Post::class,
            \App\Models\UserComment::class,
        ];

        $commentable = null;

        do {
            $selectedModel = fake()->randomElement($commentableModels);
            
            $commentable = $selectedModel::inRandomOrder()->first();
        } while (!$commentable);

        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'related_to_type' => get_class($commentable),
            'related_to_id' => $commentable->id,
            'comment' => fake()->sentence(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}