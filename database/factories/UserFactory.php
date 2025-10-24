<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $foodTypeOptions = ['Nusantara', 'Internasional', 'Seafood', 'Kafein', 'Non-Kafein', 'Vegetarian', 'Dessert', 'Makanan Ringan', 'Pastry'];
        $placeValueOptions = ['Harga Terjangkau', 'Rasa Autentik', 'Menu Bervariasi', 'Buka 24 Jam', 'Jaringan Lancar', 'Estetika', 'Suasana Tenang', 'Suasana Tradisional', 'Suasana Homey', 'Pet Friendly', 'Ramah Keluarga', 'Pelayanan Ramah', 'Cocok untuk Nongkrong', 'Cocok untuk Work From Cafe', 'Tempat Bersejarah'];
        $userAvatarOptions = ['avatar_m1_hdpi.png', 'avatar_m2_hdpi.png', 'avatar_m3_hdpi.png', 'avatar_m4_hdpi.png', 'avatar_f1_hdpi.png', 'avatar_f2_hdpi.png', 'avatar_f3_hdpi.png', 'avatar_f4_hdpi.png'];

        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'total_exp' => fake()->numberBetween(0, 10000),
            'image_url' => 'https://res.cloudinary.com/' . env('CLOUDINARY_CLOUD_NAME') . '/image/upload/' . env('CLOUDINARY_VERSION') . '/snappie/assets/avatar/' . fake()->randomElement($userAvatarOptions),
            'additional_info' => [
                'user_detail' => [
                    'gender' => fake()->randomElement(['male', 'female', 'other']),
                ],
                'user_preferences' => [
                    'food_type' => fake()->randomElement($foodTypeOptions),
                    'place_value' => fake()->randomElement($placeValueOptions),
                ],
            ],
            'created_at' => fake()->dateTimeThisYear(),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}