<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LeaderboardRequest extends FormRequest
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
        
        return match($method) {
            'getTopUsers' => [
                'limit' => 'sometimes|integer|min:1|max:100',
            ],
            'getTopUserThisWeek' => [
                'limit' => 'sometimes|integer|min:1|max:100',
            ],
            'getTopUsersThisMonth' => [
                'limit' => 'sometimes|integer|min:1|max:100',
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
            'limit.integer' => 'Limit harus berupa angka.',
            'limit.min' => 'Limit minimal 1.',
            'limit.max' => 'Limit maksimal 100.',
            
            'per_page.integer' => 'Per page harus berupa angka.',
            'per_page.min' => 'Per page minimal 1.',
            'per_page.max' => 'Per page maksimal 50.',
            
            'page.integer' => 'Page harus berupa angka.',
            'page.min' => 'Page minimal 1.',
            
            'user_id.required' => 'ID pengguna wajib diisi.',
            'user_id.integer' => 'ID pengguna harus berupa angka.',
            'user_id.exists' => 'Pengguna tidak ditemukan.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'limit' => 'batas data',
            'per_page' => 'data per halaman',
            'page' => 'halaman',
            'user_id' => 'ID pengguna',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $method = $this->route()->getActionMethod();
            
            // Set default values if not provided
            if (in_array($method, ['getTopUsers', 'getTopUserThisWeek', 'getTopUsersThisMonth'])) {
                if (!$this->has('limit')) {
                    $this->merge(['limit' => 10]);
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