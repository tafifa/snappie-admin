<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\UserActionLog;
use Illuminate\Support\Facades\DB;

class SocialService
{
    protected AchievementChecker $achievementChecker;

    public function __construct(AchievementChecker $achievementChecker)
    {
        $this->achievementChecker = $achievementChecker;
    }

    /**
     * Format gamification result - only include if there are completed achievements/challenges.
     * @param array $achievementResult
     * @return array|null
     */
    protected function formatGamificationResult(array $achievementResult): ?array
    {
        $achievementsUnlocked = $achievementResult["achievements_unlocked"] ?? [];
        $challengesUpdated = $achievementResult["challenges_updated"] ?? [];

        // Filter completed challenges (progress == target)
        $challengesCompleted = array_filter($challengesUpdated, function($challenge) {
            return $challenge["progress"] >= $challenge["target"];
        });

        // Only return gamification data if there are unlocked achievements or completed challenges
        if (empty($achievementsUnlocked) && empty($challengesCompleted)) {
            return null;
        }

        $result = [];

        if (!empty($achievementsUnlocked)) {
            $result["achievements_unlocked"] = array_values($achievementsUnlocked);
        }

        if (!empty($challengesCompleted)) {
            $result["challenges_completed"] = array_values($challengesCompleted);
        }

        // Calculate bonus rewards
        $bonusCoins = 0;
        $bonusXp = 0;
        foreach ($achievementsUnlocked as $achievement) {
            $bonusCoins += $achievement["reward_coins"] ?? 0;
            $bonusXp += $achievement["reward_xp"] ?? 0;
        }
        foreach ($challengesCompleted as $challenge) {
            $bonusCoins += $challenge["reward_coins"] ?? 0;
            $bonusXp += $challenge["reward_xp"] ?? 0;
        }

        if ($bonusCoins > 0 || $bonusXp > 0) {
            $result["rewards"] = [
                "coins" => $bonusCoins,
                "xp" => $bonusXp,
            ];
        }

        return $result;
    }

    public function getPosts(
        int $perPage = 10,
        ?int $page = null,
        array $filters = [],
    ): array {
        $query = Post::query()
            ->active()
            ->with([
                "user:id,name,username,image_url",
                "place:id,name,image_urls",
                "comments.user:id,name,username,image_url",
                "likes.user:id,name,username,image_url",
            ])
            ->withCount(["likes", "comments"]);

        if (
            isset($filters["author_ids"]) &&
            is_array($filters["author_ids"]) &&
            count($filters["author_ids"]) > 0
        ) {
            $query->whereIn("user_id", $filters["author_ids"]);
        }

        if (isset($filters["place_id"])) {
            $query->where("place_id", (int) $filters["place_id"]);
        }

        $search = isset($filters["search"])
            ? trim((string) $filters["search"])
            : null;
        if ($search !== null && $search !== "") {
            $query->where("content", "like", "%" . $search . "%");
        }

        if (isset($filters["hashtag"])) {
            $tag = "#" . ltrim((string) $filters["hashtag"], "#");
            $query->where("content", "like", "%" . $tag . "%");
        }

        $query->orderBy('created_at', 'desc');

        $posts = $page
            ? $query->paginate($perPage, ["*"], "page", (int) $page)
            : $query->paginate($perPage);
        return [
            "items" => $posts->items(),
            "total" => (int) $posts->total(),
            "current_page" => (int) $posts->currentPage(),
            "per_page" => (int) $posts->perPage(),
            "last_page" => (int) $posts->lastPage(),
        ];
    }

    public function getFollowingPosts(
        int $userId,
        int $perPage = 10,
        ?int $page = null,
    ): array {
        $ids = \App\Models\UserFollow::where("follower_id", $userId)
            ->pluck("following_id")
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        if (empty($ids)) {
            return [
                "items" => [],
                "total" => 0,
                "current_page" => (int) ($page ?? 1),
                "per_page" => (int) $perPage,
                "last_page" => 0,
            ];
        }

        $query = Post::query()
            ->active()
            ->whereIn("user_id", $ids)
            ->with([
                "user:id,name,username,image_url",
                "place:id,name,image_urls",
                "comments.user:id,name,username,image_url",
                "likes.user:id,name,username,image_url",
            ])
            ->withCount(["likes", "comments"]);

        $posts = $page
            ? $query->paginate($perPage, ["*"], "page", (int) $page)
            : $query->paginate($perPage);
        return [
            "items" => $posts->items(),
            "total" => (int) $posts->total(),
            "current_page" => (int) $posts->currentPage(),
            "per_page" => (int) $posts->perPage(),
            "last_page" => (int) $posts->lastPage(),
        ];
    }

