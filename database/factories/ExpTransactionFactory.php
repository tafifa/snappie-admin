<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpTransaction>
 */
class ExpTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transactableModels = [
            \App\Models\Checkin::class,
            \App\Models\Review::class,
            \App\Models\UserChallenge::class,
        ];

        $transactable = null;

        do {
            $selectedModel = fake()->randomElement($transactableModels);
            
            $transactable = $selectedModel::inRandomOrder()->first();
        } while (!$transactable);

        return [
            'user_id' => User::factory(),
            'related_to_type' => get_class($transactable),
            'related_to_id' => $transactable->id,
            'amount' => fake()->numberBetween(50, 200),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}