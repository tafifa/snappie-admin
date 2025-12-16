<?php

namespace Database\Factories;

use App\Models\Achievement;
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

        $types = [
            Achievement::TYPE_ACHIEVEMENT,
            Achievement::TYPE_CHALLENGE,
        ];

        $resetSchedules = [
            Achievement::RESET_NONE,
            Achievement::RESET_DAILY,
            Achievement::RESET_WEEKLY,
        ];

        $actions = [
            "checkin",
            "review",
            "upload_photo",
            "rating_5_star",
            "follow",
            "post",
        ];
        $selectedAction = fake()->randomElement($actions);
        $targetValue = fake()->numberBetween(1, 20);

        return [
            "code" => fake()->unique()->slug(2),
            "name" => "Achievement " . fake()->words(2, true),
            "type" => fake()->randomElement($types),
            "description" => fake()->sentence(),
            "criteria_action" => $selectedAction,
            "criteria_target" => $targetValue,
            "image_url" => $faker->imageUrl(640, 480),
            "coin_reward" => fake()->numberBetween(50, 250),
            "reward_xp" => fake()->numberBetween(25, 125),
            "status" => true,
            "reset_schedule" => fake()->randomElement($resetSchedules),
            "display_order" => fake()->numberBetween(1, 100),
            "additional_info" => null,
        ];
    }

    /**
     * Indicate that the achievement is a one-time achievement.
     */
    public function oneTime(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "type" => Achievement::TYPE_ACHIEVEMENT,
                "reset_schedule" => Achievement::RESET_NONE,
            ],
        );
    }

    /**
     * Indicate that the achievement is a daily challenge.
     */
    public function daily(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "type" => Achievement::TYPE_CHALLENGE,
                "reset_schedule" => Achievement::RESET_DAILY,
            ],
        );
    }

    /**
     * Indicate that the achievement is a weekly challenge.
     */
    public function weekly(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "type" => Achievement::TYPE_CHALLENGE,
                "reset_schedule" => Achievement::RESET_WEEKLY,
            ],
        );
    }

    /**
     * Indicate that the achievement is a special challenge (one-time).
     */
    public function special(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "type" => Achievement::TYPE_CHALLENGE,
                "reset_schedule" => Achievement::RESET_NONE,
            ],
        );
    }

    /**
     * Indicate that the achievement is inactive.
     */
    public function inactive(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => false,
            ],
        );
    }

    /**
     * Set a specific action type for the achievement criteria.
     */
    public function forAction(string $action, int $target = 5): static
    {
        return $this->state(
            fn(array $attributes) => [
                "criteria_action" => $action,
                "criteria_target" => $target,
            ],
        );
    }
}
