<?php

namespace App\Services;

use App\Models\Place;
use App\Models\Post;
use App\Models\User;
use App\Models\UserComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;



class SocialMediaService
{
    private const PAGINATION_LIMIT = 10;
    /**
     * Membuat satu pengguna mengikuti pengguna lain.
     *
     * @param User $follower Pengguna yang melakukan follow.
     * @param User $following Pengguna yang akan di-follow.
     * @return bool True jika berhasil, false jika gagal (misal, follow diri sendiri).
     */
    public function follow(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->id === $targetUser->id || $this->isFollowing($currentUser, $targetUser)) {
            return false;
        }

        DB::transaction(function () use ($currentUser, $targetUser) {
            // 1. Tambahkan relasi
            $currentUser->following()->attach($targetUser->id);

            // 2. Update counter
            $currentUser->increment('total_following');
            $targetUser->increment('total_follower');
        });

        return true;
    }

    /**
     * Membuat satu pengguna berhenti mengikuti pengguna lain.
     *
     * @param User $currentUser Pengguna yang melakukan unfollow.
     * @param User $following Pengguna yang akan di-unfollow.
     * @return bool True jika berhasil.
     */
    public function unfollow(User $currentUser, User $targetUser): bool
    {
        if (!$this->isFollowing($currentUser, $targetUser)) {
            return false;
        }

        DB::transaction(function () use ($currentUser, $targetUser) {
            // 1. Hapus relasi
            $currentUser->following()->detach($targetUser->id);

            // 2. Update counter (pastikan tidak di bawah nol)
            $currentUser->decrement('total_following');
            $targetUser->decrement('total_follower');
        });

        return true;
    }

    /**
     * Memeriksa apakah currentUser mengikuti targetUser.
     *
     * @param User $currentUser
     * @param User $targetUser
     * @return bool
     */
    public function isFollowing(User $currentUser, User $targetUser): bool
    {
        return $currentUser->following()->where('following_id', $targetUser->id)->exists();
    }

    /**
     * Memeriksa apakah currentUser diikuti oleh targetUser.
     *
     * @param User $currentUser
     * @param User $targetUser
     * @return bool
     */
    public function isFollowed(User $currentUser, User $targetUser): bool
    {
        return $currentUser->followers()->where('follower_id', $targetUser->id)->exists();
    }

    /**
     * Mendapatkan daftar followers dari user.
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowers(User $user, int $limit = self::PAGINATION_LIMIT)
    {
        return $user->followers()->limit($limit)->get();
    }

    /**
     * Mendapatkan daftar following dari user.
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowing(User $user, int $limit = self::PAGINATION_LIMIT)
    {
        return $user->following()->limit($limit)->get();
    }

    /**
     * Mendapatkan profil pengguna dengan informasi follow status.
     *
     * @param User $user Pengguna yang profilnya akan diambil
     * @param User|null $currentUser Pengguna yang sedang login (untuk cek status follow)
     * @return array
     */
    public function getUserProfile(User $user): array
    {
        $profile = [
            'id' => $user->id,
            'name' => $user->name,
            'image_url' => $user->image_url,
            'total_post' => $user->total_post,
            'total_follower' => $user->total_follower,
            'total_following' => $user->total_following,
        ];

        return $profile;
    }

    // POST
    /**
     * Mendapatkan feed posts default (tanpa user spesifik).
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getDefaultFeedPosts(int $limit = self::PAGINATION_LIMIT): LengthAwarePaginator
    {
        $feedPosts = Post::where('status', true)
            ->with([
                'user:id,name,image_url',
                'place:id,name,description',
                'likes.user:id,name,image_url',
                'comments.user:id,name,image_url'
            ])
            ->withCount(['likes', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $feedPosts;
    }

    /**
     * Mendapatkan feed posts dari pengguna dan pengguna yang dia follow.
     *
     * @param User $user
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getFeedPosts(User $user, int $perPage = self::PAGINATION_LIMIT): LengthAwarePaginator
    {
        // Get IDs of users that current user follows
        $followingIds = $user->following()->pluck('following_id')->toArray();

        // Include user's own posts
        $followingIds[] = $user->id;

        $feedPosts = Post::whereIn('user_id', $followingIds)
            ->where('status', true)
            ->with([
                'user:id,name,image_url',
                'place:id,name,description',
                'likes.user:id,name,image_url',
                'comments.user:id,name,image_url'
            ])
            ->withCount(['likes', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // if ($feedPosts->isEmpty()) {
        //     // If user follows no one or no posts found, return default feed
        //     return $this->getDefaultFeedPosts($perPage);
        // }

        return $feedPosts;
    }

    /**
     * Mendapatkan posts trending berdasarkan engagement rate dalam periode waktu tertentu
     *
     * @param int $perPage
     * @param int $hours Jumlah jam ke belakang untuk menghitung trending (default: 24 jam)
     * @return LengthAwarePaginator
     */
    public function getTrendingPosts(int $perPage = self::PAGINATION_LIMIT): LengthAwarePaginator
    {
        $trendingPosts = Post::where('status', true)
            ->where('created_at', '>=', now())
            ->with([
                'user:id,name,image_url',
                'place:id,name,description',
                'likes.user:id,name,image_url',
                'comments.user:id,name,image_url'
            ])
            ->withCount(['likes', 'comments'])
            ->where('total_like', '>=', 5) // Minimal 5 likes untuk dianggap trending
            ->orderByRaw('(total_like + total_comment * 2) / EXTRACT(EPOCH FROM (NOW() - created_at)) DESC')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);   

        // if ($trendingPosts->isEmpty()) {
        //     // Jika tidak ada post trending, fallback ke post terbaru
        //     return $this->getDefaultFeedPosts($perPage);
        // }

        return $trendingPosts;
    }

    /**
     * Get posts by user with pagination
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPostsByUser(User $user, int $perPage = self::PAGINATION_LIMIT): LengthAwarePaginator
    {
        return Post::where('user_id', $user->id)
            ->where('status', true)
            ->with([
                'user:id,name,image_url',
                'place:id,name,description',
                'likes.user:id,name,image_url',
                'comments.user:id,name,image_url'
            ])
            ->withCount(['likes', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get posts by place with pagination
     *
     * @param Place $place
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPostsByPlace(Place $place, int $perPage = self::PAGINATION_LIMIT): LengthAwarePaginator
    {   
        return Post::where('place_id', $place->id)
            ->where('status', true)
            ->with([
                'user:id,name,image_url',
                'place:id,name,description',
                'likes.user:id,name,image_url',
                'comments.user:id,name,image_url'
            ])
            ->withCount(['likes', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get post by ID with related data
     *
     * @param int $id Post ID
     * @return Post|null
     */
    public function getPostById(int $id): ?Post
    {
        return Post::with([
                'user:id,name,image_url',
                'place:id,name,description',
                'likes.user:id,name,image_url',
                'comments.user:id,name,image_url'
            ])
            ->withCount(['likes', 'comments'])
            ->find($id);
    }

    /**
     * Membuat post baru dengan validasi dan optimasi.
     *
     * @param User $user
     * @param array $data
     * @return Post
     * @throws \Exception
     */
    public function createPost(User $user, array $data): Post
    {
        return DB::transaction(function () use ($data, $user) {
            $post = Post::create([
                'user_id' => $user->id,
                'place_id' => $data['place_id'] ?? null,
                'content' => $data['content'] ?? null,
                'image_urls' => $data['image_urls'] ?? null,
                'additional_info' => $data['additional_info'] ?? null,
                'status' => $data['status'] ?? true,
            ]);

            $user->increment('total_post');

            return $post->load([
                'user:id,name,email,avatar,total_follower,total_following',
                'place:id,name,location,address,coin_reward'
            ]);
        });
    }

    /**
     * Update post dengan validasi dan authorization.
     *
     * @param Post $post
     * @param User $user
     * @param array $data
     * @return Post
     * @throws \Exception
     */
    public function updatePost(Post $post, User $user, array $data): Post
    {
        // Authorization check
        if ($post->user_id !== $user->id && !$user->is_admin) {
            throw new \Exception('Unauthorized to update this post');
        }

        return DB::transaction(function () use ($post, $data) {
            // Filter data yang boleh diupdate
            $allowedFields = ['content', 'image_urls', 'additional_info', 'place_id', 'status'];
            $updateData = array_intersect_key($data, array_flip($allowedFields));
            $updateData = array_filter($updateData, function($value) {
                return $value !== null && $value !== '';
            });

            $post->update($updateData);

            return $post->fresh([
                'user:id,name,email,avatar,total_follower,total_following',
                'place:id,name,location,address,coin_reward'
            ]);
        });
    }

    /**
     * Hapus post dengan validasi dan cleanup.
     *
     * @param Post $post
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function deletePost(Post $post, User $user): bool
    {
        // Authorization check
        if ($post->user_id !== $user->id && !$user->is_admin) {
            throw new \Exception('Unauthorized to delete this post');
        }

        return DB::transaction(function () use ($post, $user) {
            // Hapus semua likes dan comments terkait
            $post->likes()->delete();
            $post->comments()->delete();
            
            // Hapus post
            $deleted = $post->delete();

            if ($deleted) {
                // Decrease user's total posts count hanya jika user adalah pemilik
                if ($post->user_id === $user->id && method_exists($user, 'decrement')) {
                    $user->decrement('total_post');
                }
            }

            return $deleted;
        });
    }

        /**
     * Memberikan atau menarik 'like' pada sebuah item (Post, Article, Review, dll).
     *
     * @param User $user Pengguna yang melakukan aksi.
     * @param Model $likeable Objek yang di-'like' (harus memiliki relasi 'likes').
     * @return bool True jika status 'like' berubah.
     */
    public function toggleLike(User $user, Model $likeable): bool
    {
        $existingLike = $likeable->likes()->where('user_id', $user->id)->first();

        return DB::transaction(function () use ($user, $likeable, $existingLike) {
            if ($existingLike) {
                // Jika sudah ada, hapus (unlike)
                $existingLike->delete();
                if (method_exists($likeable, 'decrement')) {
                    $likeable->decrement('total_like');
                }
            } else {
                // Jika belum ada, buat (like)
                $likeable->likes()->create(['user_id' => $user->id]);
                if (method_exists($likeable, 'increment')) {
                    $likeable->increment('total_like');
                }
            }
            return true;
        });
    }

    /**
     * Menambahkan komentar ke sebuah item.
     *
     * @param User $user
     * @param Model $commentable Objek yang dikomentari.
     * @param string $content Isi komentar.
     * @return UserComment
     */
    public function addComment(User $user, Model $commentable, string $content): UserComment
    {
        return DB::transaction(function () use ($user, $commentable, $content) {
            $comment = $commentable->comments()->create([
                'user_id' => $user->id,
                'comment' => $content,
            ]);

            if (method_exists($commentable, 'increment')) {
                $commentable->increment('total_comment');
            }

            return $comment;
        });
    }

    /**
     * Menghapus komentar dari sebuah item.
     *
     * @param UserComment $comment
     * @param User $user User yang menghapus (untuk validasi)
     * @return bool
     * @throws \Exception
     */
    public function deleteComment(UserComment $comment): bool
    {
        return DB::transaction(function () use ($comment) {
            // Ambil commentable object sebelum menghapus
            $commentable = $comment->commentable;
            
            // Hapus komentar
            $deleted = $comment->delete();

            // Decrement total_comment jika method exists
            if ($deleted && $commentable && method_exists($commentable, 'decrement')) {
                $commentable->decrement('total_comment');
            }

            return $deleted;
        });
    }

    /**
     * Get paginated posts with user and like information.
     *
     * @param int $perPage
     * @param int|null $userId
     * @return LengthAwarePaginator
     */
    public function getPaginatedPosts(int $perPage = 10, ?int $userId = null): LengthAwarePaginator
    {
        $query = Post::with([
                'user:id,name,image_url',
                'place:id,name,description',
                'likes.user:id,name,image_url',
                'comments.user:id,name,image_url'
            ])
            ->withCount('likes', 'comments')
            ->orderBy('created_at', 'desc');

        if ($userId) {
            $query->withExists(['likes as is_liked' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }]);
        }

        return $query->paginate($perPage);
    }
}
