<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinTransaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coin_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'related_to_id',
        'related_to_type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'integer',
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
            'related_to_id' => 'required|integer',
            'related_to_type' => 'required|string|max:50',
            'amount' => 'required|integer',
        ];
    }

    /**
     * Get the user that owns the coin transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan model yang menjadi sumber transaksi ini (polimorfik).
     * Bisa berupa Checkin, Review, Achievement, dan Reward.
     */
    public function relatedTo()
    {
        return $this->morphTo();
    }
}