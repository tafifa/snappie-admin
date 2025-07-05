<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    /**
     * Create a new check-in
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'place_id' => 'required|exists:places,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
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
                    'message' => 'Place is not available for check-in',
                    'error_code' => 'PLACE_INACTIVE'
                ], 400);
            }

            // Calculate distance between user location and place
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $place->latitude,
                $place->longitude
            );

            // Check if user is within acceptable range (100 meters)
            if ($distance > 0.1) { // 0.1 km = 100 meters
                return response()->json([
                    'success' => false,
                    'message' => 'You are too far from the place to check-in',
                    'error_code' => 'LOCATION_TOO_FAR',
                    'data' => [
                        'distance' => round($distance * 1000, 0), // distance in meters
                        'max_allowed' => 100 // meters
                    ]
                ], 400);
            }

            // Check if user already checked in today
            $existingCheckin = Checkin::where('user_id', $user->id)
                                    ->where('place_id', $place->id)
                                    ->whereDate('created_at', today())
                                    ->first();

            if ($existingCheckin) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already checked in to this place today',
                    'error_code' => 'ALREADY_CHECKED_IN_TODAY',
                    'data' => [
                        'existing_checkin' => [
                            'id' => $existingCheckin->id,
                            'created_at' => $existingCheckin->created_at->toISOString()
                        ]
                    ]
                ], 400);
            }

            DB::beginTransaction();

            // Create check-in
            $checkin = Checkin::create([
                'user_id' => $user->id,
                'place_id' => $place->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'checkin_status' => 'approved',
                'mission_status' => 'pending',
            ]);

            // Award base points for check-in
            $baseExp = 10;
            $baseCoin = 5;

            $user->increment('total_exp', $baseExp);
            $user->increment('total_coin', $baseCoin);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful',
                'data' => [
                    'id' => $checkin->id,
                    'place' => [
                        'id' => $place->id,
                        'name' => $place->name,
                        'category' => $place->category,
                        'address' => $place->address,
                    ],
                    'checkin_status' => $checkin->checkin_status,
                    'mission_status' => $checkin->mission_status,
                    'latitude' => (float) $checkin->latitude,
                    'longitude' => (float) $checkin->longitude,
                    'distance' => round($distance * 1000, 0), // meters
                    'rewards' => [
                        'base_exp' => $baseExp,
                        'base_coin' => $baseCoin,
                    ],
                    'mission_available' => !empty($place->clue_mission),
                    'user_stats' => [
                        'total_exp' => $user->fresh()->total_exp,
                        'total_coin' => $user->fresh()->total_coin,
                        'level' => $user->fresh()->level,
                    ],
                    'created_at' => $checkin->created_at->toISOString(),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Check-in failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get check-in history for user
     */
    public function history(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:50',
                'status' => 'sometimes|string|in:pending,approved,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $perPage = $request->get('per_page', 20);

            $query = Checkin::with(['place:id,name,category,address,image_urls'])
                           ->where('user_id', $user->id);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('checkin_status', $request->status);
            }

            $checkins = $query->orderBy('created_at', 'desc')
                            ->paginate($perPage);

            $data = $checkins->through(function ($checkin) {
                // Calculate rewards earned
                $expEarned = 10; // base exp
                $coinEarned = 5; // base coin

                if ($checkin->mission_status === 'completed') {
                    $expEarned += $checkin->place->exp_reward ?? 0;
                    $coinEarned += $checkin->place->coin_reward ?? 0;
                }

                return [
                    'id' => $checkin->id,
                    'place' => [
                        'id' => $checkin->place->id,
                        'name' => $checkin->place->name,
                        'category' => $checkin->place->category,
                        'address' => $checkin->place->address,
                        'image_urls' => $checkin->place->image_urls,
                    ],
                    'checkin_status' => $checkin->checkin_status,
                    'mission_status' => $checkin->mission_status,
                    'latitude' => (float) $checkin->latitude,
                    'longitude' => (float) $checkin->longitude,
                    'mission_image_url' => $checkin->mission_image_url,
                    'rewards_earned' => [
                        'exp' => $expEarned,
                        'coin' => $coinEarned,
                    ],
                    'created_at' => $checkin->created_at->toISOString(),
                    'updated_at' => $checkin->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Check-in history retrieved successfully',
                'data' => $data,
                'pagination' => [
                    'current_page' => $checkins->currentPage(),
                    'per_page' => $checkins->perPage(),
                    'total' => $checkins->total(),
                    'total_pages' => $checkins->lastPage(),
                    'has_next_page' => $checkins->hasMorePages(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve check-in history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c; // Distance in km
    }
}