    public function getTrendingPosts(
        int $perPage = 10,
        ?int $page = null,
    ): array {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $agg = \Illuminate\Support\Facades\DB::table("posts")
            ->selectRaw(
                "posts.user_id AS uid, COUNT(user_likes.id) AS weekly_likes, COUNT(user_comments.id) AS weekly_comments",
            )
            ->leftJoin("user_likes", function ($join) use ($start, $end) {
                $join
                    ->on("user_likes.related_to_id", "=", "posts.id")
                    ->where(
                        "user_likes.related_to_type",
                        \App\Models\Post::class,
                    )
                    ->whereBetween("user_likes.created_at", [$start, $end]);
            })
            ->leftJoin("user_comments", function ($join) use ($start, $end) {
                $join
                    ->on("user_comments.post_id", "=", "posts.id")
                    ->whereBetween("user_comments.created_at", [$start, $end]);
            })
            ->groupBy("posts.user_id");

        $query = Post::query()
            ->select("posts.*")
            ->active()
            ->with([
                "user:id,name,username,image_url",
                "place:id,name,image_urls",
                "comments.user:id,name,username,image_url",
                "likes.user:id,name,username,image_url",
            ])
            ->withCount(["likes", "comments"])
            ->joinSub($agg, "agg", function ($join) {
                $join->on("posts.user_id", "=", "agg.uid");
            })
            ->orderByRaw("(agg.weekly_likes + agg.weekly_comments) DESC")
            ->orderBy("posts.created_at", "desc");

        $posts = $page
            ? $query->paginate($perPage, ["*"], "page", (int) $page)
            : $query->paginate($perPage);

        return [
            "items" => $posts->items(),
            "total" => (int) $posts->total(),
            "current_page" => (int) $posts->currentPage(),
            "per_page" => (int) $posts->perPage(),
            "last_page" => (int) $posts->lastPage(),
        ];
    }

    /**
     * Get post details
     */
    public function getPostDetail(string $postId): array
    {
        $post = Post::query()
            ->with([
                "user:id,name,username,image_url",
                "place:id,name,image_urls",
                "comments.user:id,name,username,image_url",
                "likes.user:id,name,username,image_url",
            ])
            ->withCount(["likes", "comments"])
            ->find($postId);

        if (!$post) {
            return [];
        }
        return $post->toArray();
    }

    /**
     * Create a post
     */
    public function createPost(User $user, array $payload): array
    {
        return DB::transaction(function () use ($user, $payload) {
            $place = \App\Models\Place::find($payload["place_id"]);
            if (!$place) {
                throw new \InvalidArgumentException("Place not found");
            }

            // 1. Cek status tempat
            if (!$place->status) {
                throw new \InvalidArgumentException(
                    "This place is currently not active.",
                );
            }

            $post = Post::create([
                "user_id" => $user->id,
                "place_id" => $payload["place_id"],
                "content" => $payload["content"],
                "image_urls" => $payload["image_urls"] ?? null,
                "additional_info" => $payload["additional_info"] ?? null,
            ]);
            $user->increment("total_post");

            // Log post action for achievement tracking
            $achievementResult = $this->achievementChecker->checkOnAction(
                $user,
                UserActionLog::ACTION_POST,
                [
                    "post_id" => $post->id,
                    "place_id" => $place->id,
                    "place_name" => $place->name,
                ]
            );

            $result = [
                "post" => $post->toArray(),
            ];

            // Add gamification if there are completed achievements/challenges
            $gamification = $this->formatGamificationResult($achievementResult);
            if ($gamification !== null) {
                $result["gamification"] = $gamification;
            }

            return $result;
        });
    }

    /**
     * Delete a post
     */
    public function deletePost(User $user, int $postId): bool
    {
        return DB::transaction(function () use ($user, $postId) {
            $post = Post::find($postId);

            if (!$post) {
                throw new \InvalidArgumentException("Post not found");
            }

            // Check if user is the owner of the post
            if ($post->user_id !== $user->id) {
                throw new \InvalidArgumentException("You are not authorized to delete this post");
            }

            // Delete the post
            $post->delete();

            // Decrement user's total_post count
            if ($user->total_post > 0) {
                $user->decrement("total_post");
            }

            return true;
        });
    }

