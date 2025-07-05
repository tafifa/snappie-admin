<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'places';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'address',
        'latitude',
        'longitude',
        'image_urls',
        'status',
        'partnership_status',
        'clue_mission',
        'exp_reward',
        'coin_reward',
        'additional_info'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'image_urls' => 'array',
        'status' => 'boolean',
        'partnership_status' => 'boolean',
        'exp_reward' => 'integer',
        'coin_reward' => 'integer',
        'additional_info' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all check-ins for this place.
     */
    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Get all reviews for this place.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get reward information for missions
     */
    public function getRewardInfoAttribute()
    {
        return [
            'clue_mission' => $this->clue_mission,
            'exp_reward' => $this->exp_reward,
            'coin_reward' => $this->coin_reward,
        ];
    }
}
