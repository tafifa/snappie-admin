<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $table = 'places';
    protected $primaryKey = 'place_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'location',
        'rating',
        'description',
        'images',
        'tags',
        'is_available'
    ];

    protected $casts = [
        'images'       => 'array',
        'tags'         => 'array',
        'is_available' => 'boolean',
        // Penanganan untuk tipe point bisa disesuaikan jika diperlukan
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'place_categories', 'place_id', 'category_id');
    }

    public function travelPlans()
    {
        return $this->belongsToMany(TravelPlan::class, 'travel_plan_places', 'place_id', 'travel_plan_id')
                    ->withPivot('sequence');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'place_id', 'place_id');
    }

    public function missions()
    {
        return $this->hasMany(Mission::class, 'place_id', 'place_id');
    }
}
