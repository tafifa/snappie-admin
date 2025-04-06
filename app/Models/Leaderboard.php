<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    protected $table = 'leaderboards';
    protected $primaryKey = 'leaderboard_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'start_date',
        'end_date',
        'last_updated'
    ];
}
