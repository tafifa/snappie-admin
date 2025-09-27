<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\Place;
use App\Models\Checkin;
use App\Models\CoinTransaction;
use App\Models\ExpTransaction;
use App\Models\Review;
use App\Models\UserAchievement;
use App\Models\Challenge;
use App\Models\Reward;
use App\Models\UserChallenge;
use App\Models\UserReward;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class GamificationService
{
  /**
   * Berikan koin kepada pengguna dan catat transaksinya.
   * Menggunakan database transaction untuk memastikan integritas data.
   *
   * @param User $user Pengguna yang menerima koin.
   * @param int $amount Jumlah koin.
   * @param Model $relatedObject Objek yang menjadi sumber koin (e.g., Achievement).
   * @return CoinTransaction
   * @throws Exception
   */
  public function addCoins(User $user, int $amount, Model $relatedObject): CoinTransaction
  {
    if ($amount <= 0) {
      throw new Exception("Coin amount must be greater than 0.");
    }

    return DB::transaction(function () use ($user, $amount, $relatedObject) {
      // 1. Tambah total koin pengguna
      $user->increment('total_coin', $amount);

      // 2. Buat catatan transaksi
      $coinTransaction = $user->coinTransactions()->create([
        'amount' => $amount,
        'related_to_id' => $relatedObject->id,
        'related_to_type' => get_class($relatedObject),
      ]);
      return $coinTransaction;
    });
  }

  /**
   * Kurangi koin pengguna dan catat transaksinya.
   *
   * @param User $user Pengguna yang mengurangi koin.
   * @param int $amount Jumlah koin.
   * @param Model $relatedObject Objek yang menjadi sumber koin (e.g., Achievement).
   * @return CoinTransaction
   * @throws Exception
   */
  public function useCoins(User $user, int $amount, Model $relatedObject): CoinTransaction
  {
    if ($user->total_coin < $amount) {
      throw new Exception('User does not have enough coins.');
    }

    return DB::transaction(function () use ($user, $amount, $relatedObject) {
      // 1. Kurangi total koin pengguna
      $user->decrement('total_coin', $amount);

      // 2. Buat catatan transaksi
      $coinTransaction = $user->coinTransactions()->create([
        'amount' => -$amount,
        'related_to_id' => $relatedObject->id,
        'related_to_type' => get_class($relatedObject),
      ]);
      return $coinTransaction;
    });
  }

  /**
   * Berikan EXP kepada pengguna dan catat transaksinya.
   *
   * @param User $user Pengguna yang menerima EXP.
   * @param int $amount Jumlah EXP.
   * @param Model $relatedObject Objek yang menjadi sumber EXP (e.g., Challenge).
   * @return ExpTransaction
   * @throws Exception
   */
  public function addExp(User $user, int $amount, Model $relatedObject): ExpTransaction
  {
    if ($amount <= 0) {
      throw new Exception('EXP amount must be greater than zero.');
    }

    return DB::transaction(function () use ($user, $amount, $relatedObject) {
      $user->increment('total_exp', $amount);

      $expTransaction = $user->expTransactions()->create([
        'amount' => $amount,
        'related_to_id' => $relatedObject->id,
        'related_to_type' => get_class($relatedObject),
      ]);
      return $expTransaction;
    });
  }

  /**
   * Melakukan proses check-in untuk pengguna di sebuah tempat.
   * Menerapkan aturan: 1 check-in per tempat per bulan kalender.
   *
   * @param User $user Pengguna yang melakukan check-in.
   * @param Place $place Tempat di mana check-in dilakukan.
   * @param array $data Data tambahan untuk check-in.
   * @return Checkin Objek Checkin yang baru dibuat.
   * @throws Exception Jika validasi gagal (tempat tidak aktif, sudah check-in bulan ini).
   */
  public function performCheckin(User $user, Place $place, array $data = []): Checkin
  {
    // 1. Cek status tempat (place)
    if (!$place->status) {
      throw new Exception('This place is currently not active.');
    }

    // 2. Cek apakah pengguna sudah check-in di tempat ini pada bulan kalender yang sama
    $hasCheckedInThisMonth = $user->checkins()
      ->where('place_id', $place->id)
      ->whereYear('created_at', Carbon::now()->year)
      ->whereMonth('created_at', Carbon::now()->month)
      ->exists();

    // 3. Jika sudah, lewati proses dan berikan pesan error
    if ($hasCheckedInThisMonth) {
      throw new Exception('You have already checked in at this place this month.');
    }

    // 4. Jika belum, jalankan semua proses dalam satu transaksi yang aman
    return DB::transaction(function () use ($user, $place, $data) {
      // A. Buat data check-in baru
      $checkin = $user->checkins()->create([
        'place_id' => $place->id,
        'latitude' => $data['latitude'] ?? null,
        'longitude' => $data['longitude'] ?? null,
        'image_url' => $data['image_url'] ?? null,
        'additional_info' => $data['additional_info'] ?? null,
        'status' => true, // Langsung set status true
      ]);

      // B. Update statistik pengguna dan tempat
      $user->increment('total_checkin');
      $place->increment('total_checkin');

      // C. Berikan reward (koin & EXP)
      $this->addCoins($user, $place->coin_reward, $checkin);
      $this->addExp($user, $place->exp_reward, $checkin);

      return $checkin;
    });
  }

  /**
   * Menambahkan ulasan baru dan memastikan statistik tempat diperbarui.
   *
   * @param User $user Pengguna yang menulis ulasan.
   * @param Place $place Tempat yang diulas.
   * @param array $data Data ulasan, contoh: ['content' => '...', 'rating' => 5].
   * @return Review Objek ulasan yang baru dibuat.
   */
  public function createReview(User $user, Place $place, array $data): Review
  {
    // 1. Cek status tempat (place)
    if (!$place->status) {
      throw new Exception('This place is currently not active.');
    }

    // 2. Cek apakah pengguna sudah review tempat ini pada bulan kalender yang sama
    $hasReviewedInThisMonth = $user->reviews()
      ->where('place_id', $place->id)
      ->whereYear('created_at', Carbon::now()->year)
      ->whereMonth('created_at', Carbon::now()->month)
      ->exists();

    // 3. Jika sudah, lewati proses dan berikan pesan error
    if ($hasReviewedInThisMonth) {
      throw new Exception('You have already reviewed this place this month.');
    }

    // 4. Jika belum, jalankan semua proses dalam satu transaksi yang aman
    return DB::transaction(function () use ($user, $place, $data) {
      // A. Buat ulasan baru
      $review = $place->reviews()->create([
        'user_id' => $user->id,
        'content' => $data['content'],
        'rating' => $data['rating'],
        'image_urls' => $data['image_urls'] ?? null,
        'additional_info' => $data['additional_info'] ?? null,
        'status' => true, // Langsung set status true
      ]);

      // B. Update statistik pengguna dan tempat
      $user->increment('total_review');
      $place->increment('total_review');

      // Hitung ulang rata-rata rating berdasarkan semua review yang ada
      $reviewStats = $place->reviews()->selectRaw('COUNT(*) as review_count, SUM(rating) as total_rating')->first();

      $newAvgRating = ($reviewStats->review_count > 0)
        ? $reviewStats->total_rating / $reviewStats->review_count
        : 0;

      $place->update(['avg_rating' => round($newAvgRating, 2)]);

      // C. Berikan reward (koin & EXP)
      $this->addCoins($user, $place->coin_reward, $review);
      $this->addExp($user, $place->exp_reward, $review);

      return $review;
    });
  }

  /**
   * Memberikan achievement kepada pengguna jika belum dimiliki.
   *
   * @param User $user
   * @param Achievement $achievement
   * @return UserAchievement|bool True jika achievement baru diberikan, false jika sudah dimiliki.
   */
  public function grantAchievement(User $user, Achievement $achievement): UserAchievement
  {
    // Cek apakah pengguna sudah memiliki achievement ini
    if ($user->achievements()->completed()->where('achievement_id', $achievement->id)->exists()) {
      throw new Exception('You have already have this achievement.');
    }

    return DB::transaction(function () use ($user, $achievement) {
      // 1. Catat di tabel pivot user_achievements
      $user->achievements()->attach($achievement->id, ['status' => true]);

      // 2. Tambah counter di tabel user
      $user->increment('total_achievement');

      // 3. Berikan reward (menggunakan metode yang sudah ada jika ada)
      $this->addCoins($user, $achievement->coin_reward, $achievement);
      $this->addExp($user, $achievement->exp_reward, $achievement);

      // Ambil dan kembalikan instance UserAchievement yang baru dibuat
      return $user->achievements()->wherePivot('achievement_id', $achievement->id)->latest()->first();
    });
  }

  /**
   * Menyelesaikan challenge untuk pengguna jika belum pernah diselesaikan.
   *
   * @param User $user
   * @param Challenge $challenge
   * @return UserChallenge
   * @throws Exception
   */
  public function completeChallenge(User $user, Challenge $challenge): UserChallenge
  {
    // Check if user has already completed this challenge and has status true
    if ($user->challenges()->completed()->where('challenge_id', $challenge->id)->exists()) {
      throw new Exception('You have already completed this challenge.');
    }

    return DB::transaction(function () use ($user, $challenge) {
      // 1. Catat di tabel pivot user_challenges
      $user->challenges()->attach($challenge->id, ['status' => true]);

      // 2. Tambah counter di tabel user
      $user->increment('total_challenge');

      // 3. Berikan reward (koin & EXP)
      $this->addCoins($user, $challenge->coin_reward, $challenge);
      $this->addExp($user, $challenge->exp_reward, $challenge);

      // Ambil dan kembalikan instance UserChallenge yang baru dibuat
      return $user->challenges()->wherePivot('challenge_id', $challenge->id)->latest()->first();
    });
  }

  /**
   * Memproses penukaran reward oleh pengguna.
   *
   * @param User $user
   * @param Reward $reward
   * @return \App\Models\UserReward
   * @throws Exception
   */
  public function redeemReward(User $user, Reward $reward): UserReward
  {
    // 1. Validasi (apakah koin cukup, apakah stok ada, apakah reward aktif)
    if ($user->total_coin < $reward->coin_requirement) {
      throw new Exception('Koin tidak mencukupi.');
    }
    if ($reward->stock <= 0) {
      throw new Exception('Stok hadiah habis.');
    }
    if (!$reward->status) {
      throw new Exception('Hadiah ini tidak aktif.');
    }

    return DB::transaction(function () use ($user, $reward) {
      // 2. Kurangi koin pengguna & stok reward
      $this->useCoins($user, $reward->coin_requirement, $reward); // Menggunakan metode yang sudah ada
      $reward->decrement('stock');

      // 3. Catat di tabel user_rewards
      $user->rewards()->attach($reward->id, [
        'status' => true, // atau 'claimed', dll.
        'additional_info' => ['redemption_code' => 'XYZ-' . uniqid()] // Contoh info tambahan
      ]);

      // 4. Ambil dan kembalikan instance UserReward yang baru dibuat
      return $user->rewards()->wherePivot('reward_id', $reward->id)->latest()->first();
    });
  }


}
