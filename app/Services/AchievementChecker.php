<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserActionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class AchievementChecker
{
    /**
     * Main entry point - check achievements when user performs an action.
     *
     * @param User $user
     * @param string $actionType
     * @param array $actionData
     * @return array
     */
    public function checkOnAction(User $user, string $actionType, array $actionData = []): array
    {
        return DB::transaction(function () use ($user, $actionType, $actionData) {
            // Step 1: Log the action
            $this->logAction($user->id, $actionType, $actionData);

            // Step 2: Reset expired challenges if needed
            $this->resetExpiredChallenges($user->id);

            // Step 3: Get active achievements relevant to this action
            $relevantAchievements = $this->getRelevantAchievements($actionType);

            // Step 4: Process each achievement
            $unlocked = [];
            $updated = [];

            foreach ($relevantAchievements as $achievement) {
                $result = $this->processAchievement($user, $achievement, $actionType, $actionData);

                if ($result['unlocked']) {
                    $unlocked[] = $this->formatUnlockedAchievement($achievement);
                } elseif ($result['updated']) {
                    $updated[] = $this->formatUpdatedProgress($achievement, $result['progress']);
                }
            }

            return [
                'achievements_unlocked' => $unlocked,
                'challenges_updated' => $updated,
            ];
        });
    }

    /**
     * Log user action to the database.
     *
     * @param int $userId
     * @param string $actionType
     * @param array $actionData
     * @return UserActionLog
     */
    public function logAction(int $userId, string $actionType, array $actionData = []): UserActionLog
    {
        return UserActionLog::create([
            'user_id' => $userId,
            'action_type' => $actionType,
            'action_data' => $actionData,
        ]);
    }

    /**
     * Reset expired challenges for a user (daily/weekly/monthly).
     *
     * @param int $userId
     * @return void
     */
    public function resetExpiredChallenges(int $userId): void
    {
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();

        // Get all resettable achievements
        $resettableAchievements = Achievement::active()
            ->resettable()
            ->get();

        foreach ($resettableAchievements as $achievement) {
            $periodDate = $this->getPeriodDate($achievement->reset_schedule);

            // Check if user has a record for current period
            $existingProgress = UserAchievement::where('user_id', $userId)
                ->where('achievement_id', $achievement->id)
                ->where('period_date', $periodDate)
                ->first();

            // If no record for current period, it will be created when needed
            // Old records are kept for historical purposes
        }
    }

    /**
     * Get achievements relevant to a specific action type.
     *
     * @param string $actionType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelevantAchievements(string $actionType)
    {
        return Achievement::active()
            ->where('criteria_action', $actionType)
            ->ordered()
            ->get();
    }

    /**
     * Process a single achievement for a user.
     *
     * @param User $user
     * @param Achievement $achievement
     * @param string $triggerAction
     * @param array $actionData
     * @return array
     */
    public function processAchievement(User $user, Achievement $achievement, string $triggerAction, array $actionData = []): array
    {
        // Get or create user progress record
        $periodDate = $this->getPeriodDate($achievement->reset_schedule);

        $progress = UserAchievement::firstOrCreate(
            [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'period_date' => $periodDate,
            ],
            [
                'current_progress' => 0,
                'target_progress' => $achievement->target,
                'status' => false,
            ]
        );

        // Skip if already completed (for non-resettable achievements)
        if ($progress->isCompleted() && $achievement->isOneTime()) {
            return ['unlocked' => false, 'updated' => false, 'progress' => $progress];
        }

        // Skip if already completed for this period (for resettable achievements)
        if ($progress->isCompleted() && $achievement->isResettable()) {
            return ['unlocked' => false, 'updated' => false, 'progress' => $progress];
        }

        // Calculate new progress
        $newProgress = $this->calculateProgress($user->id, $achievement, $triggerAction, $actionData);
        $progress->current_progress = $newProgress;
        $progress->target_progress = $achievement->target;

        // Check completion
        if ($newProgress >= $achievement->target) {
            // Mark as completed
            $progress->markAsCompleted();

            // Grant rewards
            $this->grantRewards($user, $achievement);

            // Update user's total_achievement count
            if ($achievement->isOneTime()) {
                $user->increment('total_achievement');
            }

            return ['unlocked' => true, 'updated' => false, 'progress' => $progress];
        }

        // Save progress if not completed
        $progress->save();

        return ['unlocked' => false, 'updated' => true, 'progress' => $progress];
    }

    /**
     * Calculate the current progress for an achievement.
     *
     * @param int $userId
     * @param Achievement $achievement
     * @param string $triggerAction
     * @param array $actionData
     * @return int
     */
    public function calculateProgress(int $userId, Achievement $achievement, string $triggerAction, array $actionData = []): int
    {
        $action = $achievement->criteria_action;
        
        // Get date range based on reset schedule
        $dateRange = $this->getDateRangeForSchedule($achievement->reset_schedule);
        
        // Count the actions
        $query = UserActionLog::forUser($userId)->ofType($action);
        
        if ($dateRange) {
            $query->betweenDates($dateRange['start'], $dateRange['end']);
        }
        
        return $query->count();
    }

    /**
     * Grant rewards to user for completing an achievement.
     *
     * @param User $user
     * @param Achievement $achievement
     * @return void
     */
    protected function grantRewards(User $user, Achievement $achievement): void
    {
        $gamificationService = app(GamificationService::class);

        $metadata = [
            'type' => 'Achievement',
            'id' => $achievement->id,
            'code' => $achievement->code,
            'name' => $achievement->name,
        ];

        // Grant coins if any
        if ($achievement->coin_reward > 0) {
            $gamificationService->addCoins($user, $achievement->coin_reward, $metadata);
        }

        // Grant XP if any
        if ($achievement->reward_xp > 0) {
            $gamificationService->addExp($user, $achievement->reward_xp, $metadata);
        }

        Log::info('Achievement rewards granted', [
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'achievement_code' => $achievement->code,
            'coins' => $achievement->coin_reward,
            'xp' => $achievement->reward_xp,
        ]);
    }

    /**
     * Get the period date based on reset schedule.
     *
     * @param string $resetSchedule
     * @return string|null
     */
    public function getPeriodDate(string $resetSchedule): ?string
    {
        return match ($resetSchedule) {
            Achievement::RESET_DAILY => now()->toDateString(),
            Achievement::RESET_WEEKLY => now()->startOfWeek()->toDateString(),
            default => null, // For one-time achievements
        };
    }

    /**
     * Get date range based on reset schedule.
     *
     * @param string $resetSchedule
     * @return array|null
     */
    protected function getDateRangeForSchedule(string $resetSchedule): ?array
    {
        return match ($resetSchedule) {
            Achievement::RESET_DAILY => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            Achievement::RESET_WEEKLY => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
            ],
            default => null, // No date range for one-time achievements
        };
    }

    /**
     * Format unlocked achievement for API response.
     *
     * @param Achievement $achievement
     * @return array
     */
    protected function formatUnlockedAchievement(Achievement $achievement): array
    {
        return [
            'id' => $achievement->id,
            'code' => $achievement->code,
            'name' => $achievement->name,
            'description' => $achievement->description,
            'icon_url' => $achievement->image_url,
            'type' => $achievement->type,
            'reward_coins' => $achievement->coin_reward,
            'reward_xp' => $achievement->reward_xp,
        ];
    }

    /**
     * Format updated progress for API response.
     *
     * @param Achievement $achievement
     * @param UserAchievement $progress
     * @return array
     */
    protected function formatUpdatedProgress(Achievement $achievement, UserAchievement $progress): array
    {
        return [
            'id' => $achievement->id,
            'code' => $achievement->code,
            'name' => $achievement->name,
            'type' => $achievement->type,
            'progress' => $progress->current_progress,
            'target' => $progress->target_progress,
            'percentage' => $progress->progress_percentage,
        ];
    }

    /**
     * Get all achievements progress for a user.
     *
     * @param User $user
     * @return array
     */
    public function getUserAchievementsProgress(User $user): array
    {
        $achievements = Achievement::active()->ordered()->get();
        $result = [];

        foreach ($achievements as $achievement) {
            $periodDate = $this->getPeriodDate($achievement->reset_schedule);

            $progress = UserAchievement::where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->where('period_date', $periodDate)
                ->first();

            $result[] = [
                'id' => $achievement->id,
                'code' => $achievement->code,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'icon_url' => $achievement->image_url,
                'type' => $achievement->type,
                'reward_coins' => $achievement->coin_reward,
                'reward_xp' => $achievement->reward_xp,
                'reset_schedule' => $achievement->reset_schedule,
                'progress' => $progress ? $progress->current_progress : 0,
                'target' => $achievement->target,
                'percentage' => $progress ? $progress->progress_percentage : 0,
                'is_completed' => $progress ? $progress->isCompleted() : false,
                'completed_at' => $progress?->completed_at?->toIso8601String(),
            ];
        }

        return $result;
    }

    /**
     * Get active challenges (daily, weekly, special) for a user.
     *
     * @param User $user
     * @return array
     */
    public function getActiveChallenges(User $user): array
    {
        $challenges = Achievement::active()
            ->whereIn('type', [
                Achievement::TYPE_CHALLENGE,
            ])
            ->ordered()
            ->get();

        $result = [];

        foreach ($challenges as $challenge) {
            $periodDate = $this->getPeriodDate($challenge->reset_schedule);

            $progress = UserAchievement::where('user_id', $user->id)
                ->where('achievement_id', $challenge->id)
                ->where('period_date', $periodDate)
                ->first();

            $result[] = [
                'id' => $challenge->id,
                'code' => $challenge->code,
                'name' => $challenge->name,
                'description' => $challenge->description,
                'icon_url' => $challenge->image_url,
                'type' => $challenge->type,
                'reward_coins' => $challenge->coin_reward,
                'reward_xp' => $challenge->reward_xp,
                'reset_schedule' => $challenge->reset_schedule,
                'progress' => $progress ? $progress->current_progress : 0,
                'target' => $challenge->target,
                'percentage' => $progress ? $progress->progress_percentage : 0,
                'is_completed' => $progress ? $progress->isCompleted() : false,
                'completed_at' => $progress?->completed_at?->toIso8601String(),
                'expires_at' => $this->getExpirationDate($challenge->reset_schedule),
            ];
        }

        return $result;
    }

    /**
     * Get expiration date based on reset schedule.
     *
     * @param string $resetSchedule
     * @return string|null
     */
    protected function getExpirationDate(string $resetSchedule): ?string
    {
        return match ($resetSchedule) {
            Achievement::RESET_DAILY => now()->endOfDay()->toIso8601String(),
            Achievement::RESET_WEEKLY => now()->endOfWeek()->toIso8601String(),
            default => null,
        };
    }
}
