<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActionLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_action_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action_type',
        'action_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'action_data' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Action type constants (Simplified - Core Actions Only)
     */
    const ACTION_CHECKIN = 'checkin';          // Check-in at place
    const ACTION_REVIEW = 'review';            // Write review
    const ACTION_POST = 'post';                // Create post
    const ACTION_LIKE = 'like';                // Like content
    const ACTION_COMMENT = 'comment';          // Comment on content
    const ACTION_FOLLOW = 'follow';            // Follow user
    const ACTION_COIN_EARNED = 'coin_earned';  // Earn coins
    const ACTION_XP_EARNED = 'xp_earned';      // Earn XP
    const ACTION_TOP_RANK = 'top_rank';        // Reach top rank

    /**
     * Get all valid action types
     *
     * @return array
     */
    public static function getActionTypes(): array
    {
        return [
            self::ACTION_CHECKIN,
            self::ACTION_REVIEW,
            self::ACTION_POST,
            self::ACTION_LIKE,
            self::ACTION_COMMENT,
            self::ACTION_FOLLOW,
            self::ACTION_COIN_EARNED,
            self::ACTION_XP_EARNED,
            self::ACTION_TOP_RANK,
        ];
    }

    /**
     * Get the validation rules for the model.
     *
     * @return array
     */
    public static function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'action_type' => 'required|string|max:50',
            'action_data' => 'nullable|json',
        ];
    }

    /**
     * Get the user that performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- QUERY SCOPES ---

    /**
     * Scope a query to filter by action type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $actionType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope a query to filter by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter actions within a date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter actions from today.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    /**
     * Scope a query to filter actions from this week.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope a query to filter actions from this month.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }
}
