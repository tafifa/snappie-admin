<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'image_url',
        'total_coin',
        'total_exp',
        'status',
        'last_login_at',
        'additional_info',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'total_coin' => 'integer',
        'total_exp' => 'integer',
        'additional_info' => 'array',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get user level based on experience points
     */
    public function getLevelAttribute()
    {
        return intval($this->total_exp / 100) + 1;
    }

    /**
     * Get experience points needed for next level
     */
    public function getExpToNextLevelAttribute()
    {
        $currentLevel = $this->level;
        $expForNextLevel = $currentLevel * 100;
        return $expForNextLevel - $this->total_exp;
    }

    /**
     * Get all check-ins for this user.
     */
    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Get all reviews created by this user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}