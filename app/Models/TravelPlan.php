<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelPlan extends Model
{
    protected $table = 'travel_plans';
    protected $primaryKey = 'plan_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['user_id', 'name', 'is_done'];

    protected $casts = [
        'is_done' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(UsersApp::class, 'user_id', 'user_id');
    }

    public function places()
    {
        return $this->belongsToMany(Place::class, 'travel_plan_places', 'travel_plan_id', 'place_id')
                    ->withPivot('sequence');
    }
}
