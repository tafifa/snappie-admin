<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\Place;
use App\Models\Checkin;
use App\Models\Review;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user if not exists
        Admin::firstOrCreate(
            ['email' => env('ADMIN_EMAIL')],
            [
                'name' => env('ADMIN_NAME'),
                'password' => bcrypt(env('ADMIN_PASSWORD')),
            ]
        );

        $this->command->info('Admin user created/verified successfully');

        // Create sample users
        $users = [
            [
                'name' => 'John Doe',
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'total_coin' => 1500,
                'total_exp' => 2400,
                'status' => true,
                'last_login_at' => now()->subDays(1),
            ],
            [
                'name' => 'Jane Smith',
                'username' => 'janesmith',
                'email' => 'jane@example.com',
                'total_coin' => 850,
                'total_exp' => 1200,
                'status' => true,
                'last_login_at' => now()->subHours(3),
            ],
            [
                'name' => 'Bob Wilson',
                'username' => 'bobwilson',
                'email' => 'bob@example.com',
                'total_coin' => 2200,
                'total_exp' => 3800,
                'status' => true,
                'last_login_at' => now()->subMinutes(30),
            ],
            [
                'name' => 'Alice Brown',
                'username' => 'alicebrown',
                'email' => 'alice@example.com',
                'total_coin' => 450,
                'total_exp' => 650,
                'status' => false,
                'last_login_at' => now()->subWeeks(2),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Users created successfully');

        // Create sample places
        $places = [
            [
                'name' => 'Warung Gudeg Bu Lindu',
                'slug' => 'warung-gudeg-bu-lindu',
                'category' => 'Traditional Food',
                'description' => 'Authentic Javanese gudeg with rich traditional taste. Family owned business since 1965.',
                'address' => 'Jl. Kaliurang KM 5, Yogyakarta',
                'latitude' => -7.7571,
                'longitude' => 110.3789,
                'image_urls' => [
                    'https://example.com/gudeg1.jpg',
                    'https://example.com/gudeg2.jpg'
                ],
                'status' => true,
                'partnership_status' => true,
                'clue_mission' => 'Find the traditional wooden sign with the family name',
                'exp_reward' => 100,
                'coin_reward' => 50,
                'additional_info' => [
                    'opening_hours' => '06:00 - 22:00',
                    'price_range' => 'Rp 15,000 - Rp 35,000',
                    'specialties' => ['Gudeg', 'Ayam Opor', 'Telur Pindang']
                ],
            ],
            [
                'name' => 'Sate Klathak Pak Pong',
                'slug' => 'sate-klathak-pak-pong',
                'category' => 'Grilled Food',
                'description' => 'Famous grilled goat satay with unique iron skewer technique',
                'address' => 'Jl. Imogiri Timur KM 8, Bantul, Yogyakarta',
                'latitude' => -7.8753,
                'longitude' => 110.4021,
                'image_urls' => [
                    'https://example.com/sate1.jpg',
                    'https://example.com/sate2.jpg'
                ],
                'status' => true,
                'partnership_status' => false,
                'clue_mission' => 'Look for the traditional grill with iron skewers',
                'exp_reward' => 120,
                'coin_reward' => 60,
                'additional_info' => [
                    'opening_hours' => '17:00 - 23:00',
                    'price_range' => 'Rp 25,000 - Rp 50,000',
                    'specialties' => ['Sate Klathak', 'Tongseng', 'Gulai']
                ],
            ],
            [
                'name' => 'Bakpia Pathok 25',
                'slug' => 'bakpia-pathok-25',
                'category' => 'Traditional Snacks',
                'description' => 'Legendary bakpia shop with authentic Javanese sweet filling',
                'address' => 'Jl. Malioboro No. 25, Yogyakarta',
                'latitude' => -7.7923,
                'longitude' => 110.3647,
                'image_urls' => [
                    'https://example.com/bakpia1.jpg'
                ],
                'status' => true,
                'partnership_status' => true,
                'clue_mission' => 'Find the traditional shop with number 25 signage',
                'exp_reward' => 80,
                'coin_reward' => 40,
                'additional_info' => [
                    'opening_hours' => '08:00 - 21:00',
                    'price_range' => 'Rp 20,000 - Rp 45,000',
                    'specialties' => ['Bakpia Kacang Hijau', 'Bakpia Keju', 'Bakpia Coklat']
                ],
            ],
            [
                'name' => 'Nasi Pecel Bu Tinuk',
                'slug' => 'nasi-pecel-bu-tinuk',
                'category' => 'Traditional Food',
                'description' => 'Traditional Javanese mixed rice with peanut sauce and vegetables',
                'address' => 'Pasar Kranggan, Yogyakarta',
                'latitude' => -7.7845,
                'longitude' => 110.3756,
                'image_urls' => [
                    'https://example.com/pecel1.jpg',
                    'https://example.com/pecel2.jpg',
                    'https://example.com/pecel3.jpg'
                ],
                'status' => false,
                'partnership_status' => false,
                'clue_mission' => 'Find the traditional market stall with green umbrella',
                'exp_reward' => 90,
                'coin_reward' => 45,
                'additional_info' => [
                    'opening_hours' => '06:00 - 14:00',
                    'price_range' => 'Rp 10,000 - Rp 25,000',
                    'specialties' => ['Nasi Pecel', 'Rempeyek', 'Es Dawet']
                ],
            ],
            [
                'name' => 'Kedai Kopi Klotok',
                'slug' => 'kedai-kopi-klotok',
                'category' => 'Cafe',
                'description' => 'Traditional coffee shop with authentic Javanese coffee brewing method',
                'address' => 'Jl. Prawirotaman No. 15, Yogyakarta',
                'latitude' => -7.8012,
                'longitude' => 110.3658,
                'image_urls' => [
                    'https://example.com/kopi1.jpg',
                    'https://example.com/kopi2.jpg'
                ],
                'status' => true,
                'partnership_status' => true,
                'clue_mission' => 'Find the vintage coffee grinder at the entrance',
                'exp_reward' => 75,
                'coin_reward' => 35,
                'additional_info' => [
                    'opening_hours' => '07:00 - 23:00',
                    'price_range' => 'Rp 8,000 - Rp 25,000',
                    'specialties' => ['Kopi Tubruk', 'Kopi Joss', 'Wedang Jahe']
                ],
            ],
            [
                'name' => 'Ayam Geprek Mas Kobis',
                'slug' => 'ayam-geprek-mas-kobis',
                'category' => 'Fast Food',
                'description' => 'Spicy smashed fried chicken with various sambal levels',
                'address' => 'Jl. Gejayan No. 88, Yogyakarta',
                'latitude' => -7.7689,
                'longitude' => 110.3801,
                'image_urls' => [
                    'https://example.com/geprek1.jpg'
                ],
                'status' => true,
                'partnership_status' => false,
                'clue_mission' => 'Look for the red chili pepper mascot statue',
                'exp_reward' => 85,
                'coin_reward' => 40,
                'additional_info' => [
                    'opening_hours' => '10:00 - 22:00',
                    'price_range' => 'Rp 12,000 - Rp 30,000',
                    'specialties' => ['Ayam Geprek Level 1-10', 'Nasi Bakar', 'Es Teh Manis']
                ],
            ],
            [
                'name' => 'Lesehan Jejamuran',
                'slug' => 'lesehan-jejamuran',
                'category' => 'Restaurant',
                'description' => 'Mushroom specialty restaurant with traditional lesehan dining style',
                'address' => 'Jl. Kaliurang KM 23, Pakem, Sleman',
                'latitude' => -7.6789,
                'longitude' => 110.4123,
                'image_urls' => [
                    'https://example.com/jamur1.jpg',
                    'https://example.com/jamur2.jpg',
                    'https://example.com/jamur3.jpg'
                ],
                'status' => true,
                'partnership_status' => true,
                'clue_mission' => 'Find the giant mushroom sculpture in the garden',
                'exp_reward' => 110,
                'coin_reward' => 55,
                'additional_info' => [
                    'opening_hours' => '11:00 - 21:00',
                    'price_range' => 'Rp 20,000 - Rp 60,000',
                    'specialties' => ['Sate Jamur', 'Tongseng Jamur', 'Sup Jamur Kuping']
                ],
            ],
            [
                'name' => 'Es Dawet Ayu Banjarnegara',
                'slug' => 'es-dawet-ayu-banjarnegara',
                'category' => 'Traditional Drinks',
                'description' => 'Authentic Banjarnegara dawet with coconut milk and palm sugar',
                'address' => 'Alun-alun Kidul, Yogyakarta',
                'latitude' => -7.8134,
                'longitude' => 110.3621,
                'image_urls' => [
                    'https://example.com/dawet1.jpg'
                ],
                'status' => true,
                'partnership_status' => false,
                'clue_mission' => 'Find the traditional cart with yellow umbrella',
                'exp_reward' => 60,
                'coin_reward' => 30,
                'additional_info' => [
                    'opening_hours' => '14:00 - 22:00',
                    'price_range' => 'Rp 5,000 - Rp 15,000',
                    'specialties' => ['Es Dawet Ayu', 'Es Cendol', 'Es Kelapa Muda']
                ],
            ],
            [
                'name' => 'Warung Makan Padang Sederhana',
                'slug' => 'warung-makan-padang-sederhana',
                'category' => 'Restaurant',
                'description' => 'Authentic Padang cuisine with rich spices and traditional cooking',
                'address' => 'Jl. Sudirman No. 45, Yogyakarta',
                'latitude' => -7.7956,
                'longitude' => 110.3695,
                'image_urls' => [
                    'https://example.com/padang1.jpg',
                    'https://example.com/padang2.jpg'
                ],
                'status' => true,
                'partnership_status' => true,
                'clue_mission' => 'Find the traditional Minang house architecture facade',
                'exp_reward' => 95,
                'coin_reward' => 50,
                'additional_info' => [
                    'opening_hours' => '08:00 - 21:00',
                    'price_range' => 'Rp 15,000 - Rp 45,000',
                    'specialties' => ['Rendang', 'Gulai Ayam', 'Dendeng Balado']
                ],
            ],
            [
                'name' => 'Mie Ayam Tumini',
                'slug' => 'mie-ayam-tumini',
                'category' => 'Fast Food',
                'description' => 'Famous chicken noodle soup with homemade noodles and secret recipe',
                'address' => 'Jl. Veteran No. 18, Yogyakarta',
                'latitude' => -7.7834,
                'longitude' => 110.3712,
                'image_urls' => [
                    'https://example.com/mieayam1.jpg'
                ],
                'status' => true,
                'partnership_status' => false,
                'clue_mission' => 'Look for the vintage noodle making machine display',
                'exp_reward' => 70,
                'coin_reward' => 35,
                'additional_info' => [
                    'opening_hours' => '09:00 - 20:00',
                    'price_range' => 'Rp 8,000 - Rp 20,000',
                    'specialties' => ['Mie Ayam Bakso', 'Mie Ayam Pangsit', 'Es Jeruk']
                ],
            ],
        ];

        foreach ($places as $placeData) {
            Place::firstOrCreate(
                ['slug' => $placeData['slug']],
                $placeData
            );
        }

        $this->command->info('Places created successfully');

        // Create many sample check-ins (realistic distribution: each user visits multiple places multiple times)
        $createdUsers = User::all();
        $createdPlaces = Place::all();

        $this->command->info('Creating check-ins...');

        // Generate 50+ check-ins across users and places
        $checkinData = [];
        $statuses = ['done', 'pending', 'notdone'];
        $missionStatuses = ['completed', 'pending', 'failed'];

        foreach ($createdUsers as $user) {
            foreach ($createdPlaces as $place) {
                // Each user visits each place 2-4 times
                $visitCount = rand(2, 4);
                
                for ($i = 0; $i < $visitCount; $i++) {
                    $checkInStatus = $statuses[array_rand($statuses)];
                    $missionStatus = $missionStatuses[array_rand($missionStatuses)];
                    $checkinTime = now()->subDays(rand(1, 30))->subHours(rand(0, 23));
                    
                    $checkinData[] = [
                        'user_id' => $user->id,
                        'place_id' => $place->id,
                        'time' => $checkinTime,
                        'location' => json_encode([
                            'latitude' => $place->latitude + (rand(-100, 100) / 10000), // Small variation
                            'longitude' => $place->longitude + (rand(-100, 100) / 10000)
                        ]),
                        'check_in_status' => $checkInStatus,
                        'mission_status' => $missionStatus,
                        'mission_completed_at' => $missionStatus === 'completed' ? $checkinTime->addMinutes(rand(5, 30)) : null,
                        'mission_image_url' => $missionStatus === 'completed' ? 'https://example.com/mission_' . rand(1, 20) . '.jpg' : null,
                        'created_at' => $checkinTime,
                        'updated_at' => $checkinTime,
                    ];
                }
            }
        }

        // Add additional random check-ins to make it more realistic
        for ($i = 0; $i < 60; $i++) {
            $user = $createdUsers->random();
            $place = $createdPlaces->random();
            $checkInStatus = $statuses[array_rand($statuses)];
            $missionStatus = $missionStatuses[array_rand($missionStatuses)];
            $checkinTime = now()->subDays(rand(1, 60))->subHours(rand(0, 23));
            
            $checkinData[] = [
                'user_id' => $user->id,
                'place_id' => $place->id,
                'time' => $checkinTime,
                'location' => json_encode([
                    'latitude' => $place->latitude + (rand(-100, 100) / 10000),
                    'longitude' => $place->longitude + (rand(-100, 100) / 10000)
                ]),
                'check_in_status' => $checkInStatus,
                'mission_status' => $missionStatus,
                'mission_completed_at' => $missionStatus === 'completed' ? $checkinTime->addMinutes(rand(5, 30)) : null,
                'mission_image_url' => $missionStatus === 'completed' ? 'https://example.com/mission_' . rand(1, 20) . '.jpg' : null,
                'created_at' => $checkinTime,
                'updated_at' => $checkinTime,
            ];
        }

        // Insert all check-ins
        foreach (array_chunk($checkinData, 50) as $chunk) {
            Checkin::insert($chunk);
        }

        $this->command->info('Check-ins created successfully (' . count($checkinData) . ' total)');

        // Create many sample reviews (realistic distribution: users review places they've been to)
        $this->command->info('Creating reviews...');
        
        $reviewData = [];
        $reviewStatuses = ['approved', 'pending', 'rejected'];
        $reviewContents = [
            'Amazing food! Highly recommended for authentic taste.',
            'Great place with friendly service. Will come back again.',
            'Good food but the waiting time was quite long.',
            'Authentic traditional taste, worth the visit.',
            'The best in town! Perfect spices and fresh ingredients.',
            'Decent food, reasonable price. Nice atmosphere.',
            'Outstanding quality and portion. Very satisfied.',
            'Traditional recipe that brings back childhood memories.',
            'Clean place with hygienic food preparation.',
            'Unique taste that you can\'t find elsewhere.',
            'Family-friendly place with comfortable seating.',
            'Fast service and delicious food. Recommended!',
            'Average taste, nothing special but okay.',
            'Overpriced for the portion and quality.',
            'Too crowded during peak hours.',
            'Disappointing experience, food was cold.',
            'Not as good as expected from the reviews.',
            'Poor service and long waiting time.',
        ];

        foreach ($createdUsers as $user) {
            foreach ($createdPlaces as $place) {
                // Each user reviews 60-80% of places they visit
                if (rand(1, 10) <= 7) {
                    $vote = rand(1, 5);
                    $status = $reviewStatuses[array_rand($reviewStatuses)];
                    $content = $reviewContents[array_rand($reviewContents)];
                    $reviewTime = now()->subDays(rand(1, 45))->subHours(rand(0, 23));
                    
                    // Add more detailed content for higher ratings
                    if ($vote >= 4) {
                        $content .= ' The ingredients are fresh and the cooking method is traditional.';
                    } elseif ($vote <= 2) {
                        $content .= ' Needs improvement in taste and service quality.';
                    }
                    
                    $imageUrls = [];
                    // 40% chance of having images
                    if (rand(1, 10) <= 4) {
                        $imageCount = rand(1, 3);
                        for ($j = 0; $j < $imageCount; $j++) {
                            $imageUrls[] = 'https://example.com/review_' . rand(1, 100) . '.jpg';
                        }
                    }
                    
                    $reviewData[] = [
                        'user_id' => $user->id,
                        'place_id' => $place->id,
                        'content' => $content,
                        'vote' => $vote,
                        'status' => $status,
                        'image_urls' => json_encode($imageUrls),
                        'created_at' => $reviewTime,
                        'updated_at' => $reviewTime,
                    ];
                }
            }
        }

        // Add more random reviews from different combinations
        for ($i = 0; $i < 80; $i++) {
            $user = $createdUsers->random();
            $place = $createdPlaces->random();
            $vote = rand(1, 5);
            $status = $reviewStatuses[array_rand($reviewStatuses)];
            $content = $reviewContents[array_rand($reviewContents)];
            $reviewTime = now()->subDays(rand(1, 90))->subHours(rand(0, 23));
            
            $imageUrls = [];
            if (rand(1, 10) <= 3) {
                $imageCount = rand(1, 2);
                for ($j = 0; $j < $imageCount; $j++) {
                    $imageUrls[] = 'https://example.com/review_' . rand(1, 100) . '.jpg';
                }
            }
            
            $reviewData[] = [
                'user_id' => $user->id,
                'place_id' => $place->id,
                'content' => $content,
                'vote' => $vote,
                'status' => $status,
                'image_urls' => json_encode($imageUrls),
                'created_at' => $reviewTime,
                'updated_at' => $reviewTime,
            ];
        }

        // Insert all reviews
        foreach (array_chunk($reviewData, 50) as $chunk) {
            Review::insert($chunk);
        }

        $this->command->info('Reviews created successfully (' . count($reviewData) . ' total)');

        // Display final statistics
        $this->command->info('=== FINAL STATISTICS ===');
        $this->command->info('Users: ' . User::count());
        $this->command->info('Places: ' . Place::count());
        $this->command->info('Check-ins: ' . Checkin::count());
        $this->command->info('Reviews: ' . Review::count());
        $this->command->info('========================');

        $this->command->info('All sample data created successfully!');
    }
}
