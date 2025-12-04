<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    /**
     * Berikan koin kepada pengguna dan catat transaksinya.
     * Menggunakan database transaction untuk memastikan integritas data.
     * @param User $user
     * @param int $amount
     * @param array $relatedObject - Objek yang menjadi sumber koin
     * @param string $relatedObject['type'] - Tipe objek (e.g., 'Achievement', 'Checkin')
     * @param int $relatedObject['id'] - ID objek
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

            return $coinTransaction->toArray();
        });
    }

    /**
     * Kurangi koin pengguna dan catat transaksinya.
     * @param User $user
     * @param int $amount
     * @param array $relatedObject - Objek yang menjadi sumber pengurangan koin
     * @param string $relatedObject['type'] - Tipe objek
     * @param int $relatedObject['id'] - ID objek
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
     * @param array $relatedObject - Objek yang menjadi sumber EXP
     * @param string $relatedObject['type'] - Tipe objek
     * @param int $relatedObject['id'] - ID objek
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

            return $expTransaction->toArray();
        });
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

            return $checkin->toArray();
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

            return $review->toArray();
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

            return $review->toArray();
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
        array $additional_info,
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

            // Cek apakah pengguna sudah memiliki achievement ini
            $hasAchievement = \App\Models\UserAchievement::where(
                "user_id",
                $user->id,
            )
                ->where("achievement_id", $achievement_id)
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
                "additional_info" => $additional_info,
                "status" => true,
            ]);

            // Tambah counter di tabel user
            $user->increment("total_achievement");

            $metadata = [
                "type" => "Achievement",
                "id" => $achievement->id,
            ];

            // Berikan reward
            $this->addCoins($user, $achievement->coin_reward, $metadata);

            return $userAchievement->toArray();
        });
    }

    /**
     * Menyelesaikan challenge untuk pengguna jika belum pernah diselesaikan.
     * @param User $user
     * @param int $challenge_id
     * @param array $additional_info
     * @return array
     */
    public function completeChallenge(
        User $user,
        int $challenge_id,
        array $additional_info = [],
    ): array {
        return DB::transaction(function () use (
            $user,
            $challenge_id,
            $additional_info,
        ) {
            $challenge = \App\Models\Challenge::find($challenge_id);
            if (!$challenge) {
                throw new \InvalidArgumentException("Challenge not found");
            }

            if (!$challenge->status) {
                throw new \InvalidArgumentException(
                    "This challenge is currently not active.",
                );
            }

            // Cek apakah user sudah menyelesaikan challenge ini
            $hasCompleted = \App\Models\UserChallenge::where(
                "user_id",
                $user->id,
            )
                ->where("challenge_id", $challenge_id)
                ->where("status", true)
                ->exists();

            if ($hasCompleted) {
                throw new \InvalidArgumentException(
                    "You have already completed this challenge.",
                );
            }

            // Catat di tabel pivot user_challenges
            $userChallenge = \App\Models\UserChallenge::create([
                "user_id" => $user->id,
                "challenge_id" => $challenge_id,
                "additional_info" => $additional_info,
                "status" => true,
            ]);

            // Tambah counter di tabel user
            $user->increment("total_challenge");

            $metadata = [
                "type" => "Challenge",
                "id" => $challenge->id,
            ];

            // Berikan reward
            $this->addExp($user, $challenge->exp_reward, $metadata);

            return $userChallenge->toArray();
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
            $this->useCoins($user->id, $reward->coin_requirement, $metadata);
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

            return $userReward->toArray();
        });
    }

    /**
     * Ambil transaksi coin user dengan pagination dan filter tipe.
     * @param int $user_id
     * @param array $options - { page, limit, type }
     * @return array
     */
    public function getCoinTransactions(
        int $user_id,
        array $options = [],
    ): array {
        $page = $options["page"] ?? 1;
        $perPage = $options["per_page"] ?? 20;

        $query = \App\Models\CoinTransaction::where("user_id", $user_id);

        $totalItems = $query->count();

        $transactions = $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy("created_at", "desc")
            ->get();
        $totalPages = ceil($totalItems / $perPage);

        return [
            "transactions" => $transactions->toArray(),
            "pagination" => [
                "items" => $transactions->count(),
                "total" => $totalItems,
                "current_page" => (int) $page,
                "per_page" => (int) $perPage,
                "last_page" => (int) $totalPages,
            ],
        ];
    }

    /**
     * Ambil transaksi exp user dengan pagination dan filter tipe.
     * @param int $user_id
     * @param array $options - { page, limit, type }
     * @return array
     */
    public function getExpTransactions(int $user_id, array $options = []): array
    {
        $page = $options["page"] ?? 1;
        $perPage = $options["per_page"] ?? 20;

        $query = \App\Models\ExpTransaction::where("user_id", $user_id);

        $totalItems = $query->count();

        $transactions = $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy("created_at", "desc")
            ->get();
        $totalPages = ceil($totalItems / $perPage);

        return [
            "transactions" => $transactions->toArray(),
            "pagination" => [
                "items" => $transactions->count(),
                "total" => $totalItems,
                "current_page" => (int) $page,
                "last_page" => (int) $totalPages,
                "per_page" => (int) $perPage,
            ],
        ];
    }

    /**
     * Ambil achievements aktif beserta progress user.
     * @param int $user_id
     * @return array
     */
    public function getAchievements(int $user_id): array
    {
        $achievements = \App\Models\Achievement::where("status", true)
            ->with([
                "userAchievements" => function ($query) use ($user_id) {
                    $query->where("user_id", $user_id)->where("status", true);
                },
            ])
            ->orderBy("name", "asc")
            ->get();

        return $achievements->toArray();
    }

    /**
     * Ambil challenges berdasarkan status (active: dalam rentang tanggal saat ini).
     * @param int $user_id
     * @param array $options - { status }
     * @return array
     */
    public function getChallenges(int $user_id, array $options = []): array
    {
        $status = $options["status"] ?? "active";

        $query = \App\Models\Challenge::where("status", true);

        if ($status === "active") {
            $now = now();
            $query
                ->where("started_at", "<=", $now)
                ->where("ended_at", ">=", $now);
        }

        $challenges = $query
            ->with([
                "userChallenges" => function ($query) use ($user_id) {
                    $query->where("user_id", $user_id)->where("status", true);
                },
            ])
            ->orderBy("started_at", "desc")
            ->get();

        return $challenges->toArray();
    }

    /**
     * Ambil rewards aktif dengan filter kategori/tipe dan stok > 0.
     * @param array $options - { status }
     * @return array
     */
    public function getRewards(array $options = []): array
    {
        $status = $options["status"] ?? null;

        $query = \App\Models\Reward::where("status", true)->where(
            "stock",
            ">",
            0,
        );

        if ($status) {
            $query->where("status", $status);
        }

        $rewards = $query->orderBy("coin_requirement", "asc")->get();

        return $rewards->toArray();
    }

    /**
     * Fungsi listAchievements yang kompatibel dengan controller yang sudah ada
     * @return array
     */
    public function listAchievements(): array
    {
        $rows = \App\Models\Achievement::where("status", true)->get();
        return [
            "items" => $rows->toArray(),
            "total" => (int) $rows->count(),
            "current_page" => 1,
            "per_page" => (int) $rows->count(),
            "last_page" => 1,
        ];
    }

    /**
     * Fungsi listChallenges yang kompatibel dengan controller yang sudah ada
     * @return array
     */
    public function listChallenges(): array
    {
        $rows = \App\Models\Challenge::where("status", true)->get();
        return [
            "items" => $rows->toArray(),
            "total" => (int) $rows->count(),
            "current_page" => 1,
            "per_page" => (int) $rows->count(),
            "last_page" => 1,
        ];
    }

    /**
     * Fungsi listRewards yang kompatibel dengan controller yang sudah ada
     * @return array
     */
    public function listRewards(): array
    {
        $rows = \App\Models\Reward::where("status", true)->get();
        return [
            "items" => $rows->toArray(),
            "total" => (int) $rows->count(),
            "current_page" => 1,
            "per_page" => (int) $rows->count(),
            "last_page" => 1,
        ];
    }
}
