<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reviews';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'place_id',
        'content',
        'rating',
        'image_urls',
        'vote',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'image_urls' => 'array', // For multiple image URLs
        'rating' => 'integer',
        'vote' => 'integer',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
        'rating' => 5,
        'vote' => 0,
    ];

    /**
     * Get the user that owns the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the place that was reviewed.
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Get validation rules for review
     */
    public static function getValidationRules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'place_id' => 'required|exists:places,id',
            'content' => 'nullable|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'image_urls' => 'nullable|array|max:5',
            'image_urls.*' => 'url',
            'status' => 'in:approved,rejected,pending',
        ];
    }

    /**
     * Get star rating display
     */
    public function getStarRatingAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Increment vote count
     */
    public function incrementVote()
    {
        $this->increment('vote');
    }

    /**
     * Decrement vote count
     */
    public function decrementVote()
    {
        $this->decrement('vote');
    }
}
