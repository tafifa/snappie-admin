<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GamificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Rules dasar yang bisa digunakan untuk semua method
        $method = $this->route()->getActionMethod();
        return match ($method) {
            'performCheckin' => [
                'place_id' => 'required|integer',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'image_url' => 'sometimes|url|max:500',
                'additional_info' => 'sometimes|array',
            ],
            'createReview' => [
                'place_id' => 'required|integer',
                'content' => 'required|string|max:1000',
                'rating' => 'required|integer|min:1|max:5',
                'image_urls' => 'sometimes|array',
                'image_urls.*' => 'sometimes|url|max:500',
                'additional_info' => 'sometimes|array',
            ],
            'grantAchievement' => [
                'achievement_id' => 'required|integer|exists:achievements,id',
            ],
            'completeChallenge' => [
                'challenge_id' => 'required|integer|exists:challenges,id',
            ],
            'redeemReward' => [
                'reward_id' => 'required|integer|exists:rewards,id',
            ],
            'addCoins' => [
                'amount' => 'required|integer|min:1',
                'related_to_id' => 'required|integer',
                'related_to_type' => 'required|string|max:100',
                'description' => 'sometimes|string|max:500',
            ],
            'useCoins' => [
                'amount' => 'required|integer|min:1',
                'related_to_id' => 'required|integer',
                'related_to_type' => 'required|string|max:100',
                'description' => 'sometimes|string|max:500',
            ],
            'addExp' => [
                'amount' => 'required|integer|min:1',
                'related_to_id' => 'required|integer',
                'related_to_type' => 'required|string|max:100',
                'description' => 'sometimes|string|max:500',
            ],
            'getCoinTransactions' => [
                'per_page' => 'sometimes|integer|min:1|max:100',
            ],
            'getExpTransactions' => [
                'per_page' => 'sometimes|integer|min:1|max:100',
            ],
            default => []
        };
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'ID pengguna wajib diisi.',
            'user_id.exists' => 'Pengguna tidak ditemukan.',
            'place_id.required' => 'ID tempat wajib diisi.',
            'place_id.exists' => 'Tempat tidak ditemukan.',
            'achievement_id.required' => 'ID pencapaian wajib diisi.',
            'achievement_id.exists' => 'Pencapaian tidak ditemukan.',
            'challenge_id.required' => 'ID tantangan wajib diisi.',
            'challenge_id.exists' => 'Tantangan tidak ditemukan.',
            'reward_id.required' => 'ID hadiah wajib diisi.',
            'reward_id.exists' => 'Hadiah tidak ditemukan.',
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.integer' => 'Jumlah harus berupa angka.',
            'amount.min' => 'Jumlah minimal 1.',
            'related_to_id.required' => 'ID terkait wajib diisi.',
            'related_to_id.integer' => 'ID terkait harus berupa angka.',
            'related_to_type.required' => 'Tipe terkait wajib diisi.',
            'related_to_type.string' => 'Tipe terkait harus berupa teks.',
            'related_to_type.max' => 'Tipe terkait maksimal 100 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus antara -90 dan 90.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 dan 180.',
            'image_url.url' => 'URL gambar tidak valid.',
            'image_url.max' => 'URL gambar maksimal 500 karakter.',
            'image_urls.array' => 'URL gambar harus berupa array.',
            'image_urls.*.url' => 'Setiap URL gambar harus valid.',
            'image_urls.*.max' => 'Setiap URL gambar maksimal 500 karakter.',
            'additional_info.array' => 'Informasi tambahan harus berupa array.',
            'content.required' => 'Konten wajib diisi.',
            'content.string' => 'Konten harus berupa teks.',
            'content.max' => 'Konten maksimal 1000 karakter.',
            'rating.required' => 'Rating wajib diisi.',
            'rating.integer' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal 1.',
            'rating.max' => 'Rating maksimal 5.',
            'per_page.integer' => 'Jumlah per halaman harus berupa angka.',
            'per_page.min' => 'Jumlah per halaman minimal 1.',
            'per_page.max' => 'Jumlah per halaman maksimal 100.',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'ID pengguna',
            'place_id' => 'ID tempat',
            'achievement_id' => 'ID pencapaian',
            'challenge_id' => 'ID tantangan',
            'reward_id' => 'ID hadiah',
            'amount' => 'jumlah',
            'related_to_id' => 'ID terkait',
            'related_to_type' => 'tipe terkait',
            'description' => 'deskripsi',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'image_url' => 'URL gambar',
            'image_urls' => 'URL gambar',
            'additional_info' => 'informasi tambahan',
            'content' => 'konten',
            'rating' => 'rating',
            'per_page' => 'jumlah per halaman',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422));
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         $method = $this->route()->getActionMethod();

    //         // Validasi berdasarkan method
    //         switch ($method) {
    //             case 'performCheckin':
    //                 $this->validateCheckin($validator);
    //                 break;
    //             case 'createReview':
    //                 $this->validateReview($validator);
    //                 break;
    //             case 'grantAchievement':
    //                 $this->validateGrantAchievement($validator);
    //                 break;
    //             case 'completeChallenge':
    //                 $this->validateCompleteChallenge($validator);
    //                 break;
    //             case 'redeemReward':
    //                 $this->validateRedeemReward($validator);
    //                 break;
    //             case 'addCoins':
    //             case 'useCoins':
    //                 $this->validateCoinTransaction($validator);
    //                 break;
    //             case 'addExp':
    //                 $this->validateExpTransaction($validator);
    //                 break;
    //             case 'getCoinTransactions':
    //             case 'getExpTransactions':
    //                 $this->validateGetTransactions($validator);
    //                 break;
    //         }
    //     });
    // }

    // private function validateCheckin($validator)
    // {
    //     if (!$this->has('user_id')) {
    //         $validator->errors()->add('user_id', 'ID pengguna wajib diisi untuk checkin.');
    //     }
    //     if (!$this->has('place_id')) {
    //         $validator->errors()->add('place_id', 'ID tempat wajib diisi untuk checkin.');
    //     }
    // }

    // private function validateReview($validator)
    // {
    //     if (!$this->has('user_id')) {
    //         $validator->errors()->add('user_id', 'ID pengguna wajib diisi untuk review.');
    //     }
    //     if (!$this->has('place_id')) {
    //         $validator->errors()->add('place_id', 'ID tempat wajib diisi untuk review.');
    //     }
    //     if (!$this->has('content')) {
    //         $validator->errors()->add('content', 'Konten review wajib diisi.');
    //     }
    //     if (!$this->has('rating')) {
    //         $validator->errors()->add('rating', 'Rating wajib diisi untuk review.');
    //     }
    // }

    // private function validateGrantAchievement($validator)
    // {
    //     if (!$this->has('user_id')) {
    //         $validator->errors()->add('user_id', 'ID pengguna wajib diisi untuk memberikan pencapaian.');
    //     }
    //     if (!$this->has('achievement_id')) {
    //         $validator->errors()->add('achievement_id', 'ID pencapaian wajib diisi.');
    //     }
    // }

    // private function validateCompleteChallenge($validator)
    // {
    //     if (!$this->has('user_id')) {
    //         $validator->errors()->add('user_id', 'ID pengguna wajib diisi untuk menyelesaikan tantangan.');
    //     }
    //     if (!$this->has('challenge_id')) {
    //         $validator->errors()->add('challenge_id', 'ID tantangan wajib diisi.');
    //     }
    // }

    // private function validateRedeemReward($validator)
    // {
    //     if (!$this->has('user_id')) {
    //         $validator->errors()->add('user_id', 'ID pengguna wajib diisi untuk menukar hadiah.');
    //     }
    //     if (!$this->has('reward_id')) {
    //         $validator->errors()->add('reward_id', 'ID hadiah wajib diisi.');
    //     }
    // }

    // private function validateCoinTransaction($validator)
    // {
    //     if (!$this->has('user_id')) {
    //         $validator->errors()->add('user_id', 'ID pengguna wajib diisi untuk transaksi koin.');
    //     }
    //     if (!$this->has('amount')) {
    //         $validator->errors()->add('amount', 'Jumlah koin wajib diisi.');
    //     }
    //     if (!$this->has('related_to_id')) {
    //         $validator->errors()->add('related_to_id', 'ID terkait wajib diisi untuk transaksi koin.');
    //     }
    //     if (!$this->has('related_to_type')) {
    //         $validator->errors()->add('related_to_type', 'Tipe terkait wajib diisi untuk transaksi koin.');
    //     }
    // }

    // private function validateExpTransaction($validator)
    // {
    //     if (!$this->has('user_id')) {
    //         $validator->errors()->add('user_id', 'ID pengguna wajib diisi untuk transaksi exp.');
    //     }
    //     if (!$this->has('amount')) {
    //         $validator->errors()->add('amount', 'Jumlah exp wajib diisi.');
    //     }
    //     if (!$this->has('related_to_id')) {
    //         $validator->errors()->add('related_to_id', 'ID terkait wajib diisi untuk transaksi exp.');
    //     }
    //     if (!$this->has('related_to_type')) {
    //         $validator->errors()->add('related_to_type', 'Tipe terkait wajib diisi untuk transaksi exp.');
    //     }
    // }

    // private function validateGetTransactions($validator)
    // {
    //     if (!$this->has('user_id')) {
    //         $validator->errors()->add('user_id', 'ID pengguna wajib diisi untuk melihat transaksi.');
    //     }
    // }
}