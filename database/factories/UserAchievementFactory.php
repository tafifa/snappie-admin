<?php

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAchievement>
 */
class UserAchievementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $achievement = Achievement::inRandomOrder()->first();
        $target =
            $achievement?->criteria_config["target"] ??
            fake()->numberBetween(1, 10);
        $isCompleted = fake()->boolean(70); // 70% chance of being completed
        $currentProgress = $isCompleted
            ? $target
            : fake()->numberBetween(0, $target - 1);

        return [
            "user_id" => User::inRandomOrder()->first()?->id,
            "achievement_id" => $achievement?->id,
            "current_progress" => $currentProgress,
            "target_progress" => $target,
            "status" => $isCompleted,
            "completed_at" => $isCompleted
                ? fake()->dateTimeBetween("-1 year", "now")
                : null,
            "period_date" =>
                $achievement?->reset_schedule !== "none"
                    ? fake()
                        ->dateTimeBetween("-1 month", "now")
                        ->format("Y-m-d")
                    : null,
            "additional_info" => null,
            "created_at" => fake()->dateTimeBetween("-1 year", "now"),
            "updated_at" => fn(array $attributes) => $attributes["created_at"],
        ];
    }

    /**
     * Indicate that the user achievement is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $target = $attributes["target_progress"] ?? 1;
            return [
                "current_progress" => $target,
                "status" => true,
                "completed_at" => fake()->dateTimeBetween("-1 year", "now"),
            ];
        });
    }

    /**
     * Indicate that the user achievement is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            $target = $attributes["target_progress"] ?? 5;
            return [
                "current_progress" => fake()->numberBetween(
                    1,
                    max(1, $target - 1),
                ),
                "status" => false,
                "completed_at" => null,
            ];
        });
    }

    /**
     * Indicate that the user achievement has no progress.
     */
    public function notStarted(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "current_progress" => 0,
                "status" => false,
                "completed_at" => null,
            ],
        );
    }

    /**
     * Set a specific period date (for resettable achievements).
     */
    public function forPeriod(string $periodDate): static
    {
        return $this->state(
            fn(array $attributes) => [
                "period_date" => $periodDate,
            ],
        );
    }

    /**
     * Set the achievement as a one-time achievement (no period date).
     */
    public function oneTime(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "period_date" => null,
            ],
        );
    }

    /**
     * Set today as the period date.
     */
    public function today(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "period_date" => now()->toDateString(),
            ],
        );
    }

    /**
     * Set this week's start as the period date.
     */
    public function thisWeek(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "period_date" => now()->startOfWeek()->toDateString(),
            ],
        );
    }

    /**
     * Set this month's start as the period date.
     */
    public function thisMonth(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "period_date" => now()->startOfMonth()->toDateString(),
            ],
        );
    }
}
