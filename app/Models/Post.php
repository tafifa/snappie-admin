<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';

    protected $fillable = [
        'user_id',
        'place_id',
        'content',
        'image_urls',
        'total_like',
        'total_comment',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'image_urls' => 'json',
        'total_like' => 'integer',
        'total_comment' => 'integer',
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
            'content' => 'required|string|max:2000',
            'image_urls' => 'nullable|json',
            'total_like' => 'integer',
            'total_comment' => 'integer',
            'status' => 'boolean',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the place that the post belongs to.
     */
    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments()
    {
        return $this->hasMany(UserComment::class, 'post_id');
    }

    /**
     * Get the likes for the post.
     */
    public function likes()
    {
        return $this->morphMany(UserLike::class, 'related_to');
    }

    // --- QUERY SCOPES ---

    /**
     * Scope a query to only include active posts.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}