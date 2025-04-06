<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStatistic extends Model
{
    protected $table = 'user_statistics';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'total_points',
        'total_coins',
        'total_challenges',
        'total_achievements',
        'total_missions',
        'total_reviews',
        'total_upvotes'
    ];

    public function user()
    {
        return $this->belongsTo(UsersApp::class, 'user_id', 'user_id');
    }
}
