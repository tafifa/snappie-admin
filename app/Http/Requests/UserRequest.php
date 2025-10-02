<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $method = $this->route()->getActionMethod();

        return match ($method) {
            'show' => [
                'user_id' => 'required|integer',
            ],
            'update' => [
                'name' => 'sometimes|string|max:255',
                'gender' => 'sometimes|in:male,female,other',
                'image_url' => 'sometimes|url|max:500',
                'food_type' => 'sometimes|array',
                'place_value' => 'sometimes|array',
                'phone' => 'sometimes|nullable|string|max:20|regex:/^\+?[0-9]{7,15}$/', // Menambahkan regex untuk nomor telepon
                'date_of_birth' => 'sometimes|nullable|date|before:today',
                'bio' => 'sometimes|nullable|string|max:500',
                'privacy_settings' => 'sometimes|nullable|array',
                'notification_preferences' => 'sometimes|nullable|array',
            ],
            default => []
        };
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 20 karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, dan underscore.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'image_url.required' => 'URL gambar wajib diisi.',
            'image_url.url' => 'Format URL gambar tidak valid.',
            'image_url.max' => 'URL gambar maksimal 500 karakter.',
            'gender.in' => 'Jenis kelamin harus male, female, atau other.',
            'food_type.array' => 'Tipe makanan harus berupa array.',
            'place_value.array' => 'Nilai tempat harus berupa array.',
            'bio.max' => 'Bio maksimal 500 karakter', // Memperbaiki typo 'Bop' menjadi 'Bio'
            'date_of_birth.date' => 'Format tanggal lahir tidak valid.',
            'date_of_birth.before' => 'Tanggal lahir harus sebelum hari ini.',
            'phone.regex' => 'Format nomor telepon tidak valid.',

        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'ID pengguna',
            'name' => 'nama',
            'username' => 'username',
            'email' => 'email',
            'image_url' => 'URL gambar',
            'gender' => 'jenis kelamin',
            'phone' => 'nomor telepon',
            'date_of_birth' => 'tanggal lahir',
            'food_type' => 'tipe makanan',
            'place_value' => 'nilai tempat',
            'bio' => 'bio',
            'privacy_settings' => 'setelan privasi',
            'notification_preferences' => 'preferensi notifikasi',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $method = $this->route()->getActionMethod();

            // Validasi khusus untuk addToSaved dan removeFromSaved
        });
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
}
