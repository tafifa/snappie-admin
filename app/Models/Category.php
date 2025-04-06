<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name'];

    public function places()
    {
        return $this->belongsToMany(Place::class, 'place_categories', 'category_id', 'place_id');
    }
}
