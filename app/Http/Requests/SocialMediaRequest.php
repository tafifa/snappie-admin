<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SocialMediaRequest extends FormRequest
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
            // User validation
            'follow' => [
                'user_id' => 'required|integer|exists:users,id',
                'follower_id' => 'required|integer|exists:users,id',
            ],
            'unfollow' => [
                'user_id' => 'required|integer|exists:users,id',
                'follower_id' => 'required|integer|exists:users,id',
            ],
            'getFollowers' => [
                'user_id' => 'required|integer|exists:users,id',
                'limit' => 'nullable|integer|min:1|max:100',
            ],
            'getFollowing' => [
                'user_id' => 'required|integer|exists:users,id',
                'limit' => 'nullable|integer|min:1|max:100',
            ],
            'isFollowing' => [
                'user_id' => 'required|integer|exists:users,id',
                'target_user_id' => 'required|integer|exists:users,id',
            ],
            'getUserProfile' => [
                'user_id' => 'required|integer|exists:users,id',
            ],
            'getDefaultFeedPosts' => [
                'per_page' => 'nullable|integer|min:1|max:100',
            ],
            'getFeedPosts' => [
                'per_page' => 'nullable|integer|min:1|max:100',
            ],
            'getTrendingPosts' => [
                'per_page' => 'nullable|integer|min:1|max:100',
            ],
            'getPostsByUser' => [
                'user_id' => 'required|integer|exists:users,id',
                'per_page' => 'nullable|integer|min:1|max:100',
            ],
            'getPostsByPlace' => [
                'place_id' => 'required|integer|exists:places,id',
                'per_page' => 'nullable|integer|min:1|max:100',
            ],
            'getPostById' => [
                'post_id' => 'required|integer|exists:posts,id',
            ],
            default => [],
        };
        // return [
        //     // User validation
        //     'user_id' => 'nullable|integer',
        //     'follower_id' => 'nullable|integer',
        //     'target_user_id' => 'nullable|integer',
        //     'current_user_id' => 'nullable|integer',

        //     // Content validation
        //     'place_id' => 'nullable|integer',
        //     'post_id' => 'nullable|integer',
        //     'comment_id' => 'nullable|integer',
        //     'review_id' => 'nullable|integer',
        //     'content' => 'nullable|string|max:1000',
        //     'image_url' => 'nullable|url',
        //     'additional_info' => 'nullable|array',

        //     // Pagination validation
        //     'limit' => 'nullable|integer|min:1|max:100',
        //     'per_page' => 'nullable|integer|min:1|max:100',
        //     'hours' => 'nullable|integer|min:1|max:168',
        // ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // User messages
            'user_id.exists' => 'User dengan ID tersebut tidak ditemukan.',
            'follower_id.exists' => 'Follower dengan ID tersebut tidak ditemukan.',
            'target_user_id.exists' => 'Target user dengan ID tersebut tidak ditemukan.',
            'current_user_id.exists' => 'Current user dengan ID tersebut tidak ditemukan.',

            // Content messages
            'place_id.exists' => 'Tempat dengan ID tersebut tidak ditemukan.',
            'post_id.exists' => 'Post dengan ID tersebut tidak ditemukan.',
            'comment_id.exists' => 'Comment dengan ID tersebut tidak ditemukan.',
            'review_id.exists' => 'Review dengan ID tersebut tidak ditemukan.',
            'content.string' => 'Konten harus berupa teks.',
            'content.max' => 'Konten maksimal :max karakter.',
            'image_url.url' => 'URL gambar tidak valid.',
            'additional_info.array' => 'Informasi tambahan harus berupa array.',

            // Pagination messages
            'limit.integer' => 'Limit harus berupa angka.',
            'limit.min' => 'Limit minimal 1.',
            'limit.max' => 'Limit maksimal 100.',
            'per_page.integer' => 'Per page harus berupa angka.',
            'per_page.min' => 'Per page minimal 1.',
            'per_page.max' => 'Per page maksimal 100.',
            'hours.integer' => 'Hours harus berupa angka.',
            'hours.min' => 'Hours minimal 1.',
            'hours.max' => 'Hours maksimal 168 (1 minggu).',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'ID User',
            'follower_id' => 'ID Follower',
            'target_user_id' => 'ID Target User',
            'current_user_id' => 'ID Current User',
            'place_id' => 'ID Tempat',
            'post_id' => 'ID Post',
            'comment_id' => 'ID Comment',
            'review_id' => 'ID Review',
            'content' => 'Konten',
            'image_url' => 'URL Gambar',
            'additional_info' => 'Informasi Tambahan',
            'limit' => 'Limit',
            'per_page' => 'Per Page',
            'hours' => 'Hours'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $method = $this->route()->getActionMethod();

        // Add required validation based on method
        $validator->after(function ($validator) use ($method) {
            switch ($method) {
                case 'follow':
                case 'unfollow':
                    if (!$this->filled('follower_id')) {
                        $validator->errors()->add('follower_id', 'ID follower harus diisi.');
                    }
                    break;

                case 'isFollowing':
                    if (!$this->filled('user_id')) {
                        $validator->errors()->add('user_id', 'ID user harus diisi.');
                    }
                    if (!$this->filled('target_user_id')) {
                        $validator->errors()->add('target_user_id', 'ID target user harus diisi.');
                    }
                    break;

                case 'getFeedPosts':
                    if (!$this->filled('user_id')) {
                        $validator->errors()->add('user_id', 'ID user harus diisi.');
                    }
                    break;

                case 'createPost':
                    if (!$this->filled('user_id')) {
                        $validator->errors()->add('user_id', 'ID user harus diisi.');
                    }
                    if (!$this->filled('place_id')) {
                        $validator->errors()->add('place_id', 'ID tempat harus diisi.');
                    }
                    if (!$this->filled('content')) {
                        $validator->errors()->add('content', 'Konten harus diisi.');
                    }
                    break;

                case 'likePost':
                case 'unlikePost':
                    if (!$this->filled('user_id')) {
                        $validator->errors()->add('user_id', 'ID user harus diisi.');
                    }
                    $targetCount = collect(['post_id', 'comment_id', 'review_id'])
                        ->filter(fn($field) => $this->filled($field))
                        ->count();
                    if ($targetCount !== 1) {
                        $validator->errors()->add('target', 'Harus memilih tepat satu target (post, comment, atau review).');
                    }
                    break;

                case 'commentOnPost':
                    if (!$this->filled('user_id')) {
                        $validator->errors()->add('user_id', 'ID user harus diisi.');
                    }
                    if (!$this->filled('content')) {
                        $validator->errors()->add('content', 'Konten komentar harus diisi.');
                    }
                    // Limit content for comments to 500 characters
                    if ($this->filled('content') && strlen($this->content) > 500) {
                        $validator->errors()->add('content', 'Konten komentar maksimal 500 karakter.');
                    }
                    $targetCount = collect(['post_id', 'comment_id', 'review_id'])
                        ->filter(fn($field) => $this->filled($field))
                        ->count();
                    if ($targetCount !== 1) {
                        $validator->errors()->add('target', 'Harus memilih tepat satu target (post, comment, atau review).');
                    }
                    break;

                case 'deleteComment':
                    if (!$this->filled('user_id')) {
                        $validator->errors()->add('user_id', 'ID user harus diisi.');
                    }
                    if (!$this->filled('comment_id')) {
                        $validator->errors()->add('comment_id', 'ID comment harus diisi.');
                    }
                    break;
            }
        });
    }
}
