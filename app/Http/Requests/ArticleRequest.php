<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Rules dasar yang bisa digunakan untuk semua method
        return [
            'per_page' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'author_id' => 'sometimes|integer',
            'article_id' => 'sometimes|integer',
            'user_id' => 'sometimes|integer',
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'image_urls' => 'sometimes|array',
            'image_urls.hero' => 'sometimes|url|max:500',
            'image_urls.gallery' => 'sometimes|array',
            'image_urls.gallery.*' => 'url|max:500',
            'additional_info' => 'sometimes|array',
            'status' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'Jumlah per halaman harus berupa angka.',
            'per_page.min' => 'Jumlah per halaman minimal 1.',
            'per_page.max' => 'Jumlah per halaman maksimal 100.',
            'search.string' => 'Kata kunci pencarian harus berupa teks.',
            'search.max' => 'Kata kunci pencarian maksimal 255 karakter.',
            'category.string' => 'Kategori harus berupa teks.',
            'category.max' => 'Kategori maksimal 100 karakter.',
            'author_id.integer' => 'ID penulis harus berupa angka.',
            'author_id.exists' => 'Penulis tidak ditemukan.',
            'article_id.required' => 'ID artikel wajib diisi.',
            'article_id.integer' => 'ID artikel harus berupa angka.',
            'article_id.exists' => 'Artikel tidak ditemukan.',
            'user_id.required' => 'ID pengguna wajib diisi.',
            'user_id.integer' => 'ID pengguna harus berupa angka.',
            'user_id.exists' => 'Pengguna tidak ditemukan.',
            'title.required' => 'Judul artikel wajib diisi.',
            'title.string' => 'Judul artikel harus berupa teks.',
            'title.max' => 'Judul artikel maksimal 255 karakter.',
            'content.required' => 'Konten artikel wajib diisi.',
            'content.string' => 'Konten artikel harus berupa teks.',
            'image_urls.array' => 'URL gambar harus berupa array.',
            'image_urls.hero.url' => 'URL gambar hero tidak valid.',
            'image_urls.hero.max' => 'URL gambar hero maksimal 500 karakter.',
            'image_urls.gallery.array' => 'Galeri gambar harus berupa array.',
            'image_urls.gallery.*.url' => 'Setiap URL gambar galeri harus valid.',
            'image_urls.gallery.*.max' => 'Setiap URL gambar galeri maksimal 500 karakter.',
            'additional_info.array' => 'Informasi tambahan harus berupa array.',
            'status.boolean' => 'Status harus berupa true/false.',
        ];
    }

    public function attributes(): array
    {
        return [
            'per_page' => 'jumlah per halaman',
            'search' => 'kata kunci pencarian',
            'category' => 'kategori',
            'author_id' => 'ID penulis',
            'article_id' => 'ID artikel',
            'user_id' => 'ID pengguna',
            'title' => 'judul artikel',
            'content' => 'konten artikel',
            'image_urls' => 'URL gambar',
            'additional_info' => 'informasi tambahan',
            'status' => 'status',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $method = $this->route()->getActionMethod();

            // Validasi berdasarkan method
            switch ($method) {
                case 'show':
                    $this->validateShow($validator);
                    break;
            }
        });
    }

    private function validateShow($validator)
    {
        if (!$this->has('article_id') && !$this->route('id')) {
            $validator->errors()->add('article_id', 'ID artikel wajib diisi untuk melihat detail artikel.');
        }
    }
}
