<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'achievements';

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'coin_reward',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'additional_info' => 'json',
        'coin_reward' => 'integer',
        'status' => 'boolean',
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
            'exp_reward' => 'integer|min:0|max:10000',
            'coin_reward' => 'integer|min:0|max:10000',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Get all users who have earned this achievement.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->using(UserAchievement::class)
            ->withPivot('status', 'additional_info')
            ->withTimestamps();
    }

    // --- QUERY SCOPES ---

    /**
     * Scope a query to only include active achievements.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}