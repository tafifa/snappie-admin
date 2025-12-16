<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "user_achievements";

    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "user_id",
        "achievement_id",
        "current_progress",
        "target_progress",
        "status",
        "completed_at",
        "period_date",
        "additional_info",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "id" => "integer",
        "user_id" => "integer",
        "achievement_id" => "integer",
        "current_progress" => "integer",
        "target_progress" => "integer",
        "status" => "boolean",
        "completed_at" => "datetime",
        "period_date" => "date",
        "additional_info" => "json",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    /**
     * Get the validation rules for the model.
     *
     * @return array
     */
    public static function rules(): array
    {
        return [
            "user_id" => "required|exists:users,id",
            "achievement_id" => "required|exists:achievements,id",
            "current_progress" => "integer|min:0",
            "target_progress" => "integer|min:1",
            "status" => "boolean",
            "completed_at" => "nullable|date",
            "period_date" => "nullable|date",
            "additional_info" => "nullable|json",
        ];
    }

    /**
     * Get the user that owns the user achievement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the achievement that belongs to the user achievement.
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    /**
     * Check if the achievement is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === true;
    }

    /**
     * Check if the progress has reached the target.
     *
     * @return bool
     */
    public function hasReachedTarget(): bool
    {
        return $this->current_progress >= $this->target_progress;
    }

    /**
     * Get the progress percentage.
     *
     * @return int
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->target_progress <= 0) {
            return 0;
        }

        $percentage = ($this->current_progress / $this->target_progress) * 100;
        return min(100, (int) round($percentage));
    }

    /**
     * Get remaining progress needed.
     *
     * @return int
     */
    public function getRemainingProgressAttribute(): int
    {
        return max(0, $this->target_progress - $this->current_progress);
    }

    /**
     * Increment progress by a given amount.
     *
     * @param int $amount
     * @return bool
     */
    public function incrementProgress(int $amount = 1): bool
    {
        $this->current_progress += $amount;
        return $this->save();
    }

    /**
     * Mark the achievement as completed.
     *
     * @return bool
     */
    public function markAsCompleted(): bool
    {
        $this->status = true;
        $this->completed_at = now();
        return $this->save();
    }

    /**
     * Reset progress (for resettable achievements).
     *
     * @param string|null $newPeriodDate
     * @return bool
     */
    public function resetProgress(?string $newPeriodDate = null): bool
    {
        $this->current_progress = 0;
        $this->status = false;
        $this->completed_at = null;
        if ($newPeriodDate) {
            $this->period_date = $newPeriodDate;
        }
        return $this->save();
    }

    // --- QUERY SCOPES ---

    /**
     * Scope a query to only include completed user achievements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where("status", true);
    }

    /**
     * Scope a query to only include in-progress user achievements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress($query)
    {
        return $query->where("status", false);
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
        return $query->where("user_id", $userId);
    }

    /**
     * Scope a query to filter by achievement.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $achievementId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAchievement($query, int $achievementId)
    {
        return $query->where("achievement_id", $achievementId);
    }

    /**
     * Scope a query to filter by period date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $periodDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForPeriod($query, string $periodDate)
    {
        return $query->where("period_date", $periodDate);
    }

    /**
     * Scope a query to filter records without period date (one-time achievements).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOneTime($query)
    {
        return $query->whereNull("period_date");
    }
}
