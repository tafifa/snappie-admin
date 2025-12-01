<?php

namespace App\Services;

use App\Models\User;

class UsersService
{
    public function getById(int $userId): ?User
    {
        return User::find($userId);
    }

    public function getProfileSummary(int $userId): ?array
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'image_url' => $user->image_url,
                'total_coin' => $user->total_coin,
                'total_exp' => $user->total_exp,
                'total_following' => $user->total_following,
                'total_follower' => $user->total_follower,
                'total_checkin' => $user->total_checkin,
                'total_post' => $user->total_post,
                'total_article' => $user->total_article,
                'total_review' => $user->total_review,
                'total_achievement' => $user->total_achievement,
                'total_challenge' => $user->total_challenge,
                'status' => $user->status,
                'last_login_at' => optional($user->last_login_at)->toIso8601String(),
                'additional_info' => $user->additional_info,
                'created_at' => optional($user->created_at)->toIso8601String(),
                'updated_at' => optional($user->updated_at)->toIso8601String(),
            ],
            'stats' => [
                'activity' => [
                    'total_checkins' => (int) $user->total_checkin,
                    'total_reviews' => (int) $user->total_review,
                    'total_posts' => (int) $user->total_post,
                    'total_articles' => (int) $user->total_article,
                ],
                'social' => [
                    'total_followers' => (int) $user->total_follower,
                    'total_following' => (int) $user->total_following,
                ],
                'gamification' => [
                    'total_coins' => (int) $user->total_coin,
                    'total_exp' => (int) $user->total_exp,
                    'total_achievements' => (int) $user->total_achievement,
                    'total_challenges' => (int) $user->total_challenge,
                ],
                'additional information' => [
                    'join_date' => optional($user->created_at)->toDateString(),
                    'last_login' => optional($user->last_login_at)->format('Y-m-d H:i:s'),
                    'days_active' => (float) (optional($user->created_at)->diffInDays($user->last_login_at) ?? 0),
                ],
            ],
            'preferences' => [
                'food_type' => $user->additional_info['user_preferences']['food_type'] ?? [],
                'place_value' => $user->additional_info['user_preferences']['place_value'] ?? [],
            ],
            'settings' => [
                'language' => $user->additional_info['user_settings']['language'] ?? 'id',
                'theme' => $user->additional_info['user_settings']['theme'] ?? 'light',
            ],
        ];
    }

    public function updateProfile(int $userId, array $payload): ?User
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        $add = is_array($user->additional_info) ? $user->additional_info : (array) $user->additional_info;
        $add['user_detail']['gender'] = $payload['gender'] ?? ($add['user_detail']['gender'] ?? null);
        $add['user_detail']['phone'] = $payload['phone'] ?? ($add['user_detail']['phone'] ?? '');
        $add['user_detail']['date_of_birth'] = $payload['date_of_birth'] ?? ($add['user_detail']['date_of_birth'] ?? '');
        $add['user_detail']['bio'] = $payload['bio'] ?? ($add['user_detail']['bio'] ?? '');

        if (isset($payload['privacy_settings'])) {
            $add['privacy_settings'] = [
                'profile_visibility' => $payload['privacy_settings']['profile_visibility'] ?? null,
                'location_sharing' => $payload['privacy_settings']['location_sharing'] ?? null,
            ];
        }

        if (isset($payload['notification_preferences'])) {
            $add['notification_preferences'] = [
                'email_notifications' => $payload['notification_preferences']['email_notifications'] ?? null,
                'push_notifications' => $payload['notification_preferences']['push_notifications'] ?? null,
            ];
        }

        if (isset($payload['name'])) {
            $user->name = $payload['name'];
        }
        if (isset($payload['email'])) {
            $user->email = $payload['email'];
        }
        if (isset($payload['username'])) {
            $user->username = $payload['username'];
        }
        if (isset($payload['image_url'])) {
            $user->image_url = $payload['image_url'];
        }

        $user->additional_info = $add;
        $user->save();

        return $user;
    }

    public function getActivities(int $userId): array
    {
        $reviews = \App\Models\Review::where('user_id', $userId)->orderBy('created_at', 'desc')->limit(10)->get();
        $posts = \App\Models\Post::where('user_id', $userId)->orderBy('created_at', 'desc')->limit(10)->get();
        $userAchievements = \App\Models\UserAchievement::where('user_id', $userId)->orderBy('created_at', 'desc')->limit(10)->get();
        $userChallenges = \App\Models\UserChallenge::where('user_id', $userId)->orderBy('created_at', 'desc')->limit(10)->get();

        return [
            'reviews' => $reviews->toArray(),
            'posts' => $posts->toArray(),
            'user_achievements' => $userAchievements->toArray(),
            'user_challenges' => $userChallenges->toArray(),
        ];
    }

    public function getStats(int $userId): ?array
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }
        return [
            'activity' => [
                'total_checkins' => (int) $user->total_checkin,
                'total_reviews' => (int) $user->total_review,
                'total_posts' => (int) $user->total_post,
                'total_articles' => (int) $user->total_article,
            ],
            'social' => [
                'total_followers' => (int) $user->total_follower,
                'total_following' => (int) $user->total_following,
            ],
            'gamification' => [
                'total_coins' => (int) $user->total_coin,
                'total_exp' => (int) $user->total_exp,
                'total_achievements' => (int) $user->total_achievement,
                'total_challenges' => (int) $user->total_challenge,
            ],
        ];
    }

    public function list(array $filters = [], int $perPage = 10, ?int $page = null): array
    {
        $query = User::query();

        if (array_key_exists('status', $filters)) {
            $val = $filters['status'];
            $bool = is_bool($val) ? $val : in_array(strtolower((string) $val), ['1', 'true', 'yes'], true);
            $query->where('status', $bool);
        }

        $search = isset($filters['search']) ? trim((string) $filters['search']) : null;
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $from = $filters['created_from'] ?? null;
        $to = $filters['created_to'] ?? null;
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        } elseif ($from) {
            $query->where('created_at', '>=', $from);
        } elseif ($to) {
            $query->where('created_at', '<=', $to);
        }

        if (array_key_exists('has_image', $filters)) {
            $val = $filters['has_image'];
            $bool = is_bool($val) ? $val : in_array(strtolower((string) $val), ['1', 'true', 'yes'], true);
            if ($bool) {
                $query->whereNotNull('image_url')->where('image_url', '!=', '');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('image_url')->orWhere('image_url', '=', '');
                });
            }
        }

        $sort = $filters['sort_by'] ?? 'recent';
        if ($sort === 'followers') {
            $query->orderBy('total_follower', 'desc')->orderBy('created_at', 'desc');
        } elseif ($sort === 'posts') {
            $query->orderBy('total_post', 'desc')->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $page ? $query->paginate($perPage, ['*'], 'page', (int) $page) : $query->paginate($perPage);
        return [
            'items' => $users->items(),
            'total' => (int) $users->total(),
            'current_page' => (int) $users->currentPage(),
            'per_page' => (int) $users->perPage(),
            'last_page' => (int) $users->lastPage(),
        ];
    }

    public function updateSaved(int $userId, array $payload): ?User
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        $add = $user->additional_info ?? [];
        
        // Initialize user_saved if not exists
        if (!isset($add['user_saved'])) {
            $add['user_saved'] = [
                'saved_places' => [],
                'saved_posts' => [],
            ];
        }

        // Store saved_places directly as array
        if (isset($payload['saved_places'])) {
            $add['user_saved']['saved_places'] = array_values(array_unique($payload['saved_places']));
        }

        // Store saved_posts directly as array
        if (isset($payload['saved_posts'])) {
            $add['user_saved']['saved_posts'] = array_values(array_unique($payload['saved_posts']));
        }

        $user->additional_info = $add;
        $user->save();

        return $user;
    }

    public function getSaved(int $userId): ?array
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        $add = $user->additional_info ?? [];
        return $add['user_saved'] ?? [
            'saved_places' => [],
            'saved_posts' => [],
        ];
    }
}
