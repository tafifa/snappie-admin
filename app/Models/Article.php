<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'articles';

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'content',
        'image_urls',
        'additional_info',
    ];

    protected $casts = [
        'image_urls' => 'json',
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
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'content' => 'required|string',
            'image_urls' => 'nullable|json',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Get the user that owns the article.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likes for the article.
     */
    public function likes()
    {
        return $this->morphMany(UserLike::class, 'related_to');
    }

    /**
     * Get the comments for the article.
     */
    public function comments()
    {
        return $this->morphMany(UserComment::class, 'related_to');
    }
}