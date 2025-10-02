<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        return match ($action) {
            'register' => [
                'email' => 'required|string|email|max:255|unique:users,email',
                'name' => 'required|string|min:2|max:255',
                'gender' => 'required|in:male,female,other',
                'image_url' => 'required|url|max:500',
                'username' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
                'food_type' => 'required|array',
                'place_value' => 'required|array',
            ],
            'login' => [
                'email' => 'required|string',
                'remember' => 'sometimes|boolean',
            ],
            default => [],
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
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'username' => 'username',
            'email' => 'email',
            'image_url' => 'URL gambar',
            'bio' => 'deskripsi user',
            'gender' => 'jenis kelamin',
            'date_of_birth' => 'tanggal lahir',
            'phone' => 'nomor telepon',
            'food_type' => 'tipe makanan',
            'place_value' => 'nilai tempat',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $method = $this->route()->getActionMethod();
            
            // Validasi khusus untuk login
            if ($method === 'login') {
                $email = $this->input('email');
                
                // Validasi format email atau username
                if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Jika bukan email, anggap sebagai username
                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $email)) {
                        $validator->errors()->add('email', 'Format email atau username tidak valid.');
                    }
                }
            }
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
