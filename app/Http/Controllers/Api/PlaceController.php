<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PlaceController extends Controller
{
    /**
     * Get list of places with pagination
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'sometimes|string|in:cafe,restaurant,traditional,food_court,street_food',
                'search' => 'sometimes|string|max:255',
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Place::where('status', true);

            // Filter by category
            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            // Search by name or address
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $places = $query->withCount(['checkins', 'reviews'])
                           ->orderBy('name')
                           ->paginate($perPage);

            $data = $places->through(function ($place) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                    'slug' => $place->slug,
                    'category' => $place->category,
                    'description' => $place->description,
                    'address' => $place->address,
                    'latitude' => (float) $place->latitude,
                    'longitude' => (float) $place->longitude,
                    'image_urls' => $place->image_urls,
                    'partnership_status' => $place->partnership_status,
                    'stats' => [
                        'total_checkins' => $place->checkins_count,
                        'total_reviews' => $place->reviews_count,
                        'average_rating' => $place->reviews()->avg('vote') ? round($place->reviews()->avg('vote'), 1) : 0,
                    ],
                    'reward_info' => $place->reward_info,
                    'created_at' => $place->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Places retrieved successfully',
                'data' => $data,
                'pagination' => [
                    'current_page' => $places->currentPage(),
                    'per_page' => $places->perPage(),
                    'total' => $places->total(),
                    'total_pages' => $places->lastPage(),
                    'has_next_page' => $places->hasMorePages(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve places',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get nearby places based on GPS coordinates
     */
    public function nearby(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'sometimes|numeric|min:0.1|max:25', // radius in km
                'category' => 'sometimes|string|in:cafe,restaurant,traditional,food_court,street_food',
                'limit' => 'sometimes|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->get('radius', 5); // Default 5km
            $limit = $request->get('limit', 20);            // Using Haversine formula to calculate distance
            $places = Place::selectRaw("*, 
                    (6371 * acos(cos(radians(?)) 
                    * cos(radians(latitude)) 
                    * cos(radians(longitude) - radians(?)) 
                    + sin(radians(?)) 
                    * sin(radians(latitude)))) AS distance
                ", [$latitude, $longitude, $latitude])
                ->withCount(['checkins', 'reviews'])
                ->where('status', true)
                ->whereRaw("
                    (6371 * acos(cos(radians(?)) 
                    * cos(radians(latitude)) 
                    * cos(radians(longitude) - radians(?)) 
                    + sin(radians(?)) 
                    * sin(radians(latitude)))) <= ?
                ", [$latitude, $longitude, $latitude, $radius]);            // Filter by category if provided
            if ($request->has('category')) {
                $places->where('category', $request->category);
            }

            $places = $places->orderByRaw("
                    (6371 * acos(cos(radians(?)) 
                    * cos(radians(latitude)) 
                    * cos(radians(longitude) - radians(?)) 
                    + sin(radians(?)) 
                    * sin(radians(latitude))))
                ", [$latitude, $longitude, $latitude])
                           ->limit($limit)
                           ->get();

            $data = $places->map(function ($place) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                    'slug' => $place->slug,
                    'category' => $place->category,
                    'description' => $place->description,
                    'address' => $place->address,
                    'latitude' => (float) $place->latitude,
                    'longitude' => (float) $place->longitude,
                    'distance' => round($place->distance, 2), // in km
                    'image_urls' => $place->image_urls,
                    'partnership_status' => $place->partnership_status,
                    'stats' => [
                        'total_checkins' => $place->checkins_count,
                        'total_reviews' => $place->reviews_count,
                        'average_rating' => $place->reviews()->avg('vote') ? round($place->reviews()->avg('vote'), 1) : 0,
                    ],
                    'reward_info' => $place->reward_info,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Nearby places retrieved successfully',
                'data' => $data,
                'search_params' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'radius' => $radius,
                    'category' => $request->category,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve nearby places',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get place details by ID
     */
    public function show(Request $request, $id)
    {
        try {
            $place = Place::where('status', true)
                         ->withCount(['checkins', 'reviews'])
                         ->find($id);

            if (!$place) {
                return response()->json([
                    'success' => false,
                    'message' => 'Place not found',
                    'error_code' => 'PLACE_NOT_FOUND'
                ], 404);
            }

            // Get recent reviews with user info
            $recentReviews = $place->reviews()
                                  ->with('user:id,name,username,image_url')
                                  ->orderBy('created_at', 'desc')
                                  ->limit(5)
                                  ->get()                                  ->map(function ($review) {
                                      return [
                                          'id' => $review->id,
                                          'rating' => $review->vote,
                                          'content' => $review->content,
                                          'image_urls' => $review->image_urls,
                                          'user' => [
                                              'id' => $review->user->id,
                                              'name' => $review->user->name,
                                              'username' => $review->user->username,
                                              'image_url' => $review->user->image_url,
                                          ],
                                          'created_at' => $review->created_at->toISOString(),
                                      ];
                                  });

            return response()->json([
                'success' => true,
                'message' => 'Place details retrieved successfully',
                'data' => [
                    'id' => $place->id,
                    'name' => $place->name,
                    'slug' => $place->slug,
                    'category' => $place->category,
                    'description' => $place->description,
                    'address' => $place->address,
                    'latitude' => (float) $place->latitude,
                    'longitude' => (float) $place->longitude,
                    'image_urls' => $place->image_urls,
                    'partnership_status' => $place->partnership_status,
                    'stats' => [
                        'total_checkins' => $place->checkins_count,
                        'total_reviews' => $place->reviews_count,
                        'average_rating' => $place->reviews()->avg('vote') ? round($place->reviews()->avg('vote'), 1) : 0,
                    ],
                    'reward_info' => $place->reward_info,
                    'recent_reviews' => $recentReviews,
                    'created_at' => $place->created_at->toISOString(),
                    'updated_at' => $place->updated_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve place details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available categories
     */
    public function categories()
    {
        try {
            $categories = [
                [
                    'value' => 'cafe',
                    'label' => 'Cafe',
                    'description' => 'Coffee shops and cafes'
                ],
                [
                    'value' => 'restaurant',
                    'label' => 'Restaurant',
                    'description' => 'Fine dining restaurants'
                ],
                [
                    'value' => 'traditional',
                    'label' => 'Traditional Food',
                    'description' => 'Traditional local cuisine'
                ],
                [
                    'value' => 'food_court',
                    'label' => 'Food Court',
                    'description' => 'Food courts and markets'
                ],
                [
                    'value' => 'street_food',
                    'label' => 'Street Food',
                    'description' => 'Street food vendors'
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
