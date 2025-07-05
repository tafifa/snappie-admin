<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'checkins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'place_id',
        'time',
        'location',
        'check_in_status',
        'mission_image_url',
        'mission_status',
        'mission_completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'time' => 'datetime',
        'location' => 'array', // For GPS coordinates [latitude, longitude]
        'check_in_status' => 'string',
        'mission_status' => 'string',
        'mission_completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Accessor for latitude from location array
     */
    public function getLatitudeAttribute(): ?float
    {
        if (!$this->location || !is_array($this->location)) {
            return null;
        }
        return $this->location['latitude'] ?? $this->location[0] ?? null;
    }
    
    /**
     * Accessor for longitude from location array
     */
    public function getLongitudeAttribute(): ?float
    {
        if (!$this->location || !is_array($this->location)) {
            return null;
        }
        return $this->location['longitude'] ?? $this->location[1] ?? null;
    }
    
    /**
     * Mutator to set latitude in location array
     */
    public function setLatitudeAttribute($value): void
    {
        $location = $this->location ?? [];
        if ($value !== null) {
            $location['latitude'] = (float) $value;
            $this->attributes['location'] = json_encode($location);
        }
    }
    
    /**
     * Mutator to set longitude in location array
     */
    public function setLongitudeAttribute($value): void
    {
        $location = $this->location ?? [];
        if ($value !== null) {
            $location['longitude'] = (float) $value;
            $this->attributes['location'] = json_encode($location);
        }
    }

    /**
     * Get the user that owns the check-in.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the place that was checked into.
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
