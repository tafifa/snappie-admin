<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Place;

class ReviewSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Create many sample reviews (realistic distribution: users review places they've been to)
    $createdUsers = User::all();
    $createdPlaces = Place::all();

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
            'image_urls' => $imageUrls,
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
        'image_urls' => $imageUrls,
        'created_at' => $reviewTime,
        'updated_at' => $reviewTime,
      ];
    }

    // Insert all reviews
    foreach (array_chunk($reviewData, 50) as $chunk) {
      Review::insert($chunk);
    }

    $this->command->info('Reviews created successfully (' . count($reviewData) . ' total)');
  }
}
