<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsersApp extends Authenticatable
{
    use Notifiable;

    protected $table = 'users_app';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'profile_picture',
        'date_joined',
        'points',
        'coin'
    ];

    // Relasi 1:1 dengan user_statistics
    public function statistics()
    {
        return $this->hasOne(UserStatistic::class, 'user_id', 'user_id');
    }

    // Relasi 1:M dengan travel_plans
    public function travelPlans()
    {
        return $this->hasMany(TravelPlan::class, 'user_id', 'user_id');
    }

    // Relasi 1:M dengan reviews
    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'user_id');
    }

    // Relasi Many-to-Many dengan missions
    public function missions()
    {
        return $this->belongsToMany(Mission::class, 'user_missions', 'user_id', 'mission_id')
                    ->withPivot('completed_at', 'image_taken');
    }

    // Relasi Many-to-Many dengan challenges
    public function challenges()
    {
        return $this->belongsToMany(Challenge::class, 'user_challenges', 'user_id', 'challenge_id')
                    ->withPivot('completed_at', 'progress');
    }

    // Relasi Many-to-Many dengan achievements
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements', 'user_id', 'achievement_id')
                    ->withPivot('current_level', 'progress', 'completed_at');
    }
}
