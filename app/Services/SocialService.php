<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SocialService
{
    public function getPosts(int $perPage = 10, ?int $page = null, array $filters = []): array
    {
        $query = Post::query()
            ->active()
            ->with([
                'user:id,name,username,image_url', 
                'place:id,name,image_urls', 
                'comments.user:id,name,username,image_url', 
                'likes.user:id,name,username,image_url'
            ])
            ->withCount(['likes', 'comments']); 

        if (isset($filters['author_ids']) && is_array($filters['author_ids']) && count($filters['author_ids']) > 0) {
            $query->whereIn('user_id', $filters['author_ids']);
        }

        if (isset($filters['place_id'])) {
            $query->where('place_id', (int) $filters['place_id']);
        }

        $search = isset($filters['search']) ? trim((string) $filters['search']) : null;
        if ($search !== null && $search !== '') {
            $query->where('content', 'like', '%' . $search . '%');
        }

        if (isset($filters['hashtag'])) {
            $tag = '#' . ltrim((string) $filters['hashtag'], '#');
            $query->where('content', 'like', '%' . $tag . '%');
        }

        $posts = $page ? $query->paginate($perPage, ['*'], 'page', (int) $page) : $query->paginate($perPage);
        return [
            'items' => $posts->items(),
            'total' => (int) $posts->total(),
            'current_page' => (int) $posts->currentPage(),
            'per_page' => (int) $posts->perPage(),
            'last_page' => (int) $posts->lastPage(),
        ];
    }

    public function getFollowingPosts(int $userId, int $perPage = 10, ?int $page = null): array
    {
        $ids = \App\Models\UserFollow::where('follower_id', $userId)
            ->pluck('following_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($ids)) {
            return [
                'items' => [],
                'total' => 0,
                'current_page' => (int) ($page ?? 1),
                'per_page' => (int) $perPage,
                'last_page' => 0,
            ];
        }

        $query = Post::query()
            ->active()
            ->whereIn('user_id', $ids)
            ->with([
                'user:id,name,username,image_url', 
                'place:id,name,image_urls', 
                'comments.user:id,name,username,image_url', 
                'likes.user:id,name,username,image_url'
            ])
            ->withCount(['likes', 'comments']);

        $posts = $page ? $query->paginate($perPage, ['*'], 'page', (int) $page) : $query->paginate($perPage);
        return [
            'items' => $posts->items(),
            'total' => (int) $posts->total(),
            'current_page' => (int) $posts->currentPage(),
            'per_page' => (int) $posts->perPage(),
            'last_page' => (int) $posts->lastPage(),
        ];
    }

    public function getTrendingPosts(int $perPage = 10, ?int $page = null): array
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $agg = \Illuminate\Support\Facades\DB::table('posts')
            ->selectRaw('posts.user_id AS uid, COUNT(user_likes.id) AS weekly_likes, COUNT(user_comments.id) AS weekly_comments')
            ->leftJoin('user_likes', function ($join) use ($start, $end) {
                $join->on('user_likes.related_to_id', '=', 'posts.id')
                    ->where('user_likes.related_to_type', \App\Models\Post::class)
                    ->whereBetween('user_likes.created_at', [$start, $end]);
            })
            ->leftJoin('user_comments', function ($join) use ($start, $end) {
                $join->on('user_comments.post_id', '=', 'posts.id')
                    ->whereBetween('user_comments.created_at', [$start, $end]);
            })
            ->groupBy('posts.user_id');

        $query = Post::query()
            ->select('posts.*')
            ->active()
            ->with([
                'user:id,name,username,image_url', 
                'place:id,name,image_urls', 
                'comments.user:id,name,username,image_url', 
                'likes.user:id,name,username,image_url'
            ])
            ->withCount(['likes', 'comments'])
            ->joinSub($agg, 'agg', function ($join) {
                $join->on('posts.user_id', '=', 'agg.uid');
            })
            ->orderByRaw('(agg.weekly_likes + agg.weekly_comments) DESC')
            ->orderBy('posts.created_at', 'desc');

        $posts = $page ? $query->paginate($perPage, ['*'], 'page', (int) $page) : $query->paginate($perPage);

        return [
            'items' => $posts->items(),
            'total' => (int) $posts->total(),
            'current_page' => (int) $posts->currentPage(),
            'per_page' => (int) $posts->perPage(),
            'last_page' => (int) $posts->lastPage(),
        ];
    }

    /**
     * Get post details
     */
    public function getPostDetail(string $postId): array
    {
        $post = Post::query()
            ->with([
                'user:id,name,username,image_url', 
                'place:id,name,image_urls', 
                'comments.user:id,name,username,image_url', 
                'likes.user:id,name,username,image_url'
            ])
            ->withCount(['likes', 'comments'])
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
            $place = \App\Models\Place::find($payload['place_id']);
            if (!$place) {
                throw new \InvalidArgumentException('Place not found');
            }

            // 1. Cek status tempat
            if (!$place->status) {
                throw new \InvalidArgumentException('This place is currently not active.');
            }

            $post = Post::create([
                'user_id' => $user->id,
                'place_id' => $payload['place_id'],
                'content' => $payload['content'],
                'image_urls' => $payload['image_urls'] ?? null,
                'additional_info' => $payload['additional_info'] ?? null,
            ]);
            $user->increment('total_posts');

            return $post->toArray();
        });
    }

    /**
     * Toggle follow/unfollow a user
     */
    public function followUser(User $follower, int $followedId): bool
    {
        $userToFollow = User::find($followedId);
        if (!$userToFollow) {
            throw new \InvalidArgumentException('User not found');
        }

        return DB::transaction(function () use ($follower, $followedId) {
            $follow = \App\Models\UserFollow::where('follower_id', $follower->id)
                ->where('following_id', $followedId)
                ->first();

            if ($follow) {
                // Unfollow
                $follow->delete();
                $follower->decrement('total_following');
                return false;
            }

            // Follow
            \App\Models\UserFollow::create([
                'follower_id' => $follower->id,
                'following_id' => $followedId,
            ]);
            $follower->increment('total_following');
            return true;
        });
    }

    /**
     * Get follow data for a user
     */
    public function getFollowData(int $userId): array
    {
        $followers = \App\Models\UserFollow::where('following_id', $userId)->get();
        $following = \App\Models\UserFollow::where('follower_id', $userId)->get();
        return [
            'followers' => $followers,
            'total_followers' => $followers->count(),
            'following' => $following,
            'total_following' => $following->count(),
        ];
    }

    /**
     * Get likes for a post
     */
    public function getPostLikes(int $postId): array
    {
        $likes = \App\Models\UserLike::where('related_to_type', \App\Models\Post::class)
            ->where('related_to_id', $postId)
            ->with('user:id,name,username,image_url')
            ->get();

        return $likes->toArray();
    }

    /**
     * Like a post
     */
    public function likePost(int $userId, int $postId): bool
    {
        $post = Post::find($postId);
        if (!$post) {
            throw new \InvalidArgumentException('Post not found');
        }

        // Check if the user is already liking
        $like = \App\Models\UserLike::where('user_id', $userId)
            ->where('related_to_type', \App\Models\Post::class)
            ->where('related_to_id', $postId)
            ->first();

        if ($like) {
            // Unlike the post
            $like->delete();
            return false;
        }

        // Create new like
        $like = \App\Models\UserLike::firstOrCreate([
            'user_id' => $userId,
            'related_to_type' => \App\Models\Post::class,
            'related_to_id' => $postId,
        ]);
        return $like->wasRecentlyCreated;
    }
    
    public function getPostComments(int $postId): array
    {
        $comments = \App\Models\UserComment::where('post_id', $postId)
            ->with('user:id,name,username,image_url')
            ->get();

        return $comments->toArray();
    }

    /**
     * Comment on a post
     */
    public function createComment(int $userId, int $postId, string $comment): array
    {
        return DB::transaction(function () use ($userId, $postId, $comment) {
            $post = Post::find($postId);
            if (!$post) {
                throw new \InvalidArgumentException('Post not found');
            }

            $comment = \App\Models\UserComment::create([
                'user_id' => $userId,
                'post_id' => $postId,
                'comment' => $comment,
            ]);

            return $comment->toArray();
        });
    }

    /**
     * Get likes for a comment
     */
    public function commentLikes(int $commentId): array
    {
        $likes = \App\Models\UserLike::where('related_to_type', \App\Models\UserComment::class)
            ->where('related_to_id', $commentId)
            ->with('user:id,name,username,image_url')
            ->get();

        return $likes->toArray();
    }

    /**
     * Like a post
     */
    public function likeComment(int $userId, int $commentId): bool
    {
        $comment = \App\Models\UserComment::find($commentId);
        if (!$comment) {
            throw new \InvalidArgumentException('Comment not found');
        }

        // Check if the user is already liking
        $like = \App\Models\UserLike::where('user_id', $userId)
            ->where('related_to_type', \App\Models\UserComment::class)
            ->where('related_to_id', $commentId)
            ->first();

        if ($like) {
            // Unlike the comment
            $like->delete();
            return false;
        }

        // Create new like
        $like = \App\Models\UserLike::firstOrCreate([
            'user_id' => $userId,
            'related_to_type' => \App\Models\UserComment::class,
            'related_to_id' => $commentId,
        ]);
        return $like->wasRecentlyCreated;
    }
}
