<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Challenge;

class ChallengeSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Create sample challenges
    $challenges = [
      [
        'name' => 'Check-in Harian',
        'description' => 'Lakukan check-in di lokasi mana pun hari ini untuk mendapatkan hadiah!',
        'image_url' => 'https://example.com/images/challenges/daily_checkin.png',
        'coin_reward' => 25,
        'exp_reward' => 50,
        'started_at' => now()->startOfDay(),
        'ended_at' => now()->endOfDay(),
        'challenge_type' => 'daily',
      ],
      [
        'name' => 'Petualang Mingguan',
        'description' => 'Kunjungi dan check-in di 5 lokasi berbeda minggu ini.',
        'image_url' => 'https://example.com/images/challenges/weekly_explorer.png',
        'coin_reward' => 200,
        'exp_reward' => 500,
        'started_at' => now()->startOfWeek(),
        'ended_at' => now()->endOfWeek(),
        'challenge_type' => 'weekly',
      ],
      [
        'name' => 'Pesta Kuliner Pontianak',
        'description' => 'Check-in di 3 tempat makan yang berbeda dan berikan ulasan.',
        'image_url' => 'https://example.com/images/challenges/foodie_fest.png',
        'coin_reward' => 300,
        'exp_reward' => 750,
        'started_at' => now()->startOfMonth(),
        'ended_at' => now()->endOfMonth(),
        'challenge_type' => 'special',
      ],
      [
        'name' => 'Tantangan Kopi Nusantara',
        'description' => 'Kunjungi 2 kedai kopi yang menjadi partner kami bulan ini.',
        'image_url' => 'https://example.com/images/challenges/coffee_challenge.png',
        'coin_reward' => 150,
        'exp_reward' => 400,
        'started_at' => now()->subWeek()->startOfDay(), // Challenge from last week
        'ended_at' => now()->subWeek()->endOfDay(),
        'challenge_type' => 'special',
        'status' => false, // Inactive/ended
      ],
      [
        'name' => 'Tantangan Kuliner',
        'description' => 'Kunjungi 2 restoran yang berbeda dan berikan ulasan.',
        'image_url' => 'https://example.com/images/challenges/foodie_challenge.png',
        'coin_reward' => 150,
        'exp_reward' => 400,
        'started_at' => now()->subWeek()->startOfDay(), // Challenge from last week
        'ended_at' => now()->subWeek()->endOfDay(),
        'challenge_type' => 'special',
        'status' => false, // Inactive/ended
      ]
    ];

    foreach ($challenges as $challengeData) {
      Challenge::factory()->create($challengeData);
    }

    $this->command->info('Challenges created successfully');
  }
}
