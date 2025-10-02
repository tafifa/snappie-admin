<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceRequest;
use App\Models\Place;
use App\Services\PlaceService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PlaceController extends Controller
{
    use ApiResponseTrait;

    protected PlaceService $placeService;

    public function __construct(PlaceService $placeService)
    {
        $this->placeService = $placeService;
    }

    /**
     * Get paginated places with optional filters
     *
     * @param PlaceRequest $request
     * @return JsonResponse
     */
    public function index(PlaceRequest $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);

            // Handle different filtering options
            if ($request->has('search')) {
                $places = $this->placeService->searchByName($request->search, $perPage);
            } elseif ($request->has('min_rating')) {
                $places = $this->placeService->getByRating($request->min_rating, $perPage);
            } elseif ($request->has('min_price') && $request->has('max_price')) {
                $places = $this->placeService->getByPriceRange(
                    $request->min_price,
                    $request->max_price,
                    $perPage
                );
            } elseif ($request->has('latitude') && $request->has('longitude')) {
                $radius = $request->get('radius', 5.0);
                $places = $this->placeService->findNearby(
                    $request->latitude,
                    $request->longitude,
                    $radius,
                    $perPage
                );
            } elseif ($request->boolean('popular')) {
                $places = $this->placeService->getMostPopular($perPage);
            } elseif ($request->boolean('partner')) {
                $places = $this->placeService->getPartnerPlaces($perPage);
            } elseif ($request->boolean('active_only')) {
                $places = $this->placeService->getActive($perPage);
            } elseif ($request->has('food_type') || $request->has('place_value')) {
                $places = $this->placeService->getByUserPreferences(
                    $request->input('food_type', []),
                    $request->input('place_value', [])
                );
            } else {
                $places = $this->placeService->getPaginated($perPage);
            }

            return $this->successResponse($places, 'Places retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve places', 500, $e->getMessage());
        }
    }

    /**
     * Get a specific place by ID
     *
     * @param int $place_id
     * @return JsonResponse
     */
    public function show(int $place_id): JsonResponse
    {
        try {
            $place = $this->placeService->getById($place_id);

            if (!$place) {
                return $this->errorResponse('Place not found', 404);
            }

            return $this->successResponse($place, 'Place retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve place', 500, $e->getMessage());
        }
    }

    /**
     * Get place reviews
     *
     * @param int $place_id
     * @return JsonResponse
     */
    public function getPlaceReviews(int $place_id): JsonResponse
    {
        try {
            $place = $this->placeService->getById($place_id);

            if (!$place) {
                return $this->errorResponse('Place not found', 404);
            }

            $reviews = $this->placeService->getPlaceReviews($place->id);

            return $this->successResponse($reviews, 'Place reviews retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve place reviews', 500, $e->getMessage());
        }
    }
}
