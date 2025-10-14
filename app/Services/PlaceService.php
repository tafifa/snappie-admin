<?php

namespace App\Services;

use App\Models\Place;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PlaceService
{
  /**
   * Get paginated places with reviews and checkins count
   *
   * @param int $perPage Number of places per page
   * @return LengthAwarePaginator
   */
  public function getPaginated(int $perPage = 10): LengthAwarePaginator
  {
    return Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }

  /**
   * Search places by name
   *
   * @param string $name Search term
   * @param int $perPage Number of places per page
   * @return LengthAwarePaginator
   */
  public function searchByName(string $name, int $perPage = 10): LengthAwarePaginator
  {
    if (empty(trim($name))) {
      return new LengthAwarePaginator([], 0, $perPage);
    }

    return Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->where('name', 'LIKE', "%{$name}%")
      ->orderBy('name')
      ->paginate($perPage);
  }

  /**
   * Get places by minimum rating
   *
   * @param float $minRating Minimum rating (0-5)
   * @param int $perPage Number of places per page
   * @return LengthAwarePaginator
   */
  public function getByRating(float $minRating = 4.0, int $perPage = 10): LengthAwarePaginator
  {
    if ($minRating < 0 || $minRating > 5) {
      return new LengthAwarePaginator([], 0, $perPage);
    }

    return Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->where('avg_rating', '>=', $minRating)
      ->orderBy('avg_rating', 'desc')
      ->paginate($perPage);
  }

  /**
   * Get places by price range
   *
   * @param int $minPrice Minimum price
   * @param int $maxPrice Maximum price
   * @param int $perPage Number of places per page
   * @return LengthAwarePaginator
   */
  public function getByPriceRange(int $minPrice, int $maxPrice, int $perPage = 10): LengthAwarePaginator
  {
    if ($minPrice < 0 || $maxPrice < 0 || $minPrice > $maxPrice) {
      return new LengthAwarePaginator([], 0, $perPage);
    }

    return Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->whereBetween('price_range', [$minPrice, $maxPrice])
      ->orderBy('price_range')
      ->paginate($perPage);
  }

  /**
   * Find places nearby a location
   *
   * @param float $latitude Latitude coordinate
   * @param float $longitude Longitude coordinate
   * @param float $radius Search radius in kilometers
   * @param int $perPage Number of places per page
   * @return LengthAwarePaginator
   */
  public function findNearby(float $latitude, float $longitude, float $radius = 5.0, int $perPage = 10): LengthAwarePaginator
  {
    if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 || $radius <= 0) {
      return new LengthAwarePaginator([], 0, $perPage);
    }

    $distanceFormula = "
      (
        6371 * acos(
          cos(radians(?)) * 
          cos(radians(latitude)) * 
          cos(radians(longitude) - radians(?)) + 
          sin(radians(?)) * 
          sin(radians(latitude))
        )
      )
    ";

    return Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->selectRaw("*, {$distanceFormula} AS distance", [$latitude, $longitude, $latitude])
      ->whereNotNull('latitude')
      ->whereNotNull('longitude')
      ->whereRaw("{$distanceFormula} <= ?", [$latitude, $longitude, $latitude, $radius])
      ->orderByRaw($distanceFormula, [$latitude, $longitude, $latitude])
      ->paginate($perPage);
  }

  public function getByUserPreferences(array $foodType = [], array $placeValue = []): LengthAwarePaginator
  {
    $query = Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->orderBy('created_at', 'desc');

    if (!empty($foodType)) {
      $query->whereJsonContains('additional_info->food_type', $foodType);
    }

    if (!empty($placeValue)) {
      $query->whereJsonContains('additional_info->place_value', $placeValue);
    }

    return $query->paginate(10);
  }

  /**
   * Get active partner places
   *
   * @param int $perPage Number of places per page
   * @return LengthAwarePaginator
   */
  public function getPartnerPlaces(int $perPage = 10): LengthAwarePaginator
  {
    return Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->where('partnership_status', 'active')
      ->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }

  /**
   * Get most popular places by checkins count
   *
   * @param int $perPage Number of places per page
   * @return LengthAwarePaginator
   */
  public function getMostPopular(int $perPage = 10): LengthAwarePaginator
  {
    return Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->orderBy('checkins_count', 'desc')
      ->paginate($perPage);
  }

  /**
   * Get active places only
   *
   * @param int $perPage Number of places per page
   * @return LengthAwarePaginator
   */
  public function getActive(int $perPage = 10): LengthAwarePaginator
  {
    return Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->active()
      ->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }

  /**
   * Get place by ID with related data
   *
   * @param int $id Place ID
   * @return Place|null
   */
  public function getById(int $id): ?Place
  {
    return Place::with(['reviews', 'checkins'])
      ->withCount(['reviews', 'checkins'])
      ->find($id);
  }

  /**
   * Get place reviews
   *
   * @param int $placeId Place ID
   * @param int $perPage Number of reviews per page
   * @return LengthAwarePaginator
   */
  public function getPlaceReviews(int $placeId, int $perPage = 10): LengthAwarePaginator
  {
    return Review::with(['user:id,name,email,image_url'])
      ->where('place_id', $placeId)
      ->orderBy('created_at', 'desc')
      // ->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
      ->paginate($perPage);
  }
}
