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
     * Action type constants
     */
    const ACTION_CHECKIN = 'checkin';
    const ACTION_REVIEW = 'review';
    const ACTION_RATING_5_STAR = 'rating_5_star';
    const ACTION_UPLOAD_PHOTO = 'upload_photo';
    const ACTION_SHARE_EXPERIENCE = 'share_experience';
    const ACTION_POST = 'post';
    const ACTION_POST_LIKE_RECEIVED = 'post_like_received';
    const ACTION_RANK_FIRST = 'rank_first';
    const ACTION_COIN_EARNED = 'coin_earned';
    const ACTION_EXP_EARNED = 'exp_earned';
    const ACTION_CHALLENGE_COMPLETED = 'challenge_completed';
    const ACTION_BREAKFAST_CHECKIN = 'breakfast_checkin';
    const ACTION_CAFE_CHECKIN = 'cafe_checkin';
    const ACTION_DESSERT_CHECKIN = 'dessert_checkin';
    const ACTION_COFFEE_CHECKIN = 'coffee_checkin';
    const ACTION_LOCAL_FOOD_CHECKIN = 'local_food_checkin';
    const ACTION_HIDDEN_GEM_VISIT = 'hidden_gem_visit';
    const ACTION_MONTHLY_BEST = 'monthly_best';
    const ACTION_MONTHLY_RANK_FIRST = 'monthly_rank_first';
    const ACTION_COMMENT = 'comment';
    const ACTION_LIKE = 'like';
    const ACTION_FOLLOW = 'follow';
    const ACTION_UNIQUE_PLACE_VISIT = 'unique_place_visit';

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
            self::ACTION_RATING_5_STAR,
            self::ACTION_UPLOAD_PHOTO,
            self::ACTION_SHARE_EXPERIENCE,
            self::ACTION_POST,
            self::ACTION_POST_LIKE_RECEIVED,
            self::ACTION_RANK_FIRST,
            self::ACTION_COIN_EARNED,
            self::ACTION_EXP_EARNED,
            self::ACTION_CHALLENGE_COMPLETED,
            self::ACTION_BREAKFAST_CHECKIN,
            self::ACTION_CAFE_CHECKIN,
            self::ACTION_DESSERT_CHECKIN,
            self::ACTION_COFFEE_CHECKIN,
            self::ACTION_LOCAL_FOOD_CHECKIN,
            self::ACTION_HIDDEN_GEM_VISIT,
            self::ACTION_MONTHLY_BEST,
            self::ACTION_MONTHLY_RANK_FIRST,
            self::ACTION_COMMENT,
            self::ACTION_LIKE,
            self::ACTION_FOLLOW,
            self::ACTION_UNIQUE_PLACE_VISIT,
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
