<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $table = 'challenges';
    protected $primaryKey = 'challenge_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'points_reward',
        'coin_reward'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function users()
    {
        return $this->belongsToMany(UsersApp::class, 'user_challenges', 'challenge_id', 'user_id')
                    ->withPivot('completed_at', 'progress');
    }
}
