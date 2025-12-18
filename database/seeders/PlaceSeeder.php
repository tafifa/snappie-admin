<?php

namespace Database\Seeders;

use App\Models\Place;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Hapus data lama (opsional)
            // Place::truncate();

            // =========================================================================
            // 1. Sagarmatha Coffee Bar (The True Hidden Gem)
            // =========================================================================
            Place::create([
                'name' => 'Sagarmatha Coffee Bar',
                'description' => 'Hidden gem sesungguhnya di Pontianak. Berlokasi di rooftop gang sempit Sungai Jawi, tempat ini menawarkan suasana slow bar yang intim dengan pemandangan sunset kota yang syahdu.',
                'longitude' => 109.3155,
                'latitude' => -0.0289,
                'image_urls' => ['https://via.placeholder.com/600x400?text=Sagarmatha+Rooftop', 'https://via.placeholder.com/600x400?text=Sagarmatha+Sunset'],
                'coin_reward' => 30,
                'exp_reward' => 150, // High XP for hidden location
                'min_price' => 20000,
                'max_price' => 45000,
                'avg_rating' => 4.8,
                'total_review' => 120,
                'total_checkin' => 340,
                'status' => true,
                'partnership_status' => true,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Rooftop slow bar dengan pemandangan sunset terbaik di Pontianak.',
                        'address' => 'Jl. H. Rais A. Rachman, Gg. Selamat 3 No. 36B, Pontianak Kota.',
                        'opening_hours' => '16:00',
                        'closing_hours' => '23:00',
                        'opening_days' => ['Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0812-3456-7890', // Placeholder
                        'website' => 'https://instagram.com/sagarmatha.coffee',
                    ],
                    'place_value' => ['Hidden Gem', 'Estetika', 'Suasana Tenang', 'Cocok untuk Nongkrong'],
                    'food_type' => ['Coffee', 'Mocktail', 'Snack'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+Sagarmatha',
                    'menu' => [
                        ['name' => 'V60 Manual Brew', 'image_url' => 'https://via.placeholder.com/150', 'price' => 25000, 'description' => 'Seduhan kopi manual dengan beans pilihan.'],
                        ['name' => 'Sunset Mocktail', 'image_url' => 'https://via.placeholder.com/150', 'price' => 28000, 'description' => 'Campuran segar soda dan buah tropis.'],
                        ['name' => 'Cireng Rujak', 'image_url' => 'https://via.placeholder.com/150', 'price' => 18000, 'description' => 'Camilan gurih dengan bumbu rujak pedas.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'Outdoor Seating', 'description' => 'Area rooftop terbuka.'],
                            ['name' => 'WiFi Gratis', 'description' => 'Koneksi stabil.'],
                            ['name' => 'Toilet Bersih', 'description' => 'Tersedia di lantai bawah.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Motor', 'description' => 'Area parkir terbatas di dalam gang.']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Kecil (2-4 orang)', 'description' => 'Cocok untuk pasangan atau grup kecil.'],
                            ['name' => 'Counter Seat', 'description' => 'Duduk di depan barista.']
                        ],
                        'accessibility' => [
                            ['name' => 'Tangga', 'description' => 'Akses ke rooftop menggunakan tangga putar (kurang ramah kursi roda).']
                        ],
                        'payment' => [
                            ['name' => 'QRIS', 'description' => 'Scan QR tersedia.'],
                            ['name' => 'Tunai', 'description' => 'Menerima cash.']
                        ],
                        'service' => [
                            ['name' => 'Dine In', 'description' => 'Wajib makan di tempat.'],
                            ['name' => 'Private Event', 'description' => 'Bisa booking satu rooftop.']
                        ]
                    ]
                ]
            ]);

            // =========================================================================
            // 2. 2818 Coffee Roasters (Specialty Coffee)
            // =========================================================================
            Place::create([
                'name' => '2818 Coffee Roasters',
                'description' => 'Tempat serius untuk pecinta kopi. Mengusung konsep industrial di area perumahan, tempat ini me-roasting biji kopi mereka sendiri. Vibe-nya tenang, cocok untuk WFC.',
                'longitude' => 109.3321,
                'latitude' => -0.0450,
                'image_urls' => ['https://via.placeholder.com/600x400?text=2818+Roastery'],
                'coin_reward' => 40,
                'exp_reward' => 80,
                'min_price' => 25000,
                'max_price' => 60000,
                'avg_rating' => 4.7,
                'total_review' => 210,
                'total_checkin' => 560,
                'status' => true,
                'partnership_status' => true,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Roastery dan coffee shop industrial yang nyaman untuk bekerja.',
                        'address' => 'Jl. Johar No. 28, Pontianak.',
                        'opening_hours' => '08:00',
                        'closing_hours' => '22:00',
                        'opening_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0811-0000-2818',
                        'website' => 'https://instagram.com/2818coffeeroasters',
                    ],
                    'place_value' => ['Rasa Autentik', 'Cocok untuk Work From Cafe', 'Jaringan Lancar', 'Suasana Tenang'],
                    'food_type' => ['Coffee', 'Pastry', 'Main Course'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+2818',
                    'menu' => [
                        ['name' => 'Magic Latte', 'image_url' => 'https://via.placeholder.com/150', 'price' => 32000, 'description' => 'Double ristretto dengan susu creamy.'],
                        ['name' => 'Croissant Butter', 'image_url' => 'https://via.placeholder.com/150', 'price' => 25000, 'description' => 'Pastry renyah teman ngopi.'],
                        ['name' => 'Japanese Iced Coffee', 'image_url' => 'https://via.placeholder.com/150', 'price' => 30000, 'description' => 'Segar dan fruity.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'AC', 'description' => 'Ruangan dingin dan nyaman.'],
                            ['name' => 'Colokan Listrik', 'description' => 'Tersedia di setiap meja.'],
                            ['name' => 'WiFi Gratis', 'description' => 'Internet kecepatan tinggi.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Mobil', 'description' => 'Area parkir cukup luas.'],
                            ['name' => 'Parkir Motor', 'description' => 'Tersedia.']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Sedang (5-8 orang)', 'description' => 'Untuk meeting kecil.'],
                            ['name' => 'Meja Kecil (2-4 orang)', 'description' => 'Untuk kerja sendiri.']
                        ],
                        'accessibility' => [
                            ['name' => 'Ramp/Landai', 'description' => 'Akses masuk mudah.']
                        ],
                        'payment' => [
                            ['name' => 'Debit Card', 'description' => 'Bisa gesek.'],
                            ['name' => 'QRIS', 'description' => 'Scan QR.']
                        ],
                        'service' => [
                            ['name' => 'Take Away', 'description' => 'Tersedia.'],
                            ['name' => 'Dine In', 'description' => 'Suasana kondusif.']
                        ]
                    ]
                ]
            ]);

            // =========================================================================
            // 3. Ningrat Eatery (Modern Indonesian)
            // =========================================================================
            Place::create([
                'name' => 'Ningrat Eatery',
                'description' => 'Restoran modern yang menaikkan kelas kuliner Nusantara. Nasi goreng dan bakso disajikan dengan plating estetik. Tempat luas, cocok untuk makan bersama keluarga.',
                'longitude' => 109.3288,
                'latitude' => -0.0355,
                'image_urls' => ['https://via.placeholder.com/600x400?text=Ningrat+Interior'],
                'coin_reward' => 60,
                'exp_reward' => 50,
                'min_price' => 30000,
                'max_price' => 80000,
                'avg_rating' => 4.6,
                'total_review' => 450,
                'total_checkin' => 1200,
                'status' => true,
                'partnership_status' => true,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Restoran keluarga dengan menu Nusantara modern.',
                        'address' => 'Jl. Karimata No. 5, Pontianak.',
                        'opening_hours' => '10:00',
                        'closing_hours' => '22:00',
                        'opening_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0821-9999-8888',
                        'website' => 'https://instagram.com/ningrateatery',
                    ],
                    'place_value' => ['Ramah Keluarga', 'Menu Bervariasi', 'Pelayanan Ramah', 'Estetika'],
                    'food_type' => ['Indonesian', 'Asian', 'Dessert'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+Ningrat',
                    'menu' => [
                        ['name' => 'Nasi Goreng Ningrat', 'image_url' => 'https://via.placeholder.com/150', 'price' => 35000, 'description' => 'Nasi goreng spesial dengan sate.'],
                        ['name' => 'Bakso Beranak', 'image_url' => 'https://via.placeholder.com/150', 'price' => 40000, 'description' => 'Bakso besar isi bakso kecil.'],
                        ['name' => 'Es Campur Royal', 'image_url' => 'https://via.placeholder.com/150', 'price' => 25000, 'description' => 'Segar dengan topping melimpah.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'Musholla', 'description' => 'Luas dan bersih.'],
                            ['name' => 'AC', 'description' => 'Full AC.'],
                            ['name' => 'Playground Anak', 'description' => 'Area bermain kecil.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Mobil', 'description' => 'Sangat luas.'],
                            ['name' => 'Valet Parking', 'description' => 'Opsional saat ramai.']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Besar (9-12 orang)', 'description' => 'Untuk rombongan keluarga.'],
                            ['name' => 'Private Room', 'description' => 'Untuk acara privat.']
                        ],
                        'accessibility' => [
                            ['name' => 'Toilet Difabel', 'description' => 'Tersedia.']
                        ],
                        'payment' => [
                            ['name' => 'Credit Card', 'description' => 'Bisa.'],
                            ['name' => 'Tunai', 'description' => 'Bisa.']
                        ],
                        'service' => [
                            ['name' => 'Reservation', 'description' => 'Bisa booking tempat.'],
                            ['name' => 'Catering', 'description' => 'Menerima pesanan box.']
                        ]
                    ]
                ]
            ]);

            // =========================================================================
            // 4. Tokokopi ODS (Youth Hangout)
            // =========================================================================
            Place::create([
                'name' => 'Tokokopi ODS',
                'description' => 'Singkatan dari "Orang Dalam Sini". Spot nongkrong favorit anak muda dengan konsep unfinished industrial yang santai.',
                'longitude' => 109.3422,
                'latitude' => -0.0311,
                'image_urls' => ['https://via.placeholder.com/600x400?text=ODS+Coffee'],
                'coin_reward' => 20,
                'exp_reward' => 60,
                'min_price' => 18000,
                'max_price' => 40000,
                'avg_rating' => 4.5,
                'total_review' => 890,
                'total_checkin' => 2100,
                'status' => true,
                'partnership_status' => true,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Tempat nongkrong asik "Orang Dalam Sini" dengan kopi susu creamy.',
                        'address' => 'Jl. Sepakat 2, Pontianak.',
                        'opening_hours' => '07:00',
                        'closing_hours' => '23:00',
                        'opening_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0813-XXXX-XXXX',
                        'website' => 'https://instagram.com/tokokopiods',
                    ],
                    'place_value' => ['Harga Terjangkau', 'Cocok untuk Nongkrong', 'Buka 24 Jam', 'Suasana Tenang'],
                    'food_type' => ['Coffee', 'Toast'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+ODS',
                    'menu' => [
                        ['name' => 'Es Kopi ODS', 'image_url' => 'https://via.placeholder.com/150', 'price' => 20000, 'description' => 'Signature kopi susu gula aren.'],
                        ['name' => 'Roti Bakar Coklat', 'image_url' => 'https://via.placeholder.com/150', 'price' => 18000, 'description' => 'Roti bakar tebal topping melimpah.'],
                        ['name' => 'Sea Salt Latte', 'image_url' => 'https://via.placeholder.com/150', 'price' => 25000, 'description' => 'Kopi susu dengan foam asin gurih.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'Smoking Area', 'description' => 'Area outdoor luas.'],
                            ['name' => 'WiFi Gratis', 'description' => 'Kencang.'],
                            ['name' => 'Colokan Listrik', 'description' => 'Banyak tersedia.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Motor', 'description' => 'Sangat luas.']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Kecil (2-4 orang)', 'description' => 'Banyak meja kecil.'],
                            ['name' => 'Outdoor Table', 'description' => 'Konsep teras.']
                        ],
                        'accessibility' => [
                            ['name' => 'Rata Tanah', 'description' => 'Akses mudah.']
                        ],
                        'payment' => [
                            ['name' => 'ShopeePay', 'description' => 'Scan QR.'],
                            ['name' => 'Tunai', 'description' => 'Bisa.']
                        ],
                        'service' => [
                            ['name' => 'Take Away', 'description' => 'Cup sealer praktis.'],
                            ['name' => 'Dine In', 'description' => 'Self service.']
                        ]
                    ]
                ]
            ]);

            // =========================================================================
            // 5. House of Tahron (Homey Vintage)
            // =========================================================================
            Place::create([
                'name' => 'House of Tahron',
                'description' => 'Rumah hunian yang disulap menjadi restoran Western & Pasta yang sangat homey. Serasa makan di rumah nenek tapi dengan kualitas makanan bintang lima.',
                'longitude' => 109.3305,
                'latitude' => -0.0401,
                'image_urls' => ['https://via.placeholder.com/600x400?text=House+of+Tahron'],
                'coin_reward' => 80,
                'exp_reward' => 70,
                'min_price' => 35000,
                'max_price' => 100000,
                'avg_rating' => 4.7,
                'total_review' => 150,
                'total_checkin' => 400,
                'status' => true,
                'partnership_status' => false,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Home-restaurant dengan menu Western dan Pasta otentik.',
                        'address' => 'Jl. Tani Makmur, Pontianak.',
                        'opening_hours' => '11:00',
                        'closing_hours' => '21:00',
                        'opening_days' => ['Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0812-XXXX-XXXX',
                        'website' => 'https://',
                    ],
                    'place_value' => ['Suasana Homey', 'Rasa Autentik', 'Estetika', 'Hidden Gem'],
                    'food_type' => ['Western', 'Pasta', 'Steak'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+Tahron',
                    'menu' => [
                        ['name' => 'Aglio Olio', 'image_url' => 'https://via.placeholder.com/150', 'price' => 45000, 'description' => 'Pasta pedas gurih dengan udang.'],
                        ['name' => 'Sirloin Steak', 'image_url' => 'https://via.placeholder.com/150', 'price' => 95000, 'description' => 'Daging sapi impor juicy.'],
                        ['name' => 'Mushroom Soup', 'image_url' => 'https://via.placeholder.com/150', 'price' => 35000, 'description' => 'Sup krim jamur kental.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'Indoor Seating', 'description' => 'Ruang tamu yang nyaman.'],
                            ['name' => 'AC', 'description' => 'Sejuk.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Mobil', 'description' => 'Terbatas (Carport).']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Sedang (5-8 orang)', 'description' => 'Meja makan keluarga.'],
                            ['name' => 'Private Room', 'description' => 'Kamar yang disulap jadi ruang makan.']
                        ],
                        'accessibility' => [
                            ['name' => 'Rata Tanah', 'description' => 'Mudah diakses.']
                        ],
                        'payment' => [
                            ['name' => 'Tunai', 'description' => 'Utama.'],
                            ['name' => 'Transfer', 'description' => 'BCA Transfer.']
                        ],
                        'service' => [
                            ['name' => 'Reservation', 'description' => 'Disarankan reservasi.']
                        ]
                    ]
                ]
            ]);

            // =========================================================================
            // 6. Rumangsa Kopi (Hidden Nature)
            // =========================================================================
            Place::create([
                'name' => 'Rumangsa Kopi',
                'description' => 'Kedai kopi di area Untan yang memanfaatkan teras rumah. Sangat tenang, dikelilingi pohon rindang. Salah satu spot terbaik untuk WFC atau membaca buku.',
                'longitude' => 109.3490,
                'latitude' => -0.0550,
                'image_urls' => ['https://via.placeholder.com/600x400?text=Rumangsa'],
                'coin_reward' => 25,
                'exp_reward' => 130, // High XP
                'min_price' => 20000,
                'max_price' => 45000,
                'avg_rating' => 4.8,
                'total_review' => 95,
                'total_checkin' => 280,
                'status' => true,
                'partnership_status' => true,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Hidden spot di komplek dosen yang tenang dan asri.',
                        'address' => 'Komp. Untan, Jl. Silat No. X, Pontianak.',
                        'opening_hours' => '08:00',
                        'closing_hours' => '22:00',
                        'opening_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0852-XXXX-XXXX',
                        'website' => 'https://instagram.com/rumangsa.kopi',
                    ],
                    'place_value' => ['Pet Friendly', 'Suasana Tenang', 'Cocok untuk Work From Cafe', 'Hidden Gem'],
                    'food_type' => ['Coffee', 'Tea', 'Light Bites'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+Rumangsa',
                    'menu' => [
                        ['name' => 'Es Kopi Rumangsa', 'image_url' => 'https://via.placeholder.com/150', 'price' => 22000, 'description' => 'Kopi susu gula aren creamy.'],
                        ['name' => 'Donat Kampung', 'image_url' => 'https://via.placeholder.com/150', 'price' => 8000, 'description' => 'Donat gula klasik.'],
                        ['name' => 'Filter Coffee', 'image_url' => 'https://via.placeholder.com/150', 'price' => 25000, 'description' => 'Beans lokal pilihan.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'Outdoor Seating', 'description' => 'Teras rumah rindang.'],
                            ['name' => 'WiFi Gratis', 'description' => 'Sangat kencang.'],
                            ['name' => 'Colokan Listrik', 'description' => 'Banyak.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Motor', 'description' => 'Aman di halaman rumah.']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Kecil (2-4 orang)', 'description' => 'Kursi taman.']
                        ],
                        'accessibility' => [
                            ['name' => 'Rata Tanah', 'description' => 'Mudah.']
                        ],
                        'payment' => [
                            ['name' => 'QRIS', 'description' => 'Scan QR.']
                        ],
                        'service' => [
                            ['name' => 'Dine In', 'description' => 'Suasana santai.']
                        ]
                    ]
                ]
            ]);

            // =========================================================================
            // 7. Le Baker Street 4 (Parisian Bakery)
            // =========================================================================
            Place::create([
                'name' => 'Le Baker Street 4',
                'description' => 'Sepotong Paris di Pontianak. Bakery shop dengan etalase pastry yang menggoda iman. Croissant dan Danish-nya terkenal autentik.',
                'longitude' => 109.3200,
                'latitude' => -0.0300,
                'image_urls' => ['https://via.placeholder.com/600x400?text=Le+Baker'],
                'coin_reward' => 50,
                'exp_reward' => 60,
                'min_price' => 25000,
                'max_price' => 70000,
                'avg_rating' => 4.6,
                'total_review' => 320,
                'total_checkin' => 800,
                'status' => true,
                'partnership_status' => true,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Boutique bakery dengan konsep Eropa klasik.',
                        'address' => 'Jl. Ujung Pandang 2, Pontianak.',
                        'opening_hours' => '09:00',
                        'closing_hours' => '21:00',
                        'opening_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0811-XXXX-XXXX',
                        'website' => 'https://instagram.com/lebakerstreet4',
                    ],
                    'place_value' => ['Estetika', 'Rasa Autentik', 'Menu Bervariasi'],
                    'food_type' => ['Bakery', 'Pastry', 'Coffee'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+LeBaker',
                    'menu' => [
                        ['name' => 'Almond Croissant', 'image_url' => 'https://via.placeholder.com/150', 'price' => 35000, 'description' => 'Croissant flaky dengan isian almond.'],
                        ['name' => 'Cromboloni', 'image_url' => 'https://via.placeholder.com/150', 'price' => 40000, 'description' => 'Pastry viral dengan isian krim.'],
                        ['name' => 'Hot Cappuccino', 'image_url' => 'https://via.placeholder.com/150', 'price' => 30000, 'description' => 'Teman makan roti.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'Indoor Seating', 'description' => 'Estetik dan dingin.'],
                            ['name' => 'Toilet Bersih', 'description' => 'Tersedia.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Mobil', 'description' => 'Tersedia di depan ruko.']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Kecil (2-4 orang)', 'description' => 'Cocok untuk tea time.']
                        ],
                        'accessibility' => [
                            ['name' => 'Pintu Kaca', 'description' => 'Mudah didorong.']
                        ],
                        'payment' => [
                            ['name' => 'Debit Card', 'description' => 'Tersedia.'],
                            ['name' => 'QRIS', 'description' => 'Tersedia.']
                        ],
                        'service' => [
                            ['name' => 'Take Away', 'description' => 'Kemasan box cantik.']
                        ]
                    ]
                ]
            ]);

            // =========================================================================
            // 8. Disela Coffee & Roastery (Hidden Minimalist)
            // =========================================================================
            Place::create([
                'name' => 'Disela Coffee & Roastery',
                'description' => 'Sesuai namanya, berada "di sela-sela" bangunan lain. Coffee shop minimalis yang fokus pada kualitas beans. Tempatnya mungil tapi sangat nyaman.',
                'longitude' => 109.3380,
                'latitude' => -0.0380,
                'image_urls' => ['https://via.placeholder.com/600x400?text=Disela'],
                'coin_reward' => 30,
                'exp_reward' => 100,
                'min_price' => 20000,
                'max_price' => 50000,
                'avg_rating' => 4.6,
                'total_review' => 180,
                'total_checkin' => 450,
                'status' => true,
                'partnership_status' => true,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Coffee shop mungil "di sela" bangunan dengan kopi berkualitas.',
                        'address' => 'Jl. Dr. Sutomo, Pontianak.',
                        'opening_hours' => '08:00',
                        'closing_hours' => '22:00',
                        'opening_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0896-XXXX-XXXX',
                        'website' => 'https://instagram.com/disela.coffee',
                    ],
                    'place_value' => ['Hidden Gem', 'Pelayanan Ramah', 'Suasana Tenang'],
                    'food_type' => ['Coffee', 'Non-Coffee'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+Disela',
                    'menu' => [
                        ['name' => 'Kopi Susu Disela', 'image_url' => 'https://via.placeholder.com/150', 'price' => 22000, 'description' => 'Creamy dan strong.'],
                        ['name' => 'Lychee Tea', 'image_url' => 'https://via.placeholder.com/150', 'price' => 18000, 'description' => 'Segar dengan buah leci asli.'],
                        ['name' => 'Brownies', 'image_url' => 'https://via.placeholder.com/150', 'price' => 15000, 'description' => 'Fudgy brownies.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'AC', 'description' => 'Sejuk.'],
                            ['name' => 'Toilet Bersih', 'description' => 'Kecil tapi bersih.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Motor', 'description' => 'Di depan ruko.']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Kecil (2-4 orang)', 'description' => 'Space terbatas.']
                        ],
                        'accessibility' => [
                            ['name' => 'Rata Tanah', 'description' => 'Mudah.']
                        ],
                        'payment' => [
                            ['name' => 'QRIS', 'description' => 'Utama.'],
                            ['name' => 'Tunai', 'description' => 'Bisa.']
                        ],
                        'service' => [
                            ['name' => 'Take Away', 'description' => 'Cup travel friendly.']
                        ]
                    ]
                ]
            ]);

            // =========================================================================
            // 9. Bakso Cahaya Abadi (Legendary)
            // =========================================================================
            Place::create([
                'name' => 'Bakso Cahaya Abadi',
                'description' => 'Legenda kuliner Pontianak. Bukan tempat yang instagrammable, tapi rasa bakso sapinya konsisten lezat selama puluhan tahun.',
                'longitude' => 109.3500,
                'latitude' => -0.0250,
                'image_urls' => ['https://via.placeholder.com/600x400?text=Bakso+Cahaya+Abadi'],
                'coin_reward' => 20,
                'exp_reward' => 40,
                'min_price' => 25000,
                'max_price' => 50000,
                'avg_rating' => 4.7,
                'total_review' => 1500,
                'total_checkin' => 5000,
                'status' => true,
                'partnership_status' => false,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Warung bakso legendaris dengan resep turun temurun.',
                        'address' => 'Jl. Diponegoro No. XX, Pontianak.',
                        'opening_hours' => '10:00',
                        'closing_hours' => '21:00',
                        'opening_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0561-XXXXXX',
                        'website' => 'https://',
                    ],
                    'place_value' => ['Rasa Autentik', 'Tempat Bersejarah', 'Harga Terjangkau'],
                    'food_type' => ['Indonesian', 'Bakso'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+Bakso',
                    'menu' => [
                        ['name' => 'Bakso Sapi Komplit', 'image_url' => 'https://via.placeholder.com/150', 'price' => 30000, 'description' => 'Bakso, tahu, mie kuning, kwetiau.'],
                        ['name' => 'Es Jeruk Besar', 'image_url' => 'https://via.placeholder.com/150', 'price' => 10000, 'description' => 'Jeruk Pontianak asli.'],
                        ['name' => 'Kerupuk Ikan', 'image_url' => 'https://via.placeholder.com/150', 'price' => 5000, 'description' => 'Pelengkap makan bakso.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'Kipas Angin', 'description' => 'Non-AC.'],
                            ['name' => 'Toilet', 'description' => 'Sederhana.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Motor', 'description' => 'Di trotoar depan.']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Sedang (5-8 orang)', 'description' => 'Meja panjang sharing.']
                        ],
                        'accessibility' => [
                            ['name' => 'Rata Tanah', 'description' => 'Mudah.']
                        ],
                        'payment' => [
                            ['name' => 'Tunai', 'description' => 'Cash only.']
                        ],
                        'service' => [
                            ['name' => 'Dine In', 'description' => 'Cepat saji.']
                        ]
                    ]
                ]
            ]);

            // =========================================================================
            // 10. Popina (Chic Bistro)
            // =========================================================================
            Place::create([
                'name' => 'Popina',
                'description' => 'Bistro dengan desain interior yang chic dan playful. Menyajikan comfort food, gelato, dan cake. Destinasi "cantik" untuk arisan atau date.',
                'longitude' => 109.3250,
                'latitude' => -0.0420,
                'image_urls' => ['https://via.placeholder.com/600x400?text=Popina'],
                'coin_reward' => 70,
                'exp_reward' => 60,
                'min_price' => 35000,
                'max_price' => 90000,
                'avg_rating' => 4.6,
                'total_review' => 400,
                'total_checkin' => 950,
                'status' => true,
                'partnership_status' => true,
                'additional_info' => [
                    'place_detail' => [
                        'short_description' => 'Lifestyle bistro dengan gelato dan comfort food.',
                        'address' => 'Jl. Suprapto, Pontianak.',
                        'opening_hours' => '10:00',
                        'closing_hours' => '22:00',
                        'opening_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                        'contact_number' => '0811-XXXX-XXXX',
                        'website' => 'https://instagram.com/popina',
                    ],
                    'place_value' => ['Estetika', 'Ramah Keluarga', 'Menu Bervariasi'],
                    'food_type' => ['Western', 'Dessert', 'Gelato'],
                    'menu_image_url' => 'https://via.placeholder.com/640x480?text=Menu+Popina',
                    'menu' => [
                        ['name' => 'Gelato Cone', 'image_url' => 'https://via.placeholder.com/150', 'price' => 35000, 'description' => '2 scoops gelato homemade.'],
                        ['name' => 'Popina Fried Rice', 'image_url' => 'https://via.placeholder.com/150', 'price' => 45000, 'description' => 'Nasi goreng buntut.'],
                        ['name' => 'Lychee Rose Cake', 'image_url' => 'https://via.placeholder.com/150', 'price' => 40000, 'description' => 'Slice cake wangi bunga mawar.']
                    ],
                    'place_attributes' => [
                        'facility' => [
                            ['name' => 'Baby Chair', 'description' => 'Tersedia.'],
                            ['name' => 'Indoor Seating', 'description' => 'Sofa nyaman.'],
                            ['name' => 'AC', 'description' => 'Dingin.']
                        ],
                        'parking' => [
                            ['name' => 'Parkir Mobil', 'description' => 'Luas.']
                        ],
                        'capacity' => [
                            ['name' => 'Meja Sedang (5-8 orang)', 'description' => 'Cocok untuk grup.']
                        ],
                        'accessibility' => [
                            ['name' => 'Pintu Kaca', 'description' => 'Akses mudah.']
                        ],
                        'payment' => [
                            ['name' => 'Credit Card', 'description' => 'Bisa.'],
                            ['name' => 'Debit Card', 'description' => 'Bisa.']
                        ],
                        'service' => [
                            ['name' => 'Private Event', 'description' => 'Bisa booking area.']
                        ]
                    ]
                ]
            ]);
        });
    }
}