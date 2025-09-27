<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rewards';

    /**
     * Reward Key References
     */
    protected $fillable = [
        'name',
        'description',
        'image_url',
        'coin_requirement',
        'stock',
        'started_at',
        'ended_at',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'coin_requirement' => 'integer',
        'stock' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'coin_requirement' => 'required|integer|min:1|max:100000',
            'stock' => 'required|integer|min:0',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after:started_at',
            'status' => 'boolean',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Get all users who have claimed this reward.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_rewards')
            ->using(UserReward::class)
            ->withPivot('status', 'additional_info')
            ->withTimestamps();
    }

    /**
     * Mendapatkan model yang menjadi sumber transaksi ini (polimorfik).
     * Bisa berupa CoinTransaction dan ExpTransaction.
     */
    public function coinTransactions()
    {
        return $this->morphMany(CoinTransaction::class, 'related_to');
    }

    // --- QUERY SCOPES ---

    /**
     * Scope a query to only include active rewards.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
