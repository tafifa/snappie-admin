<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'image_url',
        'total_coin',
        'total_exp',
        'total_following',
        'total_follower',
        'total_checkin',
        'total_post',
        'total_article',
        'total_review',
        'total_achievement',
        'total_challenge',
        'status',
        'last_login_at',
        'additional_info',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'total_coin' => 'integer',
        'total_exp' => 'integer',
        'total_following' => 'integer',
        'total_follower' => 'integer',
        'total_checkin' => 'integer',
        'total_post' => 'integer',
        'total_article' => 'integer',
        'total_review' => 'integer',
        'total_achievement' => 'integer',
        'total_challenge' => 'integer',
        'status' => 'boolean',
        'additional_info' => 'json',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Accessor to guarantee additional_info always contains expected structure.
     */
    public function getAdditionalInfoAttribute($value): array
    {
        $data = $value ?? [];

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($data)) {
            $data = (array) $data;
        }

        $defaults = [
            'user_detail' => [
                'bio' => '',
                'gender' => null,
                'date_of_birth' => '',
                'phone' => '',
            ],
            'user_preferences' => [
                'food_type' => [],
                'place_value' => [],
            ],
            'user_saved' => [
                'saved_places' => [],
                'saved_posts' => [],
                'saved_articles' => [],
            ],
            'user_settings' => [
                'language' => 'id',
                'theme' => 'light',
            ],
            'user_notification' => [
                'push_notification' => true,
            ],
        ];

        return array_replace_recursive($defaults, $data);
    }

    /**
     * AdditionalInfo Key References
     */
    protected $additionalInfoKey = [
        'user_detail', // subkey: firstName, lastName, gender
        'user_preferences', // subkey: foodType, placeValue
        'user_saved', // subkey: savedPlaces, savedPosts, savedArticles
        'user_settings', // subkey: language, theme
        'user_notification', // subkey: pushNotification
    ];

    /**
     * Get the validation rules for the model.
     *
     * @return array
     */
    public static function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'image_url' => 'nullable|url|max:500',
            'total_coin' => 'integer|min:0',
            'total_exp' => 'integer|min:0',
            'total_following' => 'integer|min:0',
            'total_follower' => 'integer|min:0',
            'total_checkin' => 'integer|min:0',
            'total_post' => 'integer|min:0',
            'total_article' => 'integer|min:0',
            'total_review' => 'integer|min:0',
            'total_achievement' => 'integer|min:0',
            'total_challenge' => 'integer|min:0',
            'status' => 'boolean',
            'last_login_at' => 'nullable|date',
            'additional_info' => 'nullable|json',
        ];
    }

    /**
     * Mendapatkan semua check-in yang dilakukan oleh pengguna.
     */
    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Mendapatkan semua reviews yang dilakukan oleh pengguna.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Mendapatkan semua pencapaian yang dimiliki pengguna.
     */
    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Relasi many-to-many ke model Achievement melalui tabel user_achievements.
     */
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->using(UserAchievement::class)
            ->withPivot('status', 'additional_info')
            ->withTimestamps();
    }

    /**
     * Mendapatkan semua tantangan yang diikuti pengguna.
     */
    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class);
    }

    /**
     * Relasi many-to-many ke model Challenge melalui tabel user_challenges.
     */
    public function challenges()
    {
        return $this->belongsToMany(Challenge::class, 'user_challenges')
            ->using(UserChallenge::class)
            ->withPivot('status', 'additional_info')
            ->withTimestamps();
    }

    /**
     * Mendapatkan semua hadiah yang diklaim oleh pengguna.
     */
    public function userRewards()
    {
        return $this->hasMany(UserReward::class);
    }
    
    /**
     * Relasi many-to-many ke model Reward melalui tabel user_rewards.
     */
    public function rewards()
    {
        return $this->belongsToMany(Reward::class, 'user_rewards')
            ->using(UserReward::class)
            ->withPivot('status', 'additional_info')
            ->withTimestamps();
    }

    /**
     * Mendapatkan semua transaksi koin pengguna.
     */
    public function coinTransactions()
    {
        return $this->hasMany(CoinTransaction::class);
    }

    /**
     * Mendapatkan semua transaksi EXP pengguna.
     */
    public function expTransactions()
    {
        return $this->hasMany(ExpTransaction::class);
    }

    /**
     * Mendapatkan semua 'like' yang diberikan oleh pengguna.
     */
    public function likes()
    {
        return $this->hasMany(UserLike::class);
    }

    /**
     * Mendapatkan daftar pengguna yang mengikuti pengguna ini (followers).
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
            ->using(UserFollow::class)
            ->withTimestamps();
    }

    /**
     * Mendapatkan daftar pengguna yang diikuti oleh pengguna ini (following).
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
            ->using(UserFollow::class)
            ->withTimestamps();
    }

    public function tokens()
    {
        return $this->morphMany(PersonalAccessTokens::class, 'tokenable');
    }

    // --- QUERY SCOPES ---

    /**
     * Scope a query to only include users that are active.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}