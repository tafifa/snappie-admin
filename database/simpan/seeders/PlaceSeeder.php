<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Place;

class PlaceSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
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
        'avg_rating' => 4.5,
        'total_reviews' => 150,
        'total_checkins' => 300,
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
        'avg_rating' => 4.8,
        'total_reviews' => 250,
        'total_checkins' => 500,
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
        'avg_rating' => 4.6,
        'total_reviews' => 180,
        'total_checkins' => 400,
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
        'avg_rating' => 4.3,
        'total_reviews' => 90,
        'total_checkins' => 200,
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
        'avg_rating' => 4.7,
        'total_reviews' => 220,
        'total_checkins' => 450,
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
        'avg_rating' => 4.4,
        'total_reviews' => 190,
        'total_checkins' => 380,
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
        'avg_rating' => 4.9,
        'total_reviews' => 300,
        'total_checkins' => 600,
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
        'avg_rating' => 4.2,
        'total_reviews' => 80,
        'total_checkins' => 150,
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
        'avg_rating' => 4.6,
        'total_reviews' => 200,
        'total_checkins' => 420,
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
        'avg_rating' => 4.5,
        'total_reviews' => 170,
        'total_checkins' => 350,
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
  }
}
