<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $method = $this->route()->getActionMethod();
        
        return match ($method) {
            'show', 'getPlaceReviews' => [
                'place_id' => 'required|integer',
            ],
            'index' => [
                'food_type' => 'sometimes|array',
                'place_value' => 'sometimes|array',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'search' => 'sometimes|string|max:255',
                'min_rating' => 'sometimes|numeric|min:0|max:5',
                'min_price' => 'sometimes|integer|min:0',
                'max_price' => 'sometimes|integer|min:0|gte:min_price',
                'latitude' => 'sometimes|numeric|between:-90,90',
                'longitude' => 'sometimes|numeric|between:-180,180',
                'radius' => 'sometimes|numeric|min:0.1|max:50',
                'popular' => 'sometimes|boolean',
                'partner' => 'sometimes|boolean',
                'active_only' => 'sometimes|boolean',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'Jumlah per halaman harus berupa angka.',
            'per_page.min' => 'Jumlah per halaman minimal 1.',
            'per_page.max' => 'Jumlah per halaman maksimal 100.',
            'search.string' => 'Kata kunci pencarian harus berupa teks.',
            'search.max' => 'Kata kunci pencarian maksimal 255 karakter.',
            'min_rating.numeric' => 'Rating minimum harus berupa angka.',
            'min_rating.min' => 'Rating minimum tidak boleh kurang dari 0.',
            'min_rating.max' => 'Rating minimum tidak boleh lebih dari 5.',
            'min_price.integer' => 'Harga minimum harus berupa angka.',
            'min_price.min' => 'Harga minimum tidak boleh kurang dari 0.',
            'max_price.integer' => 'Harga maksimum harus berupa angka.',
            'max_price.min' => 'Harga maksimum tidak boleh kurang dari 0.',
            'max_price.gte' => 'Harga maksimum harus lebih besar atau sama dengan harga minimum.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus antara -90 dan 90.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 dan 180.',
            'radius.numeric' => 'Radius harus berupa angka.',
            'radius.min' => 'Radius minimal 0.1 km.',
            'radius.max' => 'Radius maksimal 50 km.',
            'popular.boolean' => 'Parameter popular harus berupa true/false.',
            'partner.boolean' => 'Parameter partner harus berupa true/false.',
            'active_only.boolean' => 'Parameter active_only harus berupa true/false.',
            'place_id.required' => 'ID tempat wajib diisi.',
            'place_id.integer' => 'ID tempat harus berupa angka.',
            'place_id.exists' => 'Tempat tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'per_page' => 'jumlah per halaman',
            'search' => 'kata kunci pencarian',
            'min_rating' => 'rating minimum',
            'min_price' => 'harga minimum',
            'max_price' => 'harga maksimum',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'radius' => 'radius',
            'popular' => 'populer',
            'partner' => 'partner',
            'active_only' => 'hanya aktif',
            'place_id' => 'ID tempat',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validasi bahwa jika ada latitude, harus ada longitude juga
            if ($this->has('latitude') && !$this->has('longitude')) {
                $validator->errors()->add('longitude', 'Longitude wajib diisi jika latitude diisi.');
            }
            
            if ($this->has('longitude') && !$this->has('latitude')) {
                $validator->errors()->add('latitude', 'Latitude wajib diisi jika longitude diisi.');
            }

            // Validasi bahwa max_price harus ada jika min_price ada
            if ($this->has('min_price') && $this->has('max_price')) {
                if ($this->min_price > $this->max_price) {
                    $validator->errors()->add('max_price', 'Harga maksimum harus lebih besar dari harga minimum.');
                }
            }
        });
    }
}