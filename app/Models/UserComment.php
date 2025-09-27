<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserComment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_comments';

    protected $fillable = [
        'user_id',
        'related_to_id',
        'related_to_type',
        'comment',
        'total_like',
        'total_comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_like' => 'integer',
        'total_comment' => 'integer',
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
            'related_to_type' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
            'total_like' => 'integer',
            'total_comment' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedTo()
    {
        return $this->morphTo();
    }

    public function likes()
    {
        return $this->morphMany(UserLike::class, 'related_to');
    }
}