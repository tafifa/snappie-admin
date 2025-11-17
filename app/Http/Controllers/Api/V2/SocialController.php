<?php

namespace App\Http\Controllers\Api\V2;

use App\Services\SocialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialController
{
    public function __construct(private SocialService $service) {}
    public function posts(Request $request): JsonResponse
    {
        try {
            $perPage = (int) ($request->query('per_page', 10));
            $page = (int) $request->query('page', 1);
            if (!is_null($request->query('trending'))) {
                $data = $this->service->getTrendingPosts($perPage, $page);
            } elseif (!is_null($request->query('following'))) {
                $userId = (int) $request->user()->id;
                $data = $this->service->getFollowingPosts($userId, $perPage, $page);
            } else {
                $data = $this->service->getPosts($perPage, $page);
            };

            return response()->json([
                'success' => true,
                'message' => 'Posts retrieved successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve posts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function postDetail(int $post_id): JsonResponse
    {
        try {
            $data = $this->service->getPostDetail($post_id);
            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found',
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Post detail',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve post detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createPost(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $payload = $request->validate([
                'place_id' => 'required|integer',
                'content' => 'required|string|max:5000',
                'image_urls'   => 'nullable|array',
                'image_urls.*' => 'url',
                'additional_info' => 'nullable|array|max:5000',
            ]);

            $post = $this->service->createPost($user, $payload);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data'    => $post,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function follow(Request $request, int $user_id): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $followed = $this->service->followUser($user, $user_id);
            return response()->json([
                'success' => true, 
                'message' => ($followed ? 'Follow' : 'Unfollow') . ' successfully',
                'data' => $followed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to follow user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function followData(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'nullable|integer',
            ]);

            $userId = $validated['user_id'] ?? $request->user()->id;
            
            $data = $this->service->getFollowData($userId);
            return response()->json([
                'success' => true, 
                'message' => 'Follow data', 
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve follow data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function likePost(Request $request, int $post_id): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            
            $liked = $this->service->likePost($user->id, $post_id);
            return response()->json([
                'success' => true, 
                'message' => 'Post '.($liked ? 'liked' : 'unliked'),
                'data' => $liked,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to like post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function likes(int $post_id): JsonResponse
    {
        try {
            $data = $this->service->getPostLikes($post_id);
            return response()->json([
                'success' => true, 
                'message' => 'Likes list', 
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve likes list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function commentPost(Request $request, int $post_id): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $payload = $request->validate([
                'comment' => 'required|string|max:255',
            ]);
            
            $data = $this->service->createComment($user->id, $post_id, $payload['comment']);
            return response()->json([
                'success' => true, 
                'message' => 'Comment created',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function comments(string $post_id): JsonResponse
    {
        try {
            $data = $this->service->getPostComments($post_id);
            return response()->json([
                'success' => true, 
                'message' => 'Comments list', 
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve comments list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function likeComment(Request $request, int $comment_id): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            
            $liked = $this->service->likeComment($user->id, $comment_id);
            return response()->json([
                'success' => true, 
                'message' => 'Comment '.($liked ? 'liked' : 'unliked'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to like comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function commentLikes(int $comment_id): JsonResponse
    {
        try {
            $data = $this->service->commentLikes($comment_id);
            return response()->json([
                'success' => true, 
                'message' => 'Comment likes list', 
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve comment likes list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
