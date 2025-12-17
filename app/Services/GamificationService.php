<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Achievement;
use App\Models\UserActionLog;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    protected AchievementChecker $achievementChecker;

    public function __construct(AchievementChecker $achievementChecker)
    {
        $this->achievementChecker = $achievementChecker;
    }

    /**
     * Format gamification result - only include if there are completed achievements/challenges.
     * @param array $achievementResult
     * @param int $bonusCoins
     * @param int $bonusXp
     * @return array|null
     */
    protected function formatGamificationResult(
        array $achievementResult,
        int $bonusCoins = 0,
        int $bonusXp = 0
    ): ?array {
        $achievementsUnlocked = $achievementResult["achievements_unlocked"] ?? [];
        $challengesUpdated = $achievementResult["challenges_updated"] ?? [];

        // Filter completed challenges (progress == target)
        $challengesCompleted = array_filter($challengesUpdated, function($challenge) {
            return $challenge["progress"] >= $challenge["target"];
        });

        // Only return gamification data if there are unlocked achievements or completed challenges
        if (empty($achievementsUnlocked) && empty($challengesCompleted)) {
            return null;
        }

        $result = [];

        if (!empty($achievementsUnlocked)) {
            $result["achievements_unlocked"] = array_values($achievementsUnlocked);
        }

        if (!empty($challengesCompleted)) {
            $result["challenges_completed"] = array_values($challengesCompleted);
        }

        if ($bonusCoins > 0 || $bonusXp > 0) {
            $result["rewards"] = [
                "coins" => $bonusCoins,
                "xp" => $bonusXp,
            ];
        }

        return $result;
    }

    /**
     * Berikan koin kepada pengguna dan catat transaksinya.
     * Menggunakan database transaction untuk memastikan integritas data.
     * @param User $user
     * @param int $amount
     * @param array $metadata - Objek yang menjadi sumber koin
     * @param string $metadata['type'] - Tipe objek (e.g., 'Achievement', 'Checkin')
     * @param int $metadata['id'] - ID objek
     * @return array
     */
    public function addCoins(User $user, int $amount, array $metadata): array
    {
        if (!$user) {
            throw new \InvalidArgumentException("User not found");
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                "Coin amount must be greater than 0.",
            );
        }

        return DB::transaction(function () use ($user, $amount, $metadata) {
            // Tambah total koin pengguna
            $user->increment("total_coin", $amount);

            // Buat catatan transaksi
            $coinTransaction = \App\Models\CoinTransaction::create([
                "user_id" => $user->id,
                "amount" => $amount,
                "metadata" => $metadata,
            ]);

            // Log coin earned action for achievement tracking
            $this->achievementChecker->checkOnAction(
                $user,
                UserActionLog::ACTION_COIN_EARNED,
                [
                    "amount" => $amount,
                    "source" => $metadata,
                    "transaction_id" => $coinTransaction->id,
                ]
            );

            return $coinTransaction->toArray();
        });
    }

    /**
     * Kurangi koin pengguna dan catat transaksinya.
     * @param User $user
     * @param int $amount
     * @param array $metadata - Objek yang menjadi sumber pengurangan koin
     * @param string $metadata['type'] - Tipe objek
     * @param int $metadata['id'] - ID objek
     * @return array
     */
    public function useCoins(User $user, int $amount, array $metadata): array
    {
        if (!$user) {
            throw new \InvalidArgumentException("User not found");
        }

        if ($user->total_coin < $amount) {
            throw new \InvalidArgumentException(
                "User does not have enough coins.",
            );
        }

        return DB::transaction(function () use ($user, $amount, $metadata) {
            // Kurangi total koin pengguna
            $user->decrement("total_coin", $amount);

            // Buat catatan transaksi
            $coinTransaction = \App\Models\CoinTransaction::create([
                "user_id" => $user->id,
                "amount" => -$amount,
                "metadata" => $metadata,
            ]);

            return $coinTransaction->toArray();
        });
    }

    /**
     * Berikan EXP kepada pengguna dan catat transaksinya.
     * @param User $user
     * @param int $amount
     * @param array $metadata - Objek yang menjadi sumber EXP
     * @param string $metadata['type'] - Tipe objek
     * @param int $metadata['id'] - ID objek
     * @return array
     */
    public function addExp(User $user, int $amount, array $metadata): array
    {
        if (!$user) {
            throw new \InvalidArgumentException("User not found");
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                "EXP amount must be greater than zero.",
            );
        }

        return DB::transaction(function () use ($user, $amount, $metadata) {
            // Tambah total EXP pengguna
            $user->increment("total_exp", $amount);

            // Buat catatan transaksi
            $expTransaction = \App\Models\ExpTransaction::create([
                "user_id" => $user->id,
                "amount" => $amount,
                "metadata" => $metadata,
            ]);

            // Log exp earned action for achievement tracking
            $this->achievementChecker->checkOnAction(
                $user,
                UserActionLog::ACTION_XP_EARNED,
                [
                    "amount" => $amount,
                    "source" => $metadata,
                    "transaction_id" => $expTransaction->id,
                ]
            );

            return $expTransaction->toArray();
        });
    }

    /**
     * Get user stats including rank.
     * @param User $user
     * @return array
     */
    public function getUserStats(User $user): array
    {
        // Calculate rank based on total_exp (higher is better)
        $rank = User::where("total_exp", ">", $user->total_exp)->count() + 1;

        return [
            "total_coins" => $user->total_coin,
            "total_xp" => $user->total_exp,
            "rank" => $rank,
            "total_checkin" => $user->total_checkin,
            "total_review" => $user->total_review,
            "total_achievement" => $user->total_achievement,
            "total_challenge" => $user->total_challenge,
        ];
    }

    /**
     * Melakukan proses check-in untuk pengguna di sebuah tempat.
     * Menerapkan aturan: 1 check-in per tempat per bulan kalender.
     * @param User $user
     * @param array $payload - { place_id, latitude, longitude, proof_image_url, additional_info }
     * @return array
     */
    public function createCheckin(User $user, array $payload): array
    {
        return DB::transaction(function () use ($user, $payload) {
            $place = \App\Models\Place::find($payload["place_id"]);
            if (!$place) {
                throw new \InvalidArgumentException("Place not found");
            }

            // 1. Cek status tempat
            if (!$place->status) {
                throw new \InvalidArgumentException(
                    "This place is currently not active.",
                );
            }

            // 2. Cek apakah pengguna sudah check-in di tempat ini pada bulan kalender yang sama
            $now = now();
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();

            $hasCheckedInThisMonth = \App\Models\Checkin::where(
                "user_id",
                $user->id,
            )
                ->where("place_id", $payload["place_id"])
                ->whereBetween("created_at", [$startOfMonth, $endOfMonth])
                ->exists();

            // 3. Jika sudah, berikan pesan error
            if ($hasCheckedInThisMonth) {
                throw new \InvalidArgumentException(
                    "You have already checked in at this place this month.",
                );
            }

            // Hitung reward sederhana
            $coinsEarned = $place->coin_reward;
            $expEarned = $place->exp_reward;

            // Buat checkin
            $checkin = \App\Models\Checkin::create([
                "user_id" => $user->id,
                "place_id" => $payload["place_id"],
                "latitude" => $payload["latitude"] ?? null,
                "longitude" => $payload["longitude"] ?? null,
                "image_url" => $payload["image_url"] ?? null,
                "additional_info" => $payload["additional_info"] ?? null,
                "status" => true,
            ]);

            // Update statistik checkin (tanpa coins/exp - akan di-handle oleh addCoins/addExp)
            $user->increment("total_checkin");

            $place->increment("total_checkin");

            $metadata = [
                "type" => "Checkin",
                "id" => $checkin->id,
            ];

            // Transaksi coin (ini akan increment total_coin di user)
            $this->addCoins($user, $coinsEarned, $metadata);

            // Transaksi exp (ini akan increment total_exp di user)
            $this->addExp($user, $expEarned, $metadata);

            // Check achievements for checkin action
            $achievementResult = $this->achievementChecker->checkOnAction(
                $user,
                UserActionLog::ACTION_CHECKIN,
                [
                    "checkin_id" => $checkin->id,
                    "place_id" => $place->id,
                    "place_name" => $place->name,
                ],
            );

            // Calculate bonus rewards from achievements
            $bonusCoins = 0;
            $bonusXp = 0;
            foreach (
                $achievementResult["achievements_unlocked"]
                as $achievement
            ) {
                $bonusCoins += $achievement["reward_coins"] ?? 0;
                $bonusXp += $achievement["reward_xp"] ?? 0;
            }

            $result = [
                "checkin" => [
                    "id" => $checkin->id,
                    "place_id" => $place->id,
                    "place_name" => $place->name,
                    "coins_earned" => $coinsEarned,
                    "xp_earned" => $expEarned,
                ],
            ];

            // Add gamification result if there are completed achievements/challenges
            $gamification = $this->formatGamificationResult($achievementResult, $bonusCoins, $bonusXp);
            if ($gamification !== null) {
                $result["gamification"] = $gamification;
            }

            return $result;
        });
    }

    /**
     * Menambahkan ulasan baru dan memastikan statistik tempat diperbarui.
     * Fungsi ini juga kompatibel dengan controller yang sudah ada.
     * @param User $user
     * @param array $payload - { place_id, content, rating, image_urls, additional_info }
     * @return array
     */
    public function createReview(User $user, array $payload): array
    {
        return DB::transaction(function () use ($user, $payload) {
            $place = \App\Models\Place::find($payload["place_id"]);
            if (!$place) {
                throw new \InvalidArgumentException("Place not found");
            }

            // 1. Cek status tempat
            if (!$place->status) {
                throw new \InvalidArgumentException(
                    "This place is currently not active.",
                );
            }
            // 2. Cek apakah pengguna sudah review tempat ini pada bulan kalender yang sama
            $now = now();
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();

            $hasReviewedInThisMonth = \App\Models\Review::where(
                "user_id",
                $user->id,
            )
                ->where("place_id", $payload["place_id"])
                ->whereBetween("created_at", [$startOfMonth, $endOfMonth])
                ->exists();

            // 3. Jika sudah, berikan pesan error
            if ($hasReviewedInThisMonth) {
                throw new \InvalidArgumentException(
                    "You have already reviewed this place this month.",
                );
            }

            // Hitung reward sederhana
            $coinsEarned = $place->coin_reward;
            $expEarned = $place->exp_reward;

            // 4. Buat ulasan baru
            $review = \App\Models\Review::create([
                "user_id" => $user->id,
                "place_id" => $payload["place_id"],
                "content" => $payload["content"],
                "rating" => $payload["rating"],
                "image_urls" => $payload["image_urls"] ?? null,
                "additional_info" => $payload["additional_info"] ?? null,
                "status" => true,
            ]);

            // Update statistik review (tanpa coins/exp - akan di-handle oleh addCoins/addExp)
            $user->increment("total_review");

            // Hitung ulang rata-rata rating
            $reviewStats = \App\Models\Review::where(
                "place_id",
                $payload["place_id"],
            )
                ->selectRaw(
                    "COUNT(id) as review_count, SUM(rating) as total_rating",
                )
                ->first();

            $newAvgRating =
                $reviewStats->review_count > 0
                    ? round(
                        $reviewStats->total_rating / $reviewStats->review_count,
                        2,
                    )
                    : 0;

            $place->update([
                "total_review" => ($place->total_review ?? 0) + 1,
                "avg_rating" => $newAvgRating,
            ]);

            $metadata = [
                "type" => "Review",
                "id" => $review->id,
            ];

            // Transaksi coin
            $this->addCoins($user, $coinsEarned, $metadata);

            // Transaksi exp
            $this->addExp($user, $expEarned, $metadata);

            // Check achievements for review action
            $achievementResult = $this->achievementChecker->checkOnAction(
                $user,
                UserActionLog::ACTION_REVIEW,
                [
                    "review_id" => $review->id,
                    "place_id" => $place->id,
                    "place_name" => $place->name,
                    "rating" => $payload["rating"],
                ],
            );

            // Calculate bonus rewards from achievements
            $bonusCoins = 0;
            $bonusXp = 0;
            foreach (
                $achievementResult["achievements_unlocked"]
                as $achievement
            ) {
                $bonusCoins += $achievement["reward_coins"] ?? 0;
                $bonusXp += $achievement["reward_xp"] ?? 0;
            }

            $result = [
                "review" => [
                    "id" => $review->id,
                    "place_id" => $place->id,
                    "place_name" => $place->name,
                    "rating" => $review->rating,
                    "coins_earned" => $coinsEarned,
                    "xp_earned" => $expEarned,
                ],
            ];

            // Add gamification result if there are completed achievements/challenges
            $gamification = $this->formatGamificationResult($achievementResult, $bonusCoins, $bonusXp);
            if ($gamification !== null) {
                $result["gamification"] = $gamification;
            }

            return $result;
        });
    }

    /**
     * Mengupdate ulasan yang sudah ada.
     * @param User $user
     * @param int $review_id
     * @param array $payload - { rating, content, image_urls, additional_info }
     * @return array
     */
    public function updateReview(
        User $user,
        int $review_id,
        array $payload,
    ): array {
        return DB::transaction(function () use ($user, $review_id, $payload) {
            $review = \App\Models\Review::find($review_id);
            if (!$review) {
                throw new \InvalidArgumentException("Review not found");
            }

            // Pastikan user adalah pemilik review
            if ($review->user_id !== $user->id) {
                throw new \InvalidArgumentException(
                    "You are not authorized to update this review",
                );
            }

            // Update fields yang diberikan
            if (isset($payload["rating"])) {
                $review->rating = $payload["rating"];
            }
            if (array_key_exists("content", $payload)) {
                $review->content = $payload["content"];
            }
            if (array_key_exists("image_urls", $payload)) {
                $review->image_urls = $payload["image_urls"];
            }
            if (array_key_exists("additional_info", $payload)) {
                $review->additional_info = $payload["additional_info"];
            }

            $review->save();

            // Hitung ulang rata-rata rating jika rating berubah
            if (isset($payload["rating"])) {
                $reviewStats = \App\Models\Review::where(
                    "place_id",
                    $review->place_id,
                )
                    ->selectRaw(
                        "COUNT(id) as review_count, SUM(rating) as total_rating",
                    )
                    ->first();

                $newAvgRating =
                    $reviewStats->review_count > 0
                        ? round(
                            $reviewStats->total_rating /
                                $reviewStats->review_count,
                            2,
                        )
                        : 0;

                \App\Models\Place::where("id", $review->place_id)->update([
                    "avg_rating" => $newAvgRating,
                ]);
            }

            return [
                "action_result" => $review->toArray(),
                "user_stats" => $this->getUserStats($user->fresh()),
            ];
        });
    }

    /**
     * Memberikan achievement kepada pengguna jika belum dimiliki.
     * @param User $user
     * @param int $achievement_id
     * @param array $additional_info - informasi tambahan yang akan disimpan
     * @return array
     */
    public function grantAchievement(
        User $user,
        int $achievement_id,
        array $additional_info = [],
    ): array {
        return DB::transaction(function () use (
            $user,
            $achievement_id,
            $additional_info,
        ) {
            $achievement = \App\Models\Achievement::find($achievement_id);
            if (!$achievement) {
                throw new \InvalidArgumentException("Achievement not found");
            }

            if (!$achievement->status) {
                throw new \InvalidArgumentException(
                    "This achievement is currently not active.",
                );
            }

            // Get period date for this achievement
            $periodDate = $this->achievementChecker->getPeriodDate(
                $achievement->reset_schedule,
            );

            // Cek apakah pengguna sudah memiliki achievement ini
            $hasAchievement = \App\Models\UserAchievement::where(
                "user_id",
                $user->id,
            )
                ->where("achievement_id", $achievement_id)
                ->where("period_date", $periodDate)
                ->where("status", true)
                ->exists();

            if ($hasAchievement) {
                throw new \InvalidArgumentException(
                    "You already have this achievement.",
                );
            }

            // Catat di tabel pivot user_achievements
            $userAchievement = \App\Models\UserAchievement::create([
                "user_id" => $user->id,
                "achievement_id" => $achievement_id,
                "current_progress" => $achievement->target,
                "target_progress" => $achievement->target,
                "additional_info" => $additional_info,
                "status" => true,
                "completed_at" => now(),
                "period_date" => $periodDate,
            ]);

            // Tambah counter di tabel user (only for one-time achievements)
            if ($achievement->isOneTime()) {
                $user->increment("total_achievement");
            }

            $metadata = [
                "type" => "Achievement",
                "id" => $achievement->id,
                "code" => $achievement->code,
            ];

            // Berikan reward
            if ($achievement->coin_reward > 0) {
                $this->addCoins($user, $achievement->coin_reward, $metadata);
            }
            if ($achievement->reward_xp > 0) {
                $this->addExp($user, $achievement->reward_xp, $metadata);
            }

            return [
                "achievement" => [
                    "id" => $achievement->id,
                    "code" => $achievement->code,
                    "name" => $achievement->name,
                    "description" => $achievement->description,
                    "icon_url" => $achievement->image_url,
                    "reward_coins" => $achievement->coin_reward,
                    "reward_xp" => $achievement->reward_xp,
                ],
                "user_achievement" => $userAchievement->toArray(),
                "user_stats" => $this->getUserStats($user->fresh()),
            ];
        });
    }

    /**
     * Menyelesaikan challenge untuk pengguna jika belum pernah diselesaikan.
     * @param User $user
     * @param int $achievement_id (challenge)
     * @param array $additional_info
     * @return array
     */
    public function completeChallenge(
        User $user,
        int $achievement_id,
        array $additional_info = [],
    ): array {
        return DB::transaction(function () use (
            $user,
            $achievement_id,
            $additional_info,
        ) {
            $challenge = Achievement::where('type', Achievement::TYPE_CHALLENGE)
                ->find($achievement_id);
            if (!$challenge) {
                throw new \InvalidArgumentException("Challenge not found");
            }

            if (!$challenge->status) {
                throw new \InvalidArgumentException(
                    "This challenge is currently not active.",
                );
            }

            // Get period date based on reset schedule
            $periodDate = $this->achievementChecker->getPeriodDate($challenge->reset_schedule);

            // Cek apakah user sudah menyelesaikan challenge ini untuk periode ini
            $hasCompleted = \App\Models\UserAchievement::where(
                "user_id",
                $user->id,
            )
                ->where("achievement_id", $achievement_id)
                ->where("period_date", $periodDate)
                ->where("status", true)
                ->exists();

            if ($hasCompleted) {
                throw new \InvalidArgumentException(
                    "You have already completed this challenge for this period.",
                );
            }

            // Catat di tabel user_achievements
            $userAchievement = \App\Models\UserAchievement::updateOrCreate(
                [
                    "user_id" => $user->id,
                    "achievement_id" => $achievement_id,
                    "period_date" => $periodDate,
                ],
                [
                    "current_progress" => $challenge->criteria_target,
                    "target_progress" => $challenge->criteria_target,
                    "status" => true,
                    "completed_at" => now(),
                    "additional_info" => $additional_info,
                ]
            );

            // Tambah counter di tabel user
            $user->increment("total_challenge");

            $metadata = [
                "type" => "Challenge",
                "id" => $challenge->id,
            ];

            // Berikan reward coins
            if ($challenge->coin_reward > 0) {
                $this->addCoins($user, $challenge->coin_reward, $metadata);
            }

            // Berikan reward exp
            if ($challenge->reward_xp > 0) {
                $this->addExp($user, $challenge->reward_xp, $metadata);
            }

            return [
                "challenge" => $challenge->toArray(),
                "user_achievement" => $userAchievement->toArray(),
                "user_stats" => $this->getUserStats($user->fresh()),
            ];
        });
    }

    /**
     * Memproses penukaran reward oleh pengguna.
     * @param User $user
     * @param int $reward_id
     * @return array
     */
    public function redeemReward(
        User $user,
        int $reward_id,
        array $additional_info = [],
    ): array {
        return DB::transaction(function () use (
            $user,
            $reward_id,
            $additional_info,
        ) {
            $reward = \App\Models\Reward::find($reward_id);
            if (!$reward) {
                throw new \InvalidArgumentException("Reward not found");
            }

            if (!$reward->status) {
                throw new \InvalidArgumentException(
                    "This reward is currently not active.",
                );
            }

            // 1. Validasi
            if ($user->total_coin < $reward->coin_requirement) {
                throw new \InvalidArgumentException("Koin tidak mencukupi.");
            }
            if ($reward->stock <= 0) {
                throw new \InvalidArgumentException("Stok hadiah habis.");
            }
            if (!$reward->status) {
                throw new \InvalidArgumentException("Hadiah ini tidak aktif.");
            }

            $metadata = [
                "type" => "Reward",
                "id" => $reward->id,
            ];

            // 2. Kurangi koin pengguna & stok reward
            $this->useCoins($user, $reward->coin_requirement, $metadata);
            $reward->decrement("stock");

            $additional_info = array_merge(
                ["redemption_code" => "XYZ-" . time()],
                $additional_info,
            );

            // 3. Catat di tabel user_rewards
            $userReward = \App\Models\UserReward::create([
                "user_id" => $user->id,
                "reward_id" => $reward_id,
                "status" => true,
                "additional_info" => $additional_info,
            ]);

            return [
                "reward" => $reward->toArray(),
                "user_reward" => $userReward->toArray(),
                "user_stats" => $this->getUserStats($user->fresh()),
            ];
        });
    }

    /**
     * Get coin transactions for a user.
     * @param User $user
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getCoinTransactions(
        User $user,
        int $limit = 20,
        int $offset = 0,
    ): array {
        $transactions = \App\Models\CoinTransaction::where("user_id", $user->id)
            ->orderBy("created_at", "desc")
            ->offset($offset)
            ->limit($limit)
            ->get();

        $total = \App\Models\CoinTransaction::where(
            "user_id",
            $user->id,
        )->count();

        return [
            "transactions" => $transactions->toArray(),
            "total" => $total,
            "limit" => $limit,
            "offset" => $offset,
        ];
    }

    /**
     * Get EXP transactions for a user.
     * @param User $user
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getExpTransactions(
        User $user,
        int $limit = 20,
        int $offset = 0,
    ): array {
        $transactions = \App\Models\ExpTransaction::where("user_id", $user->id)
            ->orderBy("created_at", "desc")
            ->offset($offset)
            ->limit($limit)
            ->get();

        $total = \App\Models\ExpTransaction::where(
            "user_id",
            $user->id,
        )->count();

        return [
            "transactions" => $transactions->toArray(),
            "total" => $total,
            "limit" => $limit,
            "offset" => $offset,
        ];
    }

    /**
     * Get achievements for a user with progress.
     * @param User $user
     * @return array
     */
    public function getAchievements(User $user): array
    {
        return $this->achievementChecker->getUserAchievementsProgress($user);
    }

    /**
     * Get active challenges for a user.
     * @param User $user
     * @return array
     */
    public function getChallenges(User $user): array
    {
        return $this->achievementChecker->getActiveChallenges($user);
    }

    /**
     * Get available rewards.
     * @param User $user
     * @return array
     */
    public function getRewards(User $user): array
    {
        $rewards = \App\Models\Reward::where("status", true)
            ->orderBy("coin_requirement", "asc")
            ->get();

        return $rewards
            ->map(function ($reward) use ($user) {
                return [
                    "id" => $reward->id,
                    "name" => $reward->name,
                    "description" => $reward->description,
                    "image_url" => $reward->image_url,
                    "coin_requirement" => $reward->coin_requirement,
                    "stock" => $reward->stock,
                    "can_redeem" =>
                        $user->total_coin >= $reward->coin_requirement &&
                        $reward->stock > 0,
                ];
            })
            ->toArray();
    }

    /**
     * List all achievements (for admin).
     * @return array
     */
    public function listAchievements(): array
    {
        return \App\Models\Achievement::orderBy("display_order", "asc")
            ->get()
            ->toArray();
    }

    /**
     * List all challenges (for admin).
     * @return array
     */
    public function listChallenges(): array
    {
        return Achievement::where("status", true)
            ->where("type", Achievement::TYPE_CHALLENGE)
            ->orderBy("display_order", "asc")
            ->get()
            ->toArray();
    }

    /**
     * List all rewards (for admin).
     * @return array
     */
    public function listRewards(): array
    {
        return \App\Models\Reward::where("status", true)
            ->orderBy("coin_requirement", "asc")
            ->get()
            ->toArray();
    }
}
