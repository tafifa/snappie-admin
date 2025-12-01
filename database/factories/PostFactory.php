<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;
use Smknstd\FakerPicsumImages\FakerPicsumImagesProvider;

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
        $faker = fake();
        $faker->addProvider(new FakerPicsumImagesProvider($faker));

        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'place_id' => Place::inRandomOrder()->first()?->id,
            'content' => fake()->paragraph(fake()->numberBetween(2, 5)),
            'image_urls' => [
                $faker->imageUrl(640, 480),
                $faker->imageUrl(640, 480),
                $faker->imageUrl(640, 480),
            ],
            'total_like' => fake()->numberBetween(0, 500),
            'total_comment' => fake()->numberBetween(0, 100),
            'status' => fake()->boolean(95), // 95% active
            'additional_info' => [
                'hashtags' => fake()->optional(0.6)->randomElements([
                    '#pontianak', '#kuliner', '#wisata', '#budaya', '#event'
                ], fake()->numberBetween(1, 3)),
                'location_details' => fake()->optional(0.4)->sentence(),
            ],
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}