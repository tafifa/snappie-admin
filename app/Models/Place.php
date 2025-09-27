<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Clickbar\Magellan\Data\Geometries\Point;

class Place extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'places';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'longitude',
        'latitude',
        'image_urls',
        'coin_reward',
        'exp_reward',
        'min_price',
        'max_price',
        'avg_rating',
        'total_review',
        'total_checkin',
        'status',
        'partnership_status',
        'additional_info'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
        'image_urls' => 'json',
        'coin_reward' => 'integer',
        'exp_reward' => 'integer',
        'min_price' => 'integer',
        'max_price' => 'integer',
        'avg_rating' => 'float',
        'total_review' => 'integer',
        'total_checkin' => 'integer',
        'status' => 'boolean',
        'partnership_status' => 'boolean',
        'additional_info' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Additional Info Key References
     */
    protected $additionalInfoKey = [
        'place_detail', // subkey: shortDescription, address, openingHours, openingDays, contactNumber, website
        'place_value', // subkey: key, value
        'food_type', // subkey: key, value
        'place_attributes' // subkey: menu, facility, parking, capacity, accessibility, payment, service
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

    // --- QUERY SCOPES ---

    /**
     * Scope query untuk hanya menyertakan tempat yang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
