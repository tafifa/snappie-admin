<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserFollow extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "user_follows";

    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ["follower_id", "following_id"];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    /**
     * Get the validation rules for the model.
     *
     * @return array
     */
    public static function rules()
    {
        return [
            "follower_id" => "required|exists:users,id",
            "following_id" => "required|exists:users,id|different:follower_id",
        ];
    }

    /**
     * Get the user who is following.
     */
    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, "follower_id")->select([
            "id",
            "name",
            "image_url",
        ]);
    }

    /**
     * Get the user being followed.
     */
    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, "following_id")->select([
            "id",
            "name",
            "image_url",
        ]);
    }
}
