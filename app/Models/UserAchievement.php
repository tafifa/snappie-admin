<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserAchievement extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_achievements';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'achievement_id',
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
            'achievement_id' => 'required|exists:achievements,id',
            'status' => 'boolean',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Get the user that owns the user achievement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the achievement that belongs to the user achievement.
     */
    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }

    // --- QUERY SCOPES ---
    /**
     * Scope a query to only include completed user achievements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', true);
    }
}