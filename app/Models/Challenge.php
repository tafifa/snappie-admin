<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'challenges';

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'exp_reward',
        'started_at',
        'ended_at',
        'challenge_type',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'id' => 'integer',
        'exp_reward' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'challenge_type' => 'string',
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
            'exp_reward' => 'integer|min:0|max:10000',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after:started_at',
            'challenge_type' => 'required|string|max:100',
            'status' => 'boolean',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Get all users who have participated in this challenge.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_challenges')
            ->using(UserChallenge::class)
            ->withPivot('status', 'additional_info')
            ->withTimestamps();
    }

    // --- QUERY SCOPES ---

    /**
     * Scope a query to only include active challenges.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}