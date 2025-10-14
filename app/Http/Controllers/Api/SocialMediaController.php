<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialMediaRequest;
use App\Models\Place;
use App\Models\Review;
use App\Services\SocialMediaService;
use App\Traits\ApiResponseTrait;
use App\Models\User;
use App\Models\Post;
use App\Models\UserComment;
use Illuminate\Http\JsonResponse;

class SocialMediaController extends Controller
{
    use ApiResponseTrait;

    protected SocialMediaService $socialMediaService;

    public function __construct(SocialMediaService $socialMediaService)
    {
        $this->socialMediaService = $socialMediaService;
    }

    /**
     * Follow a user
     */
    public function follow(SocialMediaRequest $request): JsonResponse
    {
        try {
            $follower = User::findOrFail($request->follower_id);
            $currentUser = User::findOrFail($request->user_id);

            $result = $this->socialMediaService->follow($follower, $currentUser);

            return $this->successResponse($result, 'User followed successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to follow user', 500, $e->getMessage());
        }
    }

    /**
     * Unfollow a user
     */
    public function unfollow(SocialMediaRequest $request): JsonResponse
    {
        try {
            $currentUser = User::findOrFail($request->user_id);
            $follower = User::findOrFail($request->follower_id);

            $result = $this->socialMediaService->unfollow($follower, $currentUser);

            return $this->successResponse($result, 'User unfollowed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to unfollow user', 500, $e->getMessage());
        }
    }

    /**
     * Get user followers
     */
    public function getFollowers(SocialMediaRequest $request): JsonResponse
    {
        try {
            $currentUser = User::findOrFail($request->user_id);
            $limit = $request->get('limit', 20);

            $followers = $this->socialMediaService->getFollowers($currentUser, $limit);

            return $this->successResponse($followers, 'Followers retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get followers', 500, $e->getMessage());
        }
    }

    /**
     * Get user following
     */
    public function getFollowing(SocialMediaRequest $request): JsonResponse
    {
        try {
            $currentUser = User::findOrFail($request->user_id);
            $limit = $request->get('limit', 20);

            $following = $this->socialMediaService->getFollowing($currentUser, $limit);

            return $this->successResponse($following, 'Following retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get following', 500, $e->getMessage());
        }
    }

    /**
     * Check if user is following another user
     */
    public function isFollowing(SocialMediaRequest $request): JsonResponse
    {
        try {
            $currentUser = User::findOrFail($request->user_id);
            $targetUser = User::findOrFail($request->target_user_id);

            $isFollowing = $this->socialMediaService->isFollowing($currentUser, $targetUser);

            return $this->successResponse(['is_following' => $isFollowing], 'Follow status retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to check follow status', 500, $e->getMessage());
        }
    }

    /**
     * Get user profile with follow status
     */
    public function getUserProfile(int $user_id): JsonResponse
    {
        try {
            $user = User::findOrFail($user_id);

            $profile = $this->socialMediaService->getUserProfile($user);

            return $this->successResponse($profile, 'User profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get user profile', 500, $e->getMessage());
        }
    }

    /**
     * Get default feed posts (latest posts)
     */
    public function getDefaultFeedPosts(SocialMediaRequest $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);

            $posts = $this->socialMediaService->getDefaultFeedPosts($perPage);