    /**
     * Toggle follow/unfollow a user
     */
    public function followUser(User $follower, int $followedId): array
    {
        $userToFollow = User::find($followedId);
        if (!$userToFollow) {
            throw new \InvalidArgumentException("User not found");
        }

        return DB::transaction(function () use (
            $follower,
            $followedId,
            $userToFollow,
        ) {
            $follow = \App\Models\UserFollow::where(
                "follower_id",
                $follower->id,
            )
                ->where("following_id", $followedId)
                ->first();

            if ($follow) {
                // Unfollow
                $follow->delete();
                $follower->decrement("total_following");
                $userToFollow->decrement("total_follower");
                return [
                    "action" => "unfollow",
                    "user_id" => $followedId,
                ];
            }

            // Follow
            \App\Models\UserFollow::create([
                "follower_id" => $follower->id,
                "following_id" => $followedId,
            ]);
            $follower->increment("total_following");
            $userToFollow->increment("total_follower");

            // Log follow action for achievement tracking
            $achievementResult = $this->achievementChecker->checkOnAction(
                $follower,
                UserActionLog::ACTION_FOLLOW,
                [
                    "followed_user_id" => $followedId,
                    "followed_username" => $userToFollow->username,
                ]
            );

            $result = [
                "action" => "follow",
                "user_id" => $followedId,
            ];

            // Add gamification if there are completed achievements/challenges
            $gamification = $this->formatGamificationResult($achievementResult);
            if ($gamification !== null) {
                $result["gamification"] = $gamification;
            }

            return $result;
        });
    }

    // /**
    //  * Get follow data for a user
    //  */
    // public function getFollowData(int $userId): array
    // {
    //     $followers = \App\Models\UserFollow::where("following_id", $userId)
    //         ->with(["follower:id,username,name,image_url"])
    //         ->get();
    //     $following = \App\Models\UserFollow::where("follower_id", $userId)
    //         ->with(["following:id,username,name,image_url"])
    //         ->get();
    //     return [
    //         "followers" => $followers,
    //         "total_followers" => $followers->count(),
    //         "following" => $following,
    //         "total_following" => $following->count(),
    //     ];
    // }

    /**
     * Get follow data for a user
     * @param int $userId The user to get follow data for
     * @param int|null $currentUserId The current logged-in user to check is_followed status
     *
     * is_followed logic:
     * - Followers view: true = saling follow (current user juga follow mereka)
     * - Following view: true = saling follow (mereka juga follow current user)
     */
    public function getFollowData(
        int $userId,
        ?int $currentUserId = null,
    ): array {
        $followers = \App\Models\UserFollow::where("following_id", $userId)
            ->with(["follower:id,username,name,image_url"])
            ->get();
        $following = \App\Models\UserFollow::where("follower_id", $userId)
            ->with(["following:id,username,name,image_url"])
            ->get();

        // Get list of user IDs that current user is following (untuk cek di Followers view)
        $currentUserFollowingIds = [];
        // Get list of user IDs that are following current user (untuk cek di Following view)
        $currentUserFollowerIds = [];

        if ($currentUserId) {
            $currentUserFollowingIds = \App\Models\UserFollow::where(
                "follower_id",
                $currentUserId,
            )
                ->pluck("following_id")
                ->toArray();
            $currentUserFollowerIds = \App\Models\UserFollow::where(
                "following_id",
                $currentUserId,
            )
                ->pluck("follower_id")
                ->toArray();
        }

        // Map followers with is_followed status
        // is_followed = true jika current user juga follow mereka (saling follow/teman)
        $followersData = $followers->map(function ($follow) use (
            $currentUserId,
            $currentUserFollowingIds,
        ) {
            $follower = $follow->follower;
            return [
                "id" => $follow->id,
                "follower_id" => $follow->follower_id,
                "following_id" => $follow->following_id,
                "follower" => $follower
                    ? [
                        "id" => $follower->id,
                        "username" => $follower->username,
                        "name" => $follower->name,
                        "image_url" => $follower->image_url,
                        "is_followed" => $currentUserId
                            ? in_array($follower->id, $currentUserFollowingIds)
                            : false,
                    ]
                    : null,
                "created_at" => $follow->created_at,
                "updated_at" => $follow->updated_at,
            ];
        });

        // Map following with is_followed status
        // is_followed = true jika mereka juga follow current user (saling follow/teman)
        $followingData = $following->map(function ($follow) use (
            $currentUserId,
            $currentUserFollowerIds,
        ) {
            $followingUser = $follow->following;
            return [
                "id" => $follow->id,
                "follower_id" => $follow->follower_id,
                "following_id" => $follow->following_id,
                "following" => $followingUser
                    ? [
                        "id" => $followingUser->id,
                        "username" => $followingUser->username,
                        "name" => $followingUser->name,
                        "image_url" => $followingUser->image_url,
                        "is_followed" => $currentUserId
                            ? in_array(
                                $followingUser->id,
                                $currentUserFollowerIds,
                            )
                            : false,
                    ]
                    : null,
                "created_at" => $follow->created_at,
                "updated_at" => $follow->updated_at,
            ];
        });

        return [
            "followers" => $followersData,
            "total_followers" => $followers->count(),
            "following" => $followingData,
            "total_following" => $following->count(),
        ];
    }

    /**
     * Get likes for a post
     */
    public function getPostLikes(int $postId): array
    {
        $likes = \App\Models\UserLike::where(
            "related_to_type",
            \App\Models\Post::class,
        )
            ->where("related_to_id", $postId)
            ->with("user:id,name,username,image_url")
            ->get();

        return $likes->toArray();
    }

    /**
     * Like a post
     */
    public function likePost(int $userId, int $postId): array
    {
        $post = Post::find($postId);
        if (!$post) {
            throw new \InvalidArgumentException("Post not found");
        }

        return DB::transaction(function () use ($userId, $postId, $post) {
            // Check if the user is already liking
            $like = \App\Models\UserLike::where("user_id", $userId)
                ->where("related_to_type", \App\Models\Post::class)
                ->where("related_to_id", $postId)
                ->first();

            if ($like) {
                // Unlike the post
                $like->delete();
                $post->decrement("total_like");
                return [
                    "action" => "unlike",
                    "post_id" => $postId,
                    "total_like" => $post->fresh()->total_like,
                ];
            }

            // Create new like
            $like = \App\Models\UserLike::firstOrCreate([
                "user_id" => $userId,
                "related_to_type" => \App\Models\Post::class,
                "related_to_id" => $postId,
            ]);

            // Increment total_like on post
            $post->increment("total_like");

            // Log like action for achievement tracking
            $user = User::find($userId);
            $achievementResult = $this->achievementChecker->checkOnAction(
                $user,
                UserActionLog::ACTION_LIKE,
                [
                    "post_id" => $postId,
                    "post_author_id" => $post->user_id,
                ]
            );

            $result = [
                "action" => "like",
                "post_id" => $postId,
                "total_like" => $post->fresh()->total_like,
            ];

            // Add gamification if there are completed achievements/challenges
            $gamification = $this->formatGamificationResult($achievementResult);
            if ($gamification !== null) {
                $result["gamification"] = $gamification;
            }

            return $result;
        });
    }

    public function getPostComments(int $postId): array
    {
        $comments = \App\Models\UserComment::where("post_id", $postId)
            ->with("user:id,name,username,image_url")
            ->get();

        return $comments->toArray();
    }

    /**
     * Comment on a post
     */
    public function createComment(
        int $userId,
        int $postId,
        string $comment,
    ): array {
        return DB::transaction(function () use ($userId, $postId, $comment) {
            $post = Post::find($postId);
            if (!$post) {
                throw new \InvalidArgumentException("Post not found");
            }

            $comment = \App\Models\UserComment::create([
                "user_id" => $userId,
                "post_id" => $postId,
                "comment" => $comment,
            ]);

            // Increment total_comment on post
            $post->increment("total_comment");

            // Log comment action for achievement tracking
            $user = User::find($userId);
            $achievementResult = $this->achievementChecker->checkOnAction(
                $user,
                UserActionLog::ACTION_COMMENT,
                [
                    "post_id" => $postId,
                    "comment_id" => $comment->id,
                ]
            );

            $result = [
                "comment" => $comment->toArray(),
            ];

            // Add gamification if there are completed achievements/challenges
            $gamification = $this->formatGamificationResult($achievementResult);
            if ($gamification !== null) {
                $result["gamification"] = $gamification;
            }

            return $result;
        });
    }

    /**
     * Get likes for a comment
     */
    public function commentLikes(int $commentId): array
    {
        $likes = \App\Models\UserLike::where(
            "related_to_type",
            \App\Models\UserComment::class,
        )
            ->where("related_to_id", $commentId)
            ->with("user:id,name,username,image_url")
            ->get();

        return $likes->toArray();
    }

    /**
     * Like a comment
     */
    public function likeComment(int $userId, int $commentId): array
    {
        $comment = \App\Models\UserComment::find($commentId);
        if (!$comment) {
            throw new \InvalidArgumentException("Comment not found");
        }

        // Check if the user is already liking
        $like = \App\Models\UserLike::where("user_id", $userId)
            ->where("related_to_type", \App\Models\UserComment::class)
            ->where("related_to_id", $commentId)
            ->first();

        if ($like) {
            // Unlike the comment
            $like->delete();
            $comment->decrement("total_like");
            return [
                "action" => "unlike",
                "comment_id" => $commentId,
                "total_like" => $comment->fresh()->total_like,
            ];
        }

        // Create new like
        $like = \App\Models\UserLike::firstOrCreate([
            "user_id" => $userId,
            "related_to_type" => \App\Models\UserComment::class,
            "related_to_id" => $commentId,
        ]);

        // Increment total_like on comment
        $comment->increment("total_like");

        return [
            "action" => "like",
            "comment_id" => $commentId,
            "total_like" => $comment->fresh()->total_like,
        ];
    }
}
