<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "achievements";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "code",
        "name",
        "type",
        "description",
        "criteria_action",
        "criteria_target",
        "image_url",
        "coin_reward",
        "reward_xp",
        "status",
        "reset_schedule",
        "display_order",
        "additional_info",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "id" => "integer",
        "criteria_target" => "integer",
        "coin_reward" => "integer",
        "reward_xp" => "integer",
        "status" => "boolean",
        "display_order" => "integer",
        "additional_info" => "json",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    /**
     * Type constants
     */
    const TYPE_ACHIEVEMENT = "achievement";
    const TYPE_CHALLENGE = "challenge";

    /**
     * Reset schedule constants
     */
    const RESET_NONE = "none";
    const RESET_DAILY = "daily";
    const RESET_WEEKLY = "weekly";

    /**
     * Get the validation rules for the model.
     *
     * @return array
     */
    public static function rules()
    {
        return [
            "code" => "required|string|max:50|unique:achievements,code",
            "name" => "required|string|max:255",
            "type" => "required|in:achievement,challenge",
            "description" => "nullable|string|max:1000",
            "criteria_action" => "required|string|max:50",
            "criteria_target" => "required|integer|min:1",
            "image_url" => "nullable|url|max:500",
            "coin_reward" => "integer|min:0|max:10000",
            "reward_xp" => "integer|min:0|max:10000",
            "status" => "boolean",
            "reset_schedule" => "required|in:none,daily,weekly",
            "display_order" => "integer|min:0",
            "additional_info" => "nullable|json",
        ];
    }

    /**
     * Get all users who have earned this achievement.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, "user_achievements")
            ->using(UserAchievement::class)
            ->withPivot(
                "status",
                "current_progress",
                "target_progress",
                "completed_at",
                "period_date",
                "additional_info",
            )
            ->withTimestamps();
    }

    /**
     * Get all user achievement progress records for this achievement.
     */
    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Get the target value.
     *
     * @return int
     */
    public function getTargetAttribute(): int
    {
        return $this->criteria_target ?? 1;
    }

    /**
     * Get the action type.
     *
     * @return string|null
     */
    public function getActionAttribute(): ?string
    {
        return $this->criteria_action;
    }

    /**
     * Check if this achievement is resettable.
     *
     * @return bool
     */
    public function isResettable(): bool
    {
        return $this->reset_schedule !== self::RESET_NONE;
    }

    /**
     * Check if this achievement is a one-time achievement.
     *
     * @return bool
     */
    public function isOneTime(): bool
    {
        return $this->reset_schedule === self::RESET_NONE;
    }

    // --- QUERY SCOPES ---

    /**
     * Scope a query to only include active achievements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where("status", true);
    }

    /**
     * Scope a query to only include achievements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAchievements($query)
    {
        return $query->where("type", self::TYPE_ACHIEVEMENT);
    }

    /**
     * Scope a query to only include challenges.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeChallenges($query)
    {
        return $query->where("type", self::TYPE_CHALLENGE);
    }

    /**
     * Scope a query to filter by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where("type", $type);
    }

    /**
     * Scope a query to filter by criteria action.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where("criteria_action", $action);
    }

    /**
     * Scope a query to only include one-time achievements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOneTime($query)
    {
        return $query->where("reset_schedule", self::RESET_NONE);
    }

    /**
     * Scope a query to only include resettable achievements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResettable($query)
    {
        return $query->where("reset_schedule", "!=", self::RESET_NONE);
    }

    /**
     * Scope a query to order by display order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy("display_order", "asc");
    }

    /**
     * Scope a query to filter daily challenges.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDaily($query)
    {
        return $query->where("reset_schedule", self::RESET_DAILY);
    }

    /**
     * Scope a query to filter weekly challenges.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWeekly($query)
    {
        return $query->where("reset_schedule", self::RESET_WEEKLY);
    }
}
