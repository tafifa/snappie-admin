<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Create sample achievements
    $achievements = [
      [
        'name' => 'Perintis Pontianak',
        'description' => 'Berhasil melakukan check-in untuk pertama kalinya.',
        'image_url' => 'https://example.com/images/achievements/first_checkin.png',
        'coin_reward' => 50,
        'exp_reward' => 100,
      ],
      [
        'name' => 'Penjelajah Kota',
        'description' => 'Check-in di 10 lokasi berbeda di Pontianak.',
        'image_url' => 'https://example.com/images/achievements/explorer.png',
        'coin_reward' => 200,
        'exp_reward' => 500,
      ],
      [
        'name' => 'Kritikus Andal',
        'description' => 'Menulis 5 ulasan untuk tempat yang berbeda.',
        'image_url' => 'https://example.com/images/achievements/critic.png',
        'coin_reward' => 150,
        'exp_reward' => 300,
      ],
      [
        'name' => 'Jiwa Sosialita',
        'description' => 'Mengikuti 10 pengguna lain.',
        'image_url' => 'https://example.com/images/achievements/socialite.png',
        'coin_reward' => 100,
        'exp_reward' => 200,
      ],
      [
        'name' => 'Fotografer Jalanan',
        'description' => 'Membuat 5 post dengan gambar.',
        'image_url' => 'https://example.com/images/achievements/photographer.png',
        'coin_reward' => 150,
        'exp_reward' => 400,
      ],
    ];

    foreach ($achievements as $achievementData) {
      Achievement::factory()->create($achievementData);
    }

    $this->command->info('Achievements created successfully');
  }
}
