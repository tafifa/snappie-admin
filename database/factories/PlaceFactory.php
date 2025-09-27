<?php

namespace Database\Factories;

use FakerRestaurant\Provider\id_ID\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('id_ID');
        $faker->addProvider(new Restaurant($faker));

        $minPrice = fake()->numberBetween(1, 10) * 10000; // e.g., 10000 to 100000
        $latitude = fake()->latitude(-0.1, 0.1); // Coordinates around Pontianak
        $longitude = fake()->longitude(109.2, 109.4); // Coordinates around Pontianak
        $placeValueOptions = ['Harga Terjangkau', 'Rasa Autentik', 'Menu Bervariasi', 'Buka 24 Jam', 'Jaringan Lancar', 'Estetika', 'Suasana Tenang', 'Suasana Tradisional', 'Suasana Homey', 'Pet Friendly', 'Ramah Keluarga', 'Pelayanan Ramah', 'Cocok untuk Nongkrong', 'Cocok untuk Work From Cafe', 'Tempat Bersejarah'];
        $foodTypeOptions = ['Nusantara', 'Internasional', 'Seafood', 'Kafein', 'Non-Kafein', 'Vegetarian', 'Dessert', 'Makanan Ringan', 'Pastry'];

        return [
            'name' => fake('id_ID')->company(),
            'description' => fake()->paragraph(3),
            'longitude' => $longitude,
            'latitude' => $latitude,
            'image_urls' => [
                fake()->imageUrl(640, 480, 'nature', true),
                fake()->imageUrl(640, 480, 'city', true),
                fake()->imageUrl(640, 480, 'food', true),
            ],
            'coin_reward' => fake()->numberBetween(10, 200),
            'exp_reward' => fake()->numberBetween(50, 500),
            'min_price' => $minPrice,
            'max_price' => $minPrice + fake()->numberBetween(2, 20) * 10000,
            'avg_rating' => fake()->numberBetween(3, 5),
            'total_review' => fake()->numberBetween(20, 1500), // Fixed field name
            'total_checkin' => fake()->numberBetween(100, 5000), // Fixed field name
            'status' => fake()->boolean(95), // 95% active
            'partnership_status' => fake()->boolean(20), // 20% are partners
            'additional_info' => [
                'place_detail' => [
                    'short_description' => fake()->sentence(6),
                    'address' => fake()->address(),
                    'opening_hours' => fake()->time('H:i', '06:00', '12:00'),
                    'closing_hours' => fake()->time('H:i', '17:00', '23:59'),
                    'opening_days' => [fake()->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']), fake()->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
                    'contact_number' => fake('id_ID')->phoneNumber(),
                    'website' => fake()->domainName(),
                ],
                'place_value' => fake()->randomElements($placeValueOptions, fake()->numberBetween(2, 4)),
                'food_type' =>  fake()->randomElements($foodTypeOptions, fake()->numberBetween(2, 4)),
                'place_attributes' => [
                    'menu' => [
                        'favorite1' => [
                            'name' => $faker->foodName(),
                            'image_url' => fake()->imageUrl(640, 480, 'food', true),
                            'price' => fake()->numberBetween($minPrice, $minPrice + 20000),
                            'description' => fake()->sentence(3),
                        ],
                        'favorite2' => [
                            'name' => $faker->foodName(),
                            'image_url' => fake()->imageUrl(640, 480, 'food', true),
                            'price' => fake()->numberBetween($minPrice, $minPrice + 20000),
                            'description' => fake()->sentence(3),
                        ],
                    ],
                    'menu_image_url' => fake()->imageUrl(640, 480, 'drinks', true),
                    'facility' => [],
                    'parking' => [],
                    'capacity' => [],
                    'accessibility' => [],
                    'payment' => [],
                    'service' => []
                ],
            ],
            'created_at' => fake()->dateTimeThisYear(),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}