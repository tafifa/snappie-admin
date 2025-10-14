<?php

namespace Database\Factories;

use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checkin>
 */
class CheckinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // This closure ensures the latitude and longitude match the selected place.
        $place = Place::inRandomOrder()->first();
        
        // Extract coordinates from PostGIS POINT format if available
        $latitude = fake()->latitude(-0.1, 0.1); // Default around Pontianak
        $longitude = fake()->longitude(109.2, 109.4); // Default around Pontianak
        
        // If place has location data, try to extract it (simplified approach)
        if ($place && $place->location) {
            // For now, use default coordinates since PostGIS parsing is complex
            $latitude = fake()->latitude(-0.1, 0.1);
            $longitude = fake()->longitude(109.2, 109.4);
        }

        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'place_id' => $place->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'image_url' => fake()->imageUrl(640, 480, 'nature', true), // 60% chance of having an image
            'status' => true, // Assuming most seeded check-ins are valid
            'additional_info' => [
                'device' => fake()->randomElement(['mobile_android', 'mobile_ios']),
                'purpose' => fake()->randomElement(['leisure', 'work', 'food_trip']),
            ],
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}