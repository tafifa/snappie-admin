<?php

namespace Database\Factories;

use FakerRestaurant\Provider\id_ID\Restaurant;
use Smknstd\FakerPicsumImages\FakerPicsumImagesProvider;
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
        $faker->addProvider(new FakerPicsumImagesProvider($faker));

        $minPrice = fake()->numberBetween(1, 10) * 10000; // e.g., 10000 to 100000
        $latitude = fake()->latitude(-0.1, 0.1); // Coordinates around Pontianak
        $longitude = fake()->longitude(109.2, 109.4); // Coordinates around Pontianak
        $foodTypeOptions = [
            'Non-Sup',
            'Mi Instan',
            'Menu Komposit',
            'Sup/Soto',
            'Menu Campuran',
            'Minuman dan Tambahan',
            'Liwetan',
            'Gaya Padang',
            'Gaya Tionghoa',
            'Makanan Cepat Saji',
            'Makanan Tradisional',
            'Makanan Kemasan',
            'Buah-buahan'
        ];

        $placeValueOptions = [
            'Harga Terjangkau',
            'Rasa Autentik',
            'Menu Unik/Variasi',
            'Buka 24 Jam',
            'Jaringan Lancar',
            'Estetika/Instagrammable',
            'Suasana Tenang',
            'Suasana Homey',
            'Bersejarah/Tradisional',
            'Pet Friendly',
            'Ramah Keluarga',
            'Pelayanan Ramah',
            'Rapat/Diskusi',
            'Nongkrong',
            'Work From Cafe'
        ];

        return [
            'name' => fake('id_ID')->company(),
            'description' => fake()->paragraph(3),
            'longitude' => $longitude,
            'latitude' => $latitude,
            'image_urls' => [
                $faker->imageUrl(640, 480),
                $faker->imageUrl(640, 480),
                $faker->imageUrl(640, 480),
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
                    'opening_hours' => fake()->time('H:i', '12:00'),
                    'closing_hours' => fake()->time('H:i', '23:59'),
                    'opening_days' => [fake()->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']), fake()->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
                    'contact_number' => fake('id_ID')->phoneNumber(),
                    'website' => fake()->domainName(),
                ],
                'place_value' => fake()->randomElements($placeValueOptions, 4),
                'food_type' => fake()->randomElements($foodTypeOptions, 4),
                'menu_image_url' => $faker->imageUrl(640, 480),
                'menu' => collect(range(1, fake()->numberBetween(1, 3)))->map(function () use ($faker, $minPrice) {
                    return [
                        'name' => $faker->foodName(),
                        'image_url' => $faker->imageUrl(640, 480),
                        'price' => fake()->numberBetween($minPrice, $minPrice + 20000),
                        'description' => fake()->sentence(3),
                    ];
                })->toArray(),
                'place_attributes' => [
                    'facility' => collect(range(1, fake()->numberBetween(1, 5)))->map(function () {
                        $facilities = ['WiFi Gratis', 'AC', 'Musholla', 'Toilet Bersih', 'Smoking Area', 'Outdoor Seating', 'Indoor Seating', 'Colokan Listrik', 'USB Charging Port', 'TV', 'Music Player', 'Playground Anak'];
                        return [
                            'name' => fake()->randomElement($facilities),
                            'description' => fake()->sentence(5),
                        ];
                    })->toArray(),
                    'parking' => collect(range(1, fake()->numberBetween(1, 5)))->map(function () {
                        $parkings = ['Parkir Motor', 'Parkir Mobil', 'Valet Parking', 'Parkir Gratis', 'Parkir Berbayar', 'Parkir Luas', 'Parkir Terbatas'];
                        return [
                            'name' => fake()->randomElement($parkings),
                            'description' => fake()->sentence(4),
                        ];
                    })->toArray(),
                    'capacity' => collect(range(1, fake()->numberBetween(1, 5)))->map(function () {
                        $capacities = ['Meja Kecil (2-4 orang)', 'Meja Sedang (5-8 orang)', 'Meja Besar (9-12 orang)', 'Private Room', 'Outdoor Table', 'Counter Seat'];
                        return [
                            'name' => fake()->randomElement($capacities),
                            'description' => fake()->sentence(4),
                        ];
                    })->toArray(),
                    'accessibility' => collect(range(1, fake()->numberBetween(1, 5)))->map(function () {
                        $accessibilities = ['Wheelchair Accessible', 'Ramp/Landai', 'Lift/Elevator', 'Toilet Difabel', 'Jalur Khusus Difabel', 'Kursi Roda Tersedia'];
                        return [
                            'name' => fake()->randomElement($accessibilities),
                            'description' => fake()->sentence(4),
                        ];
                    })->toArray(),
                    'payment' => collect(range(1, fake()->numberBetween(1, 5)))->map(function () {
                        $payments = ['Tunai', 'Debit Card', 'Credit Card', 'QRIS', 'GoPay', 'OVO', 'Dana', 'ShopeePay', 'LinkAja'];
                        return [
                            'name' => fake()->randomElement($payments),
                            'description' => fake()->sentence(3),
                        ];
                    })->toArray(),
                    'service' => collect(range(1, fake()->numberBetween(1, 5)))->map(function () {
                        $services = ['Dine In', 'Take Away', 'Delivery', 'Drive Thru', 'Reservation', 'Catering', 'Private Event', 'Live Music'];
                        return [
                            'name' => fake()->randomElement($services),
                            'description' => fake()->sentence(4),
                        ];
                    })->toArray(),
                ],
            ],
            'created_at' => fake()->dateTimeThisYear(),
            'updated_at' => fn(array $attributes) => $attributes['created_at'],
        ];
    }
}