<?php

namespace App\Http\Controllers\Api\V2;

use App\Services\PlacesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlacesController
{
    public function __construct(private PlacesService $service) {}
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->query('per_page', 10));
        $filters = [];
        if ($request->query('search')) $filters['search'] = (string) $request->query('search');
        if ($request->query('min_rating')) $filters['minRating'] = (float) $request->query('min_rating');
        if ($request->query('min_price') && $request->query('max_price')) {
            $filters['minPrice'] = (int) $request->query('min_price');
            $filters['maxPrice'] = (int) $request->query('max_price');
        }
        if (!is_null($request->query('partner'))) $filters['partnershipStatus'] = $request->query('partner');
        if (!is_null($request->query('active_only'))) $filters['status'] = $request->query('active_only');
        if ($request->query('latitude') && $request->query('longitude') && $request->query('radius')) {
            $filters['latitude'] = (float) $request->query('latitude');
            $filters['longitude'] = (float) $request->query('longitude');
            $filters['radius'] = (float) $request->query('radius');
        }
        if ($request->query('page')) $filters['page'] = (int) $request->query('page');
        $foodType = $request->query('food_type');
        if (is_array($foodType)) {
            if (count($foodType) > 0) $filters['foodType'] = $foodType;
        } elseif ($foodType) {
            $arr = array_filter(explode(',', (string) $foodType));
            if (count($arr) > 0) $filters['foodType'] = $arr;
        }
        $placeValue = $request->query('place_value');
        if (is_array($placeValue)) {
            if (count($placeValue) > 0) $filters['placeValue'] = $placeValue;
        } elseif ($placeValue) {
            $arr = array_filter(explode(',', (string) $placeValue));
            if (count($arr) > 0) $filters['placeValue'] = $arr;
        }
        $places = $this->service->getWithMultipleFilters($filters, $perPage);
        return response()->json([
            'success' => true,
            'message' => 'Places retrieved successfully',
            'data' => $places,
        ]);
    }

    public function show(int $place_id): JsonResponse
    {
        $place = $this->service->detail($place_id);
        if (!$place) {
            return response()->json(['success' => false, 'message' => 'Place not found'], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Place retrieved successfully',
            'data' => $place,
        ]);
    }

    public function reviews(int $place_id, Request $request): JsonResponse
    {
        $filters = [];
        if ($request->query('rating')) $filters['rating'] = (int) $request->query('rating');
        if ($request->query('created_from')) $filters['created_from'] = (string) $request->query('created_from');
        if ($request->query('created_to')) $filters['created_to'] = (string) $request->query('created_to');
        if ($request->query('sort_by')) $filters['sort_by'] = (string) $request->query('sort_by');
        $perPage = (int) ($request->query('per_page', 10));
        $page = $request->query('page') ? (int) $request->query('page') : null;
        $result = $this->service->reviews($place_id, $filters, $perPage, $page);
        return response()->json([
            'success' => true,
            'message' => 'Reviews retrieved successfully',
            'data' => $result,
        ]);
    }
}
