<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'place_id',
        'rating',
        'content',
        'images',
        'upvotes',
        'date'
    ];

    protected $casts = [
        'images' => 'array',
        'rating' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(UsersApp::class, 'user_id', 'user_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id', 'place_id');
    }
}
