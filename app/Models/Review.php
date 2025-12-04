<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

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
        'status',
        'additional_info',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'place_id' => 'integer',
        'rating' => 'integer',
        'image_urls' => 'json',
        'status' => 'boolean',
        'additional_info' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the validation rules for the model.
     *
     * @return array
     */
    public static function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'place_id' => 'required|exists:places,id',
            'content' => 'nullable|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'image_urls' => 'nullable|json',
            'status' => 'boolean',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Additional Info Key References
     */
    protected $additionalInfoKey = [
        'review_detail'
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
    
    // --- QUERY SCOPES ---

    /**
     * Scope untuk hanya mengambil ulasan yang sudah disetujui (moderasi).
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
