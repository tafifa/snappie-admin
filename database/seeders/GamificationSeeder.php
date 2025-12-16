<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\UserActionLog;
use Illuminate\Database\Seeder;

class GamificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding gamification data...');

        $this->seedAchievements();
        $this->seedChallenges();

        $this->command->info('Gamification seeding complete!');
    }

    /**
     * Seed one-time achievements.
     */
    protected function seedAchievements(): void
    {
        $achievements = [
            // Achievement: Popular Post (100+ likes)
            [
                'code' => 'ach_popular_post',
                'name' => 'Postingan Populer',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Postingan Anda mendapat 100+ likes',
                'criteria_action' => UserActionLog::ACTION_POST_LIKE_RECEIVED,
                'criteria_target' => 100,
                'image_url' => null,
                'coin_reward' => 200,
                'reward_xp' => 100,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 1,
            ],
            // Achievement: Challenge Warrior (complete 10 challenges)
            [
                'code' => 'ach_challenge_warrior',
                'name' => 'Warrior Challenge',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Selesaikan 10 challenge',
                'criteria_action' => UserActionLog::ACTION_CHALLENGE_COMPLETED,
                'criteria_target' => 10,
                'image_url' => null,
                'coin_reward' => 300,
                'reward_xp' => 150,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 2,
            ],
            // Achievement: Coin Collector (earn 100 coins total)
            [
                'code' => 'ach_100_coins',
                'name' => 'Kolektor Koin',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Dapatkan 100 koin',
                'criteria_action' => UserActionLog::ACTION_COIN_EARNED,
                'criteria_target' => 100,
                'image_url' => null,
                'coin_reward' => 100,
                'reward_xp' => 50,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 3,
            ],
            // Achievement: XP Master (earn 200 XP total)
            [
                'code' => 'ach_200_xp',
                'name' => 'Master XP',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Dapatkan 200 XP',
                'criteria_action' => UserActionLog::ACTION_EXP_EARNED,
                'criteria_target' => 200,
                'image_url' => null,
                'coin_reward' => 100,
                'reward_xp' => 50,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 4,
            ],
            // Achievement: Monthly Best October (monthly_best for October)
            [
                'code' => 'ach_best_october',
                'name' => 'Terbaik Oktober',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Raih gelar Monthly Best di Oktober',
                'criteria_action' => UserActionLog::ACTION_MONTHLY_BEST,
                'criteria_target' => 1,
                'image_url' => null,
                'coin_reward' => 500,
                'reward_xp' => 250,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 5,
            ],
            // Achievement: Rank 1 October (rank_first for October)
            [
                'code' => 'ach_rank1_october',
                'name' => 'Peringkat 1 Oktober',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Raih peringkat 1 di leaderboard Oktober',
                'criteria_action' => UserActionLog::ACTION_MONTHLY_RANK_FIRST,
                'criteria_target' => 1,
                'image_url' => null,
                'coin_reward' => 1000,
                'reward_xp' => 500,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 6,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['code' => $achievement['code']],
                $achievement
            );
        }

        $this->command->info('✓ Seeded ' . count($achievements) . ' achievements');
    }

    /**
     * Seed challenges (daily, weekly, special).
     */
    protected function seedChallenges(): void
    {
        $challenges = [
            // ===================
            // DAILY CHALLENGES (type = challenge, reset_schedule = daily)
            // ===================
            [
                'code' => 'daily_rating_5',
                'name' => 'Bintang 5 Harian',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Beri 5 rating 5 bintang hari ini',
                'criteria_action' => UserActionLog::ACTION_RATING_5_STAR,
                'criteria_target' => 5,
                'image_url' => null,
                'coin_reward' => 20,
                'reward_xp' => 10,
                'status' => true,
                'reset_schedule' => Achievement::RESET_DAILY,
                'display_order' => 100,
            ],
            [
                'code' => 'daily_share',
                'name' => 'Berbagi Pengalaman',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Share 3 pengalaman hari ini',
                'criteria_action' => UserActionLog::ACTION_SHARE_EXPERIENCE,
                'criteria_target' => 3,
                'image_url' => null,
                'coin_reward' => 15,
                'reward_xp' => 8,
                'status' => true,
                'reset_schedule' => Achievement::RESET_DAILY,
                'display_order' => 101,
            ],
            [
                'code' => 'daily_photo_mission',
                'name' => 'Misi Fotografi',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Upload 3 foto hari ini',
                'criteria_action' => UserActionLog::ACTION_UPLOAD_PHOTO,
                'criteria_target' => 3,
                'image_url' => null,
                'coin_reward' => 15,
                'reward_xp' => 8,
                'status' => true,
                'reset_schedule' => Achievement::RESET_DAILY,
                'display_order' => 102,
            ],
            [
                'code' => 'daily_review',
                'name' => 'Review Harian',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Tulis 1 review hari ini',
                'criteria_action' => UserActionLog::ACTION_REVIEW,
                'criteria_target' => 1,
                'image_url' => null,
                'coin_reward' => 10,
                'reward_xp' => 5,
                'status' => true,
                'reset_schedule' => Achievement::RESET_DAILY,
                'display_order' => 103,
            ],

            // ===================
            // WEEKLY CHALLENGES (type = challenge, reset_schedule = weekly)
            // ===================
            [
                'code' => 'weekly_photographer',
                'name' => 'Fotografer Mingguan',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Upload 10 foto minggu ini',
                'criteria_action' => UserActionLog::ACTION_UPLOAD_PHOTO,
                'criteria_target' => 10,
                'image_url' => null,
                'coin_reward' => 50,
                'reward_xp' => 25,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 200,
            ],
            [
                'code' => 'weekly_hidden_gem',
                'name' => 'Pemburu Hidden Gem',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Kunjungi 3 hidden gem minggu ini',
                'criteria_action' => UserActionLog::ACTION_HIDDEN_GEM_VISIT,
                'criteria_target' => 3,
                'image_url' => null,
                'coin_reward' => 60,
                'reward_xp' => 30,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 201,
            ],
            [
                'code' => 'weekly_breakfast',
                'name' => 'Pencinta Sarapan',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Checkin sarapan (06:00-10:00) 3x minggu ini',
                'criteria_action' => UserActionLog::ACTION_BREAKFAST_CHECKIN,
                'criteria_target' => 3,
                'image_url' => null,
                'coin_reward' => 40,
                'reward_xp' => 20,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 202,
            ],
            [
                'code' => 'weekly_popular_post',
                'name' => 'Konten Populer',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Dapatkan 50 likes di postingan minggu ini',
                'criteria_action' => UserActionLog::ACTION_POST_LIKE_RECEIVED,
                'criteria_target' => 50,
                'image_url' => null,
                'coin_reward' => 70,
                'reward_xp' => 35,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 203,
            ],
            [
                'code' => 'weekly_rank_first',
                'name' => 'Peringkat Teratas',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Raih peringkat 1 di leaderboard minggu ini',
                'criteria_action' => UserActionLog::ACTION_RANK_FIRST,
                'criteria_target' => 1,
                'image_url' => null,
                'coin_reward' => 100,
                'reward_xp' => 50,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 204,
            ],

            // ===================
            // SPECIAL CHALLENGES (type = challenge, reset_schedule = none)
            // ===================
            [
                'code' => 'special_nusantara',
                'name' => 'Penjelajah Nusantara',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Checkin di 5 restoran makanan lokal',
                'criteria_action' => UserActionLog::ACTION_LOCAL_FOOD_CHECKIN,
                'criteria_target' => 5,
                'image_url' => null,
                'coin_reward' => 100,
                'reward_xp' => 50,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 300,
            ],
            [
                'code' => 'special_sweet_tooth',
                'name' => 'Pecinta Manis',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Checkin di 5 tempat dessert',
                'criteria_action' => UserActionLog::ACTION_DESSERT_CHECKIN,
                'criteria_target' => 5,
                'image_url' => null,
                'coin_reward' => 80,
                'reward_xp' => 40,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 301,
            ],
            [
                'code' => 'special_wfc',
                'name' => 'Work From Cafe',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Checkin di 5 cafe',
                'criteria_action' => UserActionLog::ACTION_CAFE_CHECKIN,
                'criteria_target' => 5,
                'image_url' => null,
                'coin_reward' => 80,
                'reward_xp' => 40,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 302,
            ],
            [
                'code' => 'special_caffeine',
                'name' => 'Pecandu Kopi',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Checkin di 5 coffee shop',
                'criteria_action' => UserActionLog::ACTION_COFFEE_CHECKIN,
                'criteria_target' => 5,
                'image_url' => null,
                'coin_reward' => 80,
                'reward_xp' => 40,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 303,
            ],
            [
                'code' => 'special_local_guide',
                'name' => 'Pemandu Lokal',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Dapatkan 500 XP',
                'criteria_action' => UserActionLog::ACTION_EXP_EARNED,
                'criteria_target' => 500,
                'image_url' => null,
                'coin_reward' => 200,
                'reward_xp' => 100,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 304,
            ],
        ];

        foreach ($challenges as $challenge) {
            Achievement::updateOrCreate(
                ['code' => $challenge['code']],
                $challenge
            );
        }

        $this->command->info('✓ Seeded ' . count($challenges) . ' challenges');
    }
}
