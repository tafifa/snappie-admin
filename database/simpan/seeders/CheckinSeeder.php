<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Checkin;
use App\Models\User;
use App\Models\Place;

class CheckinSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
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
            'location' => [
              'latitude' => $place->latitude + (rand(-100, 100) / 10000), // Small variation
              'longitude' => $place->longitude + (rand(-100, 100) / 10000)
            ],
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
        'location' => [
          'latitude' => $place->latitude + (rand(-100, 100) / 10000),
          'longitude' => $place->longitude + (rand(-100, 100) / 10000)
        ],
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
  }
}
