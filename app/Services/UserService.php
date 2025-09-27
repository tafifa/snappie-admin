<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;

class UserService
{
  /**
   * Get the user profile.
   *
   * @param  int  $userId
   * @return User
   */
  public function getById(int $userId): User
  {
    $user = User::findOrFail($userId);
    if (!$user) {
      throw new \Exception('User not found');
    }
    return $user;
  }

  /**
   * Update the user profile.
   *
   * @param  User  $user
   * @param  array  $data
   * @return bool
   */
  public function updateProfile(User $user, array $data): array
  {
    // 1. Filter hanya data yang boleh diubah melalui profil
    $fillableData = Arr::only($data, ['name', 'username', 'email', 'image_url', 'additional_info']);

    if (!$user) {
      throw new \Exception('User not found');
    }

    // 2. Update data
    $user->update($fillableData);

    return $fillableData;
  }

  /**
   * Menambahkan ID item ke daftar 'tersimpan' milik pengguna.
   * Metode ini mengasumsikan $savedKey adalah subkey yang valid.
   *
   * @param User $user Pengguna yang menyimpan item.
   * @param string $savedKey Subkey target di dalam JSON (misal: 'savedPlaces', 'savedPosts').
   * @param int $savedId ID dari item yang akan disimpan.
   * @return bool
   */
  public function addToSaved(User $user, string $savedKey, int $savedId): bool
  {
    // Ambil data JSON saat ini, atau array kosong jika belum ada
    $info = $user->additional_info ?? [];

    // Pastikan path array ada
    if (!isset($info['user_saved'][$savedKey]) || !is_array($info['user_saved'][$savedKey])) {
      $info['user_saved'][$savedKey] = [];
    }

    // Tambahkan ID ke array jika belum ada (mencegah duplikat)
    if (!in_array($savedId, $info['user_saved'][$savedKey])) {
      $info['user_saved'][$savedKey][] = $savedId;
    }

    // Simpan kembali seluruh objek additional_info
    return $user->update(['additional_info' => $info]);
  }

  /**
   * Menghapus ID item dari daftar 'tersimpan' milik pengguna.
   *
   * @param User $user
   * @param string $savedKey Subkey target di dalam JSON.
   * @param int $savedId
   * @return bool
   */
  public function removeFromSaved(User $user, string $savedKey, int $savedId): bool
  {
    $info = $user->additional_info ?? [];

    // Cek apakah list-nya ada dan merupakan array
    if (!isset($info['user_saved'][$savedKey]) || !is_array($info['user_saved'][$savedKey])) {
      return true; // Anggap berhasil karena memang sudah tidak ada
    }

    // Cari dan hapus ID dari array
    $keyToRemove = array_search($savedId, $info['user_saved'][$savedKey]);
    if ($keyToRemove !== false) {
      unset($info['user_saved'][$savedKey][$keyToRemove]);
      // Re-index array
      $info['user_saved'][$savedKey] = array_values($info['user_saved'][$savedKey]);
    }

    return $user->update(['additional_info' => $info]);
  }
}
