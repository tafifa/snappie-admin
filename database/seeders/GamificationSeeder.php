<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GamificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menggunakan transaksi database agar data konsisten (terutama untuk Foreign Key Prerequisite)
        DB::transaction(function () {
            // Hapus data lama jika diperlukan (opsional, hati-hati di production)
            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // Achievement::truncate();
            // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->seedAchievements(); // 6 Core Actions x 3 Levels
            $this->seedChallenges();   // 9 Challenges (None, Daily, Weekly)
        });
    }

    /**
     * Seed Achievements (One-time, Leveling System)
     */
    private function seedAchievements()
    {
        // Konfigurasi berdasarkan Tabel Desain Achievement V3.3
        $series = [
            // 1. CHECK-IN SERIES
            'checkin' => [
                ['name' => 'Langkah Awal',     'target' => 1,   'coin' => 50,  'xp' => 20,  'desc' => 'Check-in pertama kali.'],
                ['name' => 'Penjelajah Kota',  'target' => 20,  'coin' => 150, 'xp' => 100, 'desc' => 'Eksplorasi menengah.'],
                ['name' => 'Legenda Peta',     'target' => 100, 'coin' => 500, 'xp' => 300, 'desc' => 'Eksplorasi expert.'],
            ],
            // 2. REVIEW SERIES
            'review' => [
                ['name' => 'Komentator',       'target' => 1,   'coin' => 50,  'xp' => 25,  'desc' => 'Review pertama.'],
                ['name' => 'Kritikus Tajam',   'target' => 10,  'coin' => 200, 'xp' => 100, 'desc' => 'Konsisten mereview.'],
                ['name' => 'Lidah Emas',       'target' => 50,  'coin' => 600, 'xp' => 400, 'desc' => 'Opinion leader.'],
            ],
            // 3. POST SERIES
            'post' => [
                ['name' => 'Iseng Posting',    'target' => 1,   'coin' => 50,  'xp' => 20,  'desc' => 'Postingan pertama.'],
                ['name' => 'Jurnalis Makanan', 'target' => 10,  'coin' => 200, 'xp' => 100, 'desc' => 'Sharing aktif.'],
                ['name' => 'Kurator Hidden Gem','target' => 50, 'coin' => 500, 'xp' => 300, 'desc' => 'Influencer konten.'],
            ],
            // 4. COIN EARNED SERIES
            'coin_earned' => [
                ['name' => 'Tabungan Awal',    'target' => 1000, 'coin' => 100,  'xp' => 50,   'desc' => 'Akumulasi koin awal.'],
                ['name' => 'Juragan Kecil',    'target' => 5000, 'coin' => 500,  'xp' => 250,  'desc' => 'Akumulasi menengah.'],
                ['name' => 'Sultan Snappie',   'target' => 20000,'coin' => 2000, 'xp' => 1000, 'desc' => 'Wealthy user.'],
            ],
            // 5. XP EARNED SERIES (Reward XP = 0 untuk mencegah loop)
            'xp_earned' => [
                ['name' => 'Pendatang Baru',   'target' => 500,   'coin' => 100,  'xp' => 0, 'desc' => 'Level up awal.'],
                ['name' => 'Warga Tetap',      'target' => 2500,  'coin' => 300,  'xp' => 0, 'desc' => 'Level up lanjut.'],
                ['name' => 'Sesepuh Kota',     'target' => 10000, 'coin' => 1000, 'xp' => 0, 'desc' => 'Top level user.'],
            ],
            // 6. TOP RANK SERIES (Menggunakan Target sebagai threshold rank)
            'top_rank' => [
                ['name' => 'Bintang Lokal',    'target' => 10, 'coin' => 200,  'xp' => 100,  'desc' => 'Masuk Top 10 Leaderboard.', 'filter' => ['max_rank' => 10]],
                ['name' => 'Selebriti Kota',   'target' => 5,  'coin' => 1000, 'xp' => 500,  'desc' => 'Masuk Top 5 Leaderboard.',  'filter' => ['max_rank' => 5]],
                ['name' => 'Penguasa Kuliner', 'target' => 1,  'coin' => 5000, 'xp' => 2500, 'desc' => 'Raih Peringkat 1 Leaderboard.', 'filter' => ['max_rank' => 1]],
            ],
        ];

        $displayOrder = 1;

        foreach ($series as $action => $levels) {
            $previousAchievementId = null; // Reset parent ID untuk setiap series baru

            foreach ($levels as $index => $data) {
                $level = $index + 1;
                // $code = "ach_{$action}_{$level}";
                
                // Membuat Achievement Record
                $achievement = Achievement::updateOrCreate(
                    // ['code' => $code], // Kunci unik
                    [
                        'name'            => $data['name'],
                        'code'            => "ach_$action",
                        'type'            => 'achievement', // Konstanta TYPE_ACHIEVEMENT
                        'description'     => $data['desc'],
                        'criteria_action' => $action,
                        'criteria_target' => $data['target'],
                        'criteria_filters'=> $data['filter'] ?? null, // Filter opsional (untuk rank)
                        'coin_reward'     => $data['coin'],
                        'reward_xp'       => $data['xp'],
                        'reset_schedule'  => 'none', // Konstanta RESET_NONE
                        'level'           => $level,
                        'required_achievement_id' => $previousAchievementId, // Link ke level sebelumnya
                        'display_order'   => $displayOrder++,
                        'status'          => true,
                    ]
                );

                // Set ID saat ini sebagai parent untuk iterasi level berikutnya
                $previousAchievementId = $achievement->id;
            }
        }
    }

    /**
     * Seed Challenges (Recurring, Reset Schedule)
     */
    private function seedChallenges()
    {
        // Konfigurasi berdasarkan Tabel Desain Challenge V3.3
        $challenges = [
            // 1. Tipe RESET: NONE (Special Missions)
            [
                'code' => 'miss_social_follow', 'name' => 'Mencari Teman', 'action' => 'follow', 
                'target' => 5, 'coin' => 100, 'xp' => 50, 'reset' => 'none', 
                'desc' => 'Ikuti (follow) 5 user lain.'
            ],
            [
                'code' => 'miss_social_comment', 'name' => 'Suara Netizen', 'action' => 'comment', 
                'target' => 10, 'coin' => 150, 'xp' => 75, 'reset' => 'none', 
                'desc' => 'Berikan 10 komentar total.'
            ],
            [
                'code' => 'miss_social_like', 'name' => 'Jempol Sakti', 'action' => 'like', 
                'target' => 50, 'coin' => 200, 'xp' => 100, 'reset' => 'none', 
                'desc' => 'Like 50 postingan total.'
            ],

            // 2. Tipe RESET: DAILY (Harian)
            [
                'code' => 'chal_daily_checkin', 'name' => 'Absen Dulu', 'action' => 'checkin', 
                'target' => 1, 'coin' => 20, 'xp' => 10, 'reset' => 'daily', 
                'desc' => 'Check-in 1x hari ini.'
            ],
            [
                'code' => 'chal_daily_coin', 'name' => 'Receh Harian', 'action' => 'coin_earned', 
                'target' => 50, 'coin' => 10, 'xp' => 25, 'reset' => 'daily', 
                'desc' => 'Dapat 50 koin hari ini.'
            ],
            [
                'code' => 'chal_daily_post', 'name' => 'Story Hari Ini', 'action' => 'post', 
                'target' => 1, 'coin' => 30, 'xp' => 15, 'reset' => 'daily', 
                'desc' => 'Buat 1 postingan hari ini.'
            ],

            // 3. Tipe RESET: WEEKLY (Mingguan)
            [
                'code' => 'chal_weekly_review', 'name' => 'Jurnal Mingguan', 'action' => 'review', 
                'target' => 3, 'coin' => 250, 'xp' => 125, 'reset' => 'weekly', 
                'desc' => 'Tulis 3 ulasan minggu ini.'
            ],
            [
                'code' => 'chal_weekly_xp', 'name' => 'Mengejar Level', 'action' => 'xp_earned', 
                'target' => 200, 'coin' => 150, 'xp' => 0, 'reset' => 'weekly', // XP 0 prevent loop
                'desc' => 'Dapat 200 XP minggu ini.'
            ],
            [
                'code' => 'chal_weekly_trip', 'name' => 'Petualang Minggu Ini', 'action' => 'checkin', 
                'target' => 5, 'coin' => 300, 'xp' => 150, 'reset' => 'weekly', 
                'desc' => 'Check-in 5 tempat minggu ini.'
            ],
        ];

        $displayOrder = 50; // Mulai urutan setelah achievements

        foreach ($challenges as $data) {
            Achievement::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name'            => $data['name'],
                    'type'            => 'challenge', // Konstanta TYPE_CHALLENGE
                    'description'     => $data['desc'],
                    'criteria_action' => $data['action'],
                    'criteria_target' => $data['target'],
                    'coin_reward'     => $data['coin'],
                    'reward_xp'       => $data['xp'],
                    'reset_schedule'  => $data['reset'],
                    'level'           => null,
                    'required_achievement_id' => null,
                    'display_order'   => $displayOrder++,
                    'status'          => true,
                ]
            );
        }
    }
}