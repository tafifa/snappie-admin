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
        'total_like',
        'status',
        'additional_info',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'image_urls' => 'json',
        'total_like' => 'integer',
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
            'total_like' => 'integer|min:0',
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

    /**
     * Get the likes for the review.
     */
    public function likes()
    {
        return $this->morphMany(UserLike::class, 'related_to');
    }

    /**
     * Mendapatkan model yang menjadi sumber transaksi ini (polimorfik).
     * Bisa berupa CoinTransaction dan ExpTransaction.
     */
    public function coinTransactions()
    {
        return $this->morphMany(CoinTransaction::class, 'related_to');
    }

    /**
     * Mendapatkan model yang menjadi sumber transaksi ini (polimorfik).
     * Bisa berupa ExpTransaction.
     */
    public function expTransactions()
    {
        return $this->morphMany(ExpTransaction::class, 'related_to');
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
