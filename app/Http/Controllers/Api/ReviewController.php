<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    /**
     * Create a new review
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'place_id' => 'required|exists:places,id',
                'vote' => 'required|integer|min:1|max:5',
                'content' => 'required|string|min:10|max:1000',
                'images' => 'sometimes|array|max:5',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048', // Max 2MB per image
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $place = Place::findOrFail($request->place_id);

            // Check if place is active
            if (!$place->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Place is not available for reviews',
                    'error_code' => 'PLACE_INACTIVE'
                ], 400);
            }

            // Check if user has already reviewed this place
            $existingReview = Review::where('user_id', $user->id)
                                  ->where('place_id', $place->id)
                                  ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this place',
                    'error_code' => 'REVIEW_ALREADY_EXISTS',
                    'data' => [
                        'existing_review_id' => $existingReview->id
                    ]
                ], 400);
            }

            $imageUrls = [];

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = 'reviews/' . Str::random(20) . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('public', $filename);
                    $imageUrls[] = '/storage/' . $filename;
                }
            }

            // Create review
            $review = Review::create([
                'user_id' => $user->id,
                'place_id' => $place->id,
                'vote' => $request->vote,
                'content' => $request->content,
                'image_urls' => $imageUrls,
                'status' => 'pending', // Reviews need approval by default
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'data' => [
                    'id' => $review->id,
                    'place' => [
                        'id' => $place->id,
                        'name' => $place->name,
                        'category' => $place->category,
                    ],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'image_url' => $user->image_url,
                    ],
                    'vote' => $review->vote,
                    'content' => $review->content,
                    'image_urls' => $review->image_urls,
                    'status' => $review->status,
                    'created_at' => $review->created_at->toISOString(),
                ]
            ], 201);

        } catch (\Exception $e) {
            // Clean up uploaded images if review creation fails
            if (!empty($imageUrls)) {
                foreach ($imageUrls as $imageUrl) {
                    $path = str_replace('/storage/', '', $imageUrl);
                    Storage::disk('public')->delete($path);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Review submission failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reviews for a place
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'place_id' => 'required|exists:places,id',
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:50',
                'rating' => 'sometimes|integer|min:1|max:5',
                'sort' => 'sometimes|string|in:newest,oldest,highest_rating,lowest_rating',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $place = Place::findOrFail($request->place_id);
            $perPage = $request->get('per_page', 10);

            $query = Review::with(['user:id,name,username,image_url'])
                          ->where('place_id', $place->id)
                          ->where('status', 'approved'); // Only show approved reviews

            // Filter by rating if provided
            if ($request->has('rating')) {
                $query->where('vote', $request->rating);
            }

            // Sorting
            $sort = $request->get('sort', 'newest');
            switch ($sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'highest_rating':
                    $query->orderBy('vote', 'desc')->orderBy('created_at', 'desc');
                    break;
                case 'lowest_rating':
                    $query->orderBy('vote', 'asc')->orderBy('created_at', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $reviews = $query->paginate($perPage);

            $data = $reviews->through(function ($review) {
                return [
                    'id' => $review->id,
                    'user' => [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                        'username' => $review->user->username,
                        'image_url' => $review->user->image_url,
                    ],
                    'vote' => $review->vote,
                    'content' => $review->content,
                    'image_urls' => $review->image_urls,
                    'status' => $review->status,
                    'created_at' => $review->created_at->toISOString(),
                    'updated_at' => $review->updated_at->toISOString(),
                ];
            });

            // Calculate rating statistics
            $allReviews = Review::where('place_id', $place->id)
                              ->where('status', 'approved');

            $ratingStats = [
                'total_reviews' => $allReviews->count(),
                'average_rating' => round($allReviews->avg('vote'), 1),
                'rating_distribution' => [
                    '5' => $allReviews->where('vote', 5)->count(),
                    '4' => $allReviews->where('vote', 4)->count(),
                    '3' => $allReviews->where('vote', 3)->count(),
                    '2' => $allReviews->where('vote', 2)->count(),
                    '1' => $allReviews->where('vote', 1)->count(),
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Reviews retrieved successfully',
                'data' => $data,
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                    'total_pages' => $reviews->lastPage(),
                    'has_next_page' => $reviews->hasMorePages(),
                ],
                'stats' => $ratingStats,
                'place' => [
                    'id' => $place->id,
                    'name' => $place->name,
                    'category' => $place->category,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
