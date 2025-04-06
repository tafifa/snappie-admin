<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $table = 'achievements';
    protected $primaryKey = 'achievement_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'level'
    ];

    public function users()
    {
        return $this->belongsToMany(UsersApp::class, 'user_achievements', 'achievement_id', 'user_id')
                    ->withPivot('current_level', 'progress', 'completed_at');
    }
}
