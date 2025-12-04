<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'checkins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'place_id',
        'latitude',
        'longitude',
        'image_url',
        'status',
        'additional_info',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'place_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'status' => 'boolean',
        'additional_info' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the validation rules for the model.
     *
     * @return array
     */
    public static function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'place_id' => 'required|exists:places,id',
            'latitude' => 'required|float',
            'longitude' => 'required|float',
            'image_url' => 'nullable|json',
            'status' => 'boolean',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Additional Info Key References
     */
    protected $additionalInfoKey = [
        'checkin_detail'
    ];


    /**
     * Mendapatkan pengguna (user) yang melakukan check-in ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan tempat (place) di mana check-in ini dilakukan.
     */
    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}