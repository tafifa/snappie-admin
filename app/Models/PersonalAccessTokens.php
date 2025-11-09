<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\User;

class PersonalAccessTokens extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personal_access_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the owning tokenable model.
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Convenience relationship when tokens are only for users.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tokenable_id');
    }

    /**
     * Scope to only include tokens that belong to users.
     */
    public function scopeForUsers($query)
    {
        return $query->where('tokenable_type', User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            // Default tokenable_type to User when not explicitly provided.
            $model->tokenable_type = $model->tokenable_type ?? User::class;
        });
    }
}
