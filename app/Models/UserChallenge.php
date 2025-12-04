<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserChallenge extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_challenges';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'challenge_id' => 'integer',
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
            'challenge_id' => 'required|exists:challenges,id',
            'status' => 'boolean',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Get the user that owns the user challenge.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the challenge that belongs to the user challenge.
     */
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    // --- QUERY SCOPES ---
    /**
     * Scope a query to only include completed user challenges.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', true);
    }
}