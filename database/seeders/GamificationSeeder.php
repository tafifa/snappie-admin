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
     * Seed one-time achievements with 3-level progression.
     */
    protected function seedAchievements(): void
    {
        $achievements = [
            // ===== EXPLORER SERIES (Check-in) - 3 Levels =====
            [
                'code' => 'ach_explorer_1',
                'name' => 'Explorer I',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Lakukan 100 check-in di tempat manapun',
                'criteria_action' => UserActionLog::ACTION_CHECKIN,
                'criteria_target' => 100,
                'image_url' => null,
                'coin_reward' => 100,
                'reward_xp' => 50,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 1,
                'level' => 1,
                'required_achievement_id' => null,
            ],
            [
                'code' => 'ach_explorer_2',
                'name' => 'Explorer II',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Lakukan 250 check-in di tempat manapun',
                'criteria_action' => UserActionLog::ACTION_CHECKIN,
                'criteria_target' => 250,
                'image_url' => null,
                'coin_reward' => 250,
                'reward_xp' => 125,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 2,
                'level' => 2,
                'required_achievement_id' => null, // Will be set after level 1 created
            ],
            [
                'code' => 'ach_explorer_3',
                'name' => 'Explorer III',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Lakukan 500 check-in di tempat manapun',
                'criteria_action' => UserActionLog::ACTION_CHECKIN,
                'criteria_target' => 500,
                'image_url' => null,
                'coin_reward' => 500,
                'reward_xp' => 250,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 3,
                'level' => 3,
                'required_achievement_id' => null, // Will be set after level 2 created
            ],

            // ===== REVIEWER SERIES (Review) - 3 Levels =====
            [
                'code' => 'ach_reviewer_1',
                'name' => 'Reviewer I',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Tulis 50 review',
                'criteria_action' => UserActionLog::ACTION_REVIEW,
                'criteria_target' => 50,
                'image_url' => null,
                'coin_reward' => 100,
                'reward_xp' => 50,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 4,
                'level' => 1,
                'required_achievement_id' => null,
            ],
            [
                'code' => 'ach_reviewer_2',
                'name' => 'Reviewer II',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Tulis 150 review',
                'criteria_action' => UserActionLog::ACTION_REVIEW,
                'criteria_target' => 150,
                'image_url' => null,
                'coin_reward' => 250,
                'reward_xp' => 125,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 5,
                'level' => 2,
                'required_achievement_id' => null,
            ],
            [
                'code' => 'ach_reviewer_3',
                'name' => 'Reviewer III',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Tulis 300 review',
                'criteria_action' => UserActionLog::ACTION_REVIEW,
                'criteria_target' => 300,
                'image_url' => null,
                'coin_reward' => 500,
                'reward_xp' => 250,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 6,
                'level' => 3,
                'required_achievement_id' => null,
            ],

            // ===== SOCIAL BUTTERFLY SERIES (Follow) - 3 Levels =====
            [
                'code' => 'ach_social_1',
                'name' => 'Social Butterfly I',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Follow 25 pengguna',
                'criteria_action' => UserActionLog::ACTION_FOLLOW,
                'criteria_target' => 25,
                'image_url' => null,
                'coin_reward' => 100,
                'reward_xp' => 50,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 7,
                'level' => 1,
                'required_achievement_id' => null,
            ],
            [
                'code' => 'ach_social_2',
                'name' => 'Social Butterfly II',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Follow 75 pengguna',
                'criteria_action' => UserActionLog::ACTION_FOLLOW,
                'criteria_target' => 75,
                'image_url' => null,
                'coin_reward' => 250,
                'reward_xp' => 125,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 8,
                'level' => 2,
                'required_achievement_id' => null,
            ],
            [
                'code' => 'ach_social_3',
                'name' => 'Social Butterfly III',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Follow 150 pengguna',
                'criteria_action' => UserActionLog::ACTION_FOLLOW,
                'criteria_target' => 150,
                'image_url' => null,
                'coin_reward' => 500,
                'reward_xp' => 250,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 9,
                'level' => 3,
                'required_achievement_id' => null,
            ],

            // ===== WEALTH SERIES (Coin Earned) - 3 Levels =====
            [
                'code' => 'ach_wealth_1',
                'name' => 'Coin Collector I',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Dapatkan 500 koin',
                'criteria_action' => UserActionLog::ACTION_COIN_EARNED,
                'criteria_target' => 500,
                'image_url' => null,
                'coin_reward' => 100,
                'reward_xp' => 50,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 10,
                'level' => 1,
                'required_achievement_id' => null,
            ],
            [
                'code' => 'ach_wealth_2',
                'name' => 'Coin Collector II',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Dapatkan 1500 koin',
                'criteria_action' => UserActionLog::ACTION_COIN_EARNED,
                'criteria_target' => 1500,
                'image_url' => null,
                'coin_reward' => 250,
                'reward_xp' => 125,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 11,
                'level' => 2,
                'required_achievement_id' => null,
            ],
            [
                'code' => 'ach_wealth_3',
                'name' => 'Coin Collector III',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Dapatkan 3000 koin',
                'criteria_action' => UserActionLog::ACTION_COIN_EARNED,
                'criteria_target' => 3000,
                'image_url' => null,
                'coin_reward' => 500,
                'reward_xp' => 250,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 12,
                'level' => 3,
                'required_achievement_id' => null,
            ],

            // ===== SINGLE ACHIEVEMENTS (No levels) =====
            [
                'code' => 'ach_top_rank',
                'name' => 'Top Rank',
                'type' => Achievement::TYPE_ACHIEVEMENT,
                'description' => 'Raih posisi #1 di leaderboard',
                'criteria_action' => UserActionLog::ACTION_TOP_RANK,
                'criteria_target' => 1,
                'image_url' => null,
                'coin_reward' => 1000,
                'reward_xp' => 500,
                'status' => true,
                'reset_schedule' => Achievement::RESET_NONE,
                'display_order' => 13,
                'level' => null,
                'required_achievement_id' => null,
            ],
        ];

        // Create achievements and set required_achievement_id for levels
        $createdAchievements = [];
        
        foreach ($achievements as $achievementData) {
            $achievement = Achievement::create($achievementData);
            $createdAchievements[$achievement->code] = $achievement;
        }

        // Set prerequisites for level 2 and 3
        $prerequisites = [
            'ach_explorer_2' => 'ach_explorer_1',
            'ach_explorer_3' => 'ach_explorer_2',
            'ach_reviewer_2' => 'ach_reviewer_1',
            'ach_reviewer_3' => 'ach_reviewer_2',
            'ach_social_2' => 'ach_social_1',
            'ach_social_3' => 'ach_social_2',
            'ach_wealth_2' => 'ach_wealth_1',
            'ach_wealth_3' => 'ach_wealth_2',
        ];

        foreach ($prerequisites as $code => $requiredCode) {
            if (isset($createdAchievements[$code]) && isset($createdAchievements[$requiredCode])) {
                $createdAchievements[$code]->update([
                    'required_achievement_id' => $createdAchievements[$requiredCode]->id,
                ]);
            }
        }

        $this->command->info('✓ Seeded ' . count($achievements) . ' achievements with 3-level progression');
    }

    /**
     * Seed repeating challenges.
     */
    protected function seedChallenges(): void
    {
        $challenges = [
            // ===== DAILY CHALLENGES =====
            [
                'code' => 'daily_checkin_5',
                'name' => 'Check-in Harian',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Lakukan 5 check-in hari ini',
                'criteria_action' => UserActionLog::ACTION_CHECKIN,
                'criteria_target' => 5,
                'image_url' => null,
                'coin_reward' => 20,
                'reward_xp' => 10,
                'status' => true,
                'reset_schedule' => Achievement::RESET_DAILY,
                'display_order' => 100,
            ],
            [
                'code' => 'daily_review_3',
                'name' => 'Review Harian',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Tulis 3 review hari ini',
                'criteria_action' => UserActionLog::ACTION_REVIEW,
                'criteria_target' => 3,
                'image_url' => null,
                'coin_reward' => 15,
                'reward_xp' => 8,
                'status' => true,
                'reset_schedule' => Achievement::RESET_DAILY,
                'display_order' => 101,
            ],
            [
                'code' => 'daily_post_2',
                'name' => 'Post Harian',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Buat 2 post hari ini',
                'criteria_action' => UserActionLog::ACTION_POST,
                'criteria_target' => 2,
                'image_url' => null,
                'coin_reward' => 15,
                'reward_xp' => 8,
                'status' => true,
                'reset_schedule' => Achievement::RESET_DAILY,
                'display_order' => 102,
            ],
            [
                'code' => 'daily_like_10',
                'name' => 'Like Harian',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Like 10 postingan hari ini',
                'criteria_action' => UserActionLog::ACTION_LIKE,
                'criteria_target' => 10,
                'image_url' => null,
                'coin_reward' => 10,
                'reward_xp' => 5,
                'status' => true,
                'reset_schedule' => Achievement::RESET_DAILY,
                'display_order' => 103,
            ],
            [
                'code' => 'daily_comment_5',
                'name' => 'Comment Harian',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Beri 5 komentar hari ini',
                'criteria_action' => UserActionLog::ACTION_COMMENT,
                'criteria_target' => 5,
                'image_url' => null,
                'coin_reward' => 15,
                'reward_xp' => 8,
                'status' => true,
                'reset_schedule' => Achievement::RESET_DAILY,
                'display_order' => 104,
            ],

            // ===== WEEKLY CHALLENGES =====
            [
                'code' => 'weekly_checkin_20',
                'name' => 'Check-in Mingguan',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Lakukan 20 check-in minggu ini',
                'criteria_action' => UserActionLog::ACTION_CHECKIN,
                'criteria_target' => 20,
                'image_url' => null,
                'coin_reward' => 50,
                'reward_xp' => 25,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 200,
            ],
            [
                'code' => 'weekly_review_10',
                'name' => 'Reviewer Mingguan',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Tulis 10 review minggu ini',
                'criteria_action' => UserActionLog::ACTION_REVIEW,
                'criteria_target' => 10,
                'image_url' => null,
                'coin_reward' => 60,
                'reward_xp' => 30,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 201,
            ],
            [
                'code' => 'weekly_post_5',
                'name' => 'Content Creator',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Buat 5 post minggu ini',
                'criteria_action' => UserActionLog::ACTION_POST,
                'criteria_target' => 5,
                'image_url' => null,
                'coin_reward' => 40,
                'reward_xp' => 20,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 202,
            ],
            [
                'code' => 'weekly_follow_10',
                'name' => 'Social Network',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Follow 10 pengguna minggu ini',
                'criteria_action' => UserActionLog::ACTION_FOLLOW,
                'criteria_target' => 10,
                'image_url' => null,
                'coin_reward' => 40,
                'reward_xp' => 20,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 203,
            ],
            [
                'code' => 'weekly_coin_200',
                'name' => 'Coin Hunter',
                'type' => Achievement::TYPE_CHALLENGE,
                'description' => 'Dapatkan 200 koin minggu ini',
                'criteria_action' => UserActionLog::ACTION_COIN_EARNED,
                'criteria_target' => 200,
                'image_url' => null,
                'coin_reward' => 70,
                'reward_xp' => 35,
                'status' => true,
                'reset_schedule' => Achievement::RESET_WEEKLY,
                'display_order' => 204,
            ],
        ];

        foreach ($challenges as $challenge) {
            Achievement::updateOrCreate(
                ['code' => $challenge['code']],
                $challenge
            );
        }

        $this->command->info('✓ Seeded ' . count($challenges) . ' challenges (daily & weekly)');
    }
}