            return $this->successResponse($posts, 'Default feed posts retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get default feed posts', 500, $e->getMessage());
        }
    }

    /**
     * Get feed posts for user
     */
    public function getFeedPosts(SocialMediaRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 10);

            $posts = $this->socialMediaService->getFeedPosts($user, $perPage);

            return $this->successResponse($posts, 'Feed posts retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get feed posts', 500, $e->getMessage());
        }
    }

    /**
     * Get trending posts
     */
    public function getTrendingPosts(SocialMediaRequest $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 20);
            $hours = $request->get('hours', 24);

            $posts = $this->socialMediaService->getTrendingPosts($perPage);

            return $this->successResponse($posts, 'Trending posts retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get trending posts', 500, $e->getMessage());
        }
    }

    /**
     * Get posts by user
     */
    public function getPostsByUser(int $user_id, SocialMediaRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($user_id);
            $perPage = $request->get('per_page', 20);

            $posts = $this->socialMediaService->getPostsByUser($user, $perPage);

            return $this->successResponse($posts, 'User posts retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get user posts', 500, $e->getMessage());
        }
    }

    /**
     * Get posts by place
     */
    public function getPostsByPlace(int $place_id, SocialMediaRequest $request): JsonResponse
    {
        try {
            $place = Place::findOrFail($place_id);
            $perPage = $request->get('per_page', 20);

            $posts = $this->socialMediaService->getPostsByPlace($place, $perPage);

            return $this->successResponse($posts, 'Place posts retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get place posts', 500, $e->getMessage());
        }
    }

    /**
     * Get post by ID
     */
    public function getPostById(int $post_id): JsonResponse
    {
        try {
            $post = $this->socialMediaService->getPostById($post_id);

            if ($post) {
                return $this->successResponse($post, 'Post retrieved successfully');
            } else {
                return $this->errorResponse('Post not found', 404);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get post', 500, $e->getMessage());
        }
    }

    /**
     * Create a new post
     */
    public function createPost(SocialMediaRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $place = Place::findOrFail($request->place_id);

            $post = $this->socialMediaService->createPost($user, $place, $request->only(['content', 'image_url', 'additional_info']));

            return $this->successResponse($post, 'Post created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create post', 500, $e->getMessage());
        }
    }

    /**
     * Update a post
     */
    public function updatePost(int $post_id, SocialMediaRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $post = Post::findOrFail($post_id);

            $updated = $this->socialMediaService->updatePost($user, $post, $request->only(['content', 'image_url', 'additional_info']));

            if ($updated) {
                return $this->successResponse($post, 'Post updated successfully');
            } else {
                return $this->errorResponse('Failed to update post', 500);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update post', 500, $e->getMessage());
        }
    }

    /**
     * Delete a post
     */
    public function deletePost(int $post_id, SocialMediaRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $post = Post::findOrFail($post_id);

            $deleted = $this->socialMediaService->deletePost($user, $post);

            if ($deleted) {
                return $this->successResponse(null, 'Post deleted successfully');
            } else {
                return $this->errorResponse('Failed to delete post', 500);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete post', 500, $e->getMessage());
        }
    }

    /**
     * Like a post
     */
    public function likePost(int $target_id, SocialMediaRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            
            // Determine target model based on request parameters
            $likeable = null;
            $targetType = null;
            
            if ($request->filled('post_id')) {
                $likeable = Post::findOrFail($target_id);
                $targetType = 'post';
            } elseif ($request->filled('comment_id')) {
                $likeable = UserComment::findOrFail($target_id);
                $targetType = 'comment';
            } elseif ($request->filled('review_id')) {
                $likeable = Review::findOrFail($target_id);
                $targetType = 'review';
            } else {
                // Default to post if no specific type is provided
                $likeable = Post::findOrFail($target_id);
                $targetType = 'post';
            }

            $result = $this->socialMediaService->toggleLike($user, $likeable);

            if ($result) {
                return $this->successResponse(null, ucfirst($targetType) . ' liked successfully');
            } else {
                return $this->errorResponse('Failed to like ' . $targetType, 500);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to like post', 500, $e->getMessage());
        }
    }

    /**
     * Unlike a post
     */
    public function unlikePost(SocialMediaRequest $request): JsonResponse
    {
        try {
            $targetId = $request->post_id ?? $request->comment_id ?? $request->review_id;

            $user = User::findOrFail($request->user_id);
            $result = $this->socialMediaService->toggleLike($user, $targetId);

            if ($result) {
                return $this->successResponse(null, 'Post unliked successfully');
            } else {
                return $this->errorResponse('Failed to unlike post', 500);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to unlike post', 500, $e->getMessage());
        }
    }

    /**
     * Get post comments
     */
    public function getPostComments(SocialMediaRequest $request): JsonResponse
    {
        try {
            $post = $this->socialMediaService->getPostById($request->post_id);

            if (!$post) {
                return $this->errorResponse('Post not found', 404);
            }

            $comments = $post->comments()->with(['user:id,name,email,avatar'])->get();

            return $this->successResponse($comments, 'Comments retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get post comments', 500, $e->getMessage());
        }
    }

    /**
     * Comment on a post
     */
    public function commentOnPost(SocialMediaRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);
            $targetId = $request->post_id ?? $request->comment_id ?? $request->review_id;

            $comment = $this->socialMediaService->addComment($user, $targetId, $request->get('content'));
            if (!$comment) {
                return $this->errorResponse('Failed to add comment', 500);
            }

            return $this->successResponse($comment, 'Comment added successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add comment', 500, $e->getMessage());
        }
    }

    /**
     * Delete a comment
     */
    public function deleteComment(SocialMediaRequest $request): JsonResponse
    {
        try {
            $comment = UserComment::where('id', $request->comment_id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$comment) {
                return $this->errorResponse('Comment not found', 404);
            }

            $result = $this->socialMediaService->deleteComment($comment);
            if (!$result) {
                return $this->errorResponse('Failed to delete comment', 500);
            }

            return $this->successResponse(null, 'Comment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete comment', 500, $e->getMessage());
        }
    }
}
