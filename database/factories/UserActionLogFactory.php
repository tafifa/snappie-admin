<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserActionLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserActionLog>
 */
class UserActionLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actionTypes = [
            UserActionLog::ACTION_CHECKIN,
            UserActionLog::ACTION_REVIEW,
            UserActionLog::ACTION_POST,
            UserActionLog::ACTION_COMMENT,
            UserActionLog::ACTION_LIKE,
            UserActionLog::ACTION_FOLLOW,
        ];

        $actionType = fake()->randomElement($actionTypes);

        return [
            "user_id" => User::inRandomOrder()->first()?->id,
            "action_type" => $actionType,
            "action_data" => $this->getActionData($actionType),
            "created_at" => fake()->dateTimeBetween("-1 year", "now"),
            "updated_at" => fn(array $attributes) => $attributes["created_at"],
        ];
    }

    /**
     * Generate action data based on action type.
     */
    protected function getActionData(string $actionType): array
    {
        return match ($actionType) {
            UserActionLog::ACTION_CHECKIN => [
                "place_id" => fake()->numberBetween(1, 20),
                "place_name" => fake()->company(),
            ],
            UserActionLog::ACTION_REVIEW => [
                "place_id" => fake()->numberBetween(1, 20),
                "review_id" => fake()->numberBetween(1, 100),
                "rating" => fake()->numberBetween(1, 5),
            ],
            UserActionLog::ACTION_POST => [
                "post_id" => fake()->numberBetween(1, 100),
            ],
            UserActionLog::ACTION_COMMENT => [
                "post_id" => fake()->numberBetween(1, 100),
                "comment_id" => fake()->numberBetween(1, 200),
            ],
            UserActionLog::ACTION_LIKE => [
                "target_type" => fake()->randomElement(["post", "comment"]),
                "target_id" => fake()->numberBetween(1, 200),
            ],
            UserActionLog::ACTION_FOLLOW => [
                "followed_user_id" => fake()->numberBetween(1, 10),
            ],
            default => [],
        };
    }

    /**
     * Indicate that the action is a checkin.
     */
    public function checkin(?int $placeId = null): static
    {
        return $this->state(
            fn(array $attributes) => [
                "action_type" => UserActionLog::ACTION_CHECKIN,
                "action_data" => [
                    "place_id" => $placeId ?? fake()->numberBetween(1, 20),
                    "place_name" => fake()->company(),
                ],
            ],
        );
    }

    /**
     * Indicate that the action is a review.
     */
    public function review(?int $placeId = null, ?int $rating = null): static
    {
        return $this->state(
            fn(array $attributes) => [
                "action_type" => UserActionLog::ACTION_REVIEW,
                "action_data" => [
                    "place_id" => $placeId ?? fake()->numberBetween(1, 20),
                    "review_id" => fake()->numberBetween(1, 100),
                    "rating" => $rating ?? fake()->numberBetween(1, 5),
                ],
            ],
        );
    }

    /**
     * Indicate that the action is a follow.
     */
    public function follow(?int $followedUserId = null): static
    {
        return $this->state(
            fn(array $attributes) => [
                "action_type" => UserActionLog::ACTION_FOLLOW,
                "action_data" => [
                    "followed_user_id" =>
                        $followedUserId ?? fake()->numberBetween(1, 10),
                ],
            ],
        );
    }

    /**
     * Indicate that the action is a post.
     */
    public function post(?int $postId = null): static
    {
        return $this->state(
            fn(array $attributes) => [
                "action_type" => UserActionLog::ACTION_POST,
                "action_data" => [
                    "post_id" => $postId ?? fake()->numberBetween(1, 100),
                ],
            ],
        );
    }

    /**
     * Set the action to be created today.
     */
    public function today(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "created_at" => now(),
                "updated_at" => now(),
            ],
        );
    }

    /**
     * Set the action to be created on a specific date.
     */
    public function onDate(string $date): static
    {
        return $this->state(
            fn(array $attributes) => [
                "created_at" => $date,
                "updated_at" => $date,
            ],
        );
    }
}
