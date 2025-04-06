<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $table = 'missions';
    protected $primaryKey = 'mission_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'place_id',
        'name',
        'description',
        'points_reward',
        'coin_reward'
    ];

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id', 'place_id');
    }

    public function users()
    {
        return $this->belongsToMany(UsersApp::class, 'user_missions', 'mission_id', 'user_id')
                    ->withPivot('completed_at', 'image_taken');
    }
}
