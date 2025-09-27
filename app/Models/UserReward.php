<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserReward extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_rewards';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'reward_id',
        'status',
        'additional_info',
    ];

    protected $casts = [
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
            'reward_id' => 'required|exists:rewards,id',
            'status' => 'boolean',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Get the user that owns the user reward.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reward that belongs to the user reward.
     */
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    } 
    
    // --- QUERY SCOPES ---
    /**
     * Scope a query to only include redeemed user rewards.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRedeemed($query)
    {
        return $query->where('status', true);
    }
}