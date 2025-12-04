<?php

namespace App\Services;

use App\Models\Place;

class PlacesService
{
    public function detail(string $placeId): ?array
    {
        $place = Place::find($placeId);
        if (!$place) {
            return null;
        }

        // Load reviews untuk place ini
        $place->load([
            "reviews" => function ($query) {
                $query
                    ->approved()
                    ->latest()
                    ->limit(5)
                    ->with(["user:id,name,image_url"]);
            },
        ]);

        return $place->toArray();
    }

    public function getWithMultipleFilters(
        array $filters,
        int $perPage = 10,
    ): array {
        $query = Place::query();

        $search = isset($filters["search"])
            ? trim((string) $filters["search"])
            : null;
        if ($search !== null && $search !== "") {
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%" . $search . "%")->orWhere(
                    "description",
                    "like",
                    "%" . $search . "%",
                );
            });
        }

        if (isset($filters["minRating"])) {
            $minRating = (float) $filters["minRating"];
            $query->where("avg_rating", ">=", $minRating);
        }

        if (isset($filters["minPrice"]) && isset($filters["maxPrice"])) {
            $minPrice = (int) $filters["minPrice"];
            $maxPrice = (int) $filters["maxPrice"];
            $query->where(function ($q) use ($minPrice, $maxPrice) {
                $q->where("min_price", "<=", $maxPrice)->where(
                    "max_price",
                    ">=",
                    $minPrice,
                );
            });
        }

        if (array_key_exists("partnershipStatus", $filters)) {
            $val = $filters["partnershipStatus"];
            $bool = is_bool($val)
                ? $val
                : in_array(
                    strtolower((string) $val),
                    ["1", "true", "yes"],
                    true,
                );
            $query->where("partnership_status", $bool);
        }

        if (array_key_exists("status", $filters)) {
            $val = $filters["status"];
            $bool = is_bool($val)
                ? $val
                : in_array(
                    strtolower((string) $val),
                    ["1", "true", "yes"],
                    true,
                );
            $query->where("status", $bool);
        }

        // Location filter using Haversine formula
        if (
            isset($filters["latitude"]) &&
            isset($filters["longitude"]) &&
            isset($filters["radius"])
        ) {
            $lat = (float) $filters["latitude"];
            $lng = (float) $filters["longitude"];
            $radius = (float) $filters["radius"];

            $query->whereNotNull("latitude")->whereNotNull("longitude");

            $haversine = "(6371 * acos(cos(radians(?))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians(?))
                        + sin(radians(?))
                        * sin(radians(latitude))))";

            $query
                ->selectRaw("*, $haversine AS distance", [$lat, $lng, $lat])
                ->having("distance", "<=", $radius)
                ->orderBy("distance");
        }

        if (
            isset($filters["foodType"]) &&
            is_array($filters["foodType"]) &&
            count($filters["foodType"]) > 0
        ) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters["foodType"] as $ft) {
                    $q->where(function ($qq) use ($ft) {
                        $qq->whereRaw(
                            "JSON_SEARCH(additional_info, 'one', ?, NULL, '$.food_type[*]') IS NOT NULL",
                            [(string) $ft],
                        )->orWhereRaw(
                            "JSON_CONTAINS(JSON_EXTRACT(additional_info, '$.food_type'), JSON_OBJECT('key', ?))",
                            [(string) $ft],
                        );
                    });
                }
            });
        }

        if (
            isset($filters["placeValue"]) &&
            is_array($filters["placeValue"]) &&
            count($filters["placeValue"]) > 0
        ) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters["placeValue"] as $pv) {
                    $q->where(function ($qq) use ($pv) {
                        $qq->whereRaw(
                            "JSON_SEARCH(additional_info, 'one', ?, NULL, '$.place_value[*]') IS NOT NULL",
                            [(string) $pv],
                        )->orWhereRaw(
                            "JSON_CONTAINS(JSON_EXTRACT(additional_info, '$.place_value'), JSON_OBJECT('key', ?))",
                            [(string) $pv],
                        );
                    });
                }
            });
        }

        $page = isset($filters["page"]) ? max(1, (int) $filters["page"]) : null;
        $places = $page
            ? $query->paginate($perPage, ["*"], "page", $page)
            : $query->paginate($perPage);

        // Load reviews untuk setiap place
        $places->load([
            "reviews" => function ($query) {
                $query->approved()->latest()->limit(5);
            },
        ]);

        return [
            "items" => $places->items(),
            "total" => (int) $places->total(),
            "current_page" => (int) $places->currentPage(),
            "per_page" => (int) $places->perPage(),
            "last_page" => (int) $places->lastPage(),
        ];
    }

    public function reviews(
        int $placeId,
        array $filters = [],
        int $perPage = 10,
        ?int $page = null,
    ): array {
        $query = \App\Models\Review::query()
            ->approved()
            ->where("place_id", $placeId)
            ->with(["user:id,name,image_url"]);

        if (isset($filters["rating"])) {
            $query->where("rating", (int) $filters["rating"]);
        }

        $from = $filters["created_from"] ?? null;
        $to = $filters["created_to"] ?? null;
        if ($from && $to) {
            $query->whereBetween("created_at", [$from, $to]);
        } elseif ($from) {
            $query->where("created_at", ">=", $from);
        } elseif ($to) {
            $query->where("created_at", "<=", $to);
        }

        $sort = $filters["sort_by"] ?? "recent";
        if ($sort === "top") {
            $query->orderBy("rating", "desc")->orderBy("created_at", "desc");
        } else {
            $query->orderBy("created_at", "desc");
        }

        $reviews = $page
            ? $query->paginate($perPage, ["*"], "page", (int) $page)
            : $query->paginate($perPage);

        $avg =
            (float) (\App\Models\Review::where("place_id", $placeId)
                ->approved()
                ->avg("rating") ?? 0);
        $total = (int) \App\Models\Review::where("place_id", $placeId)
            ->approved()
            ->count();
        $hist = [];
        for ($i = 1; $i <= 5; $i++) {
            $hist[$i] = (int) \App\Models\Review::where("place_id", $placeId)
                ->approved()
                ->where("rating", $i)
                ->count();
        }

        return [
            "items" => $reviews->items(),
            "total" => (int) $reviews->total(),
            "current_page" => (int) $reviews->currentPage(),
            "per_page" => (int) $reviews->perPage(),
            "last_page" => (int) $reviews->lastPage(),
            "summary" => [
                "average_rating" => $avg,
                "total_reviews" => $total,
                "histogram" => $hist,
            ],
        ];
    }

    public function checkins(
        int $placeId,
        array $filters = [],
        int $perPage = 10,
        ?int $page = null,
    ): array {
        $query = \App\Models\Checkin::query()
            ->where("place_id", $placeId)
            ->where("status", true)
            ->with(["user:id,name,image_url"]);

        $from = $filters["created_from"] ?? null;
        $to = $filters["created_to"] ?? null;
        if ($from && $to) {
            $query->whereBetween("created_at", [$from, $to]);
        } elseif ($from) {
            $query->where("created_at", ">=", $from);
        } elseif ($to) {
            $query->where("created_at", "<=", $to);
        }

        $query->orderBy("created_at", "desc");

        $checkins = $page
            ? $query->paginate($perPage, ["*"], "page", (int) $page)
            : $query->paginate($perPage);

        return [
            "items" => $checkins->items(),
            "total" => (int) $checkins->total(),
            "current_page" => (int) $checkins->currentPage(),
            "per_page" => (int) $checkins->perPage(),
            "last_page" => (int) $checkins->lastPage(),
        ];
    }

    public function posts(
        int $placeId,
        array $filters = [],
        int $perPage = 10,
        ?int $page = null,
    ): array {
        $query = \App\Models\Post::query()
            ->where("place_id", $placeId)
            ->active()
            ->with(["user:id,name,image_url"]);

        $from = $filters["created_from"] ?? null;
        $to = $filters["created_to"] ?? null;
        if ($from && $to) {
            $query->whereBetween("created_at", [$from, $to]);
        } elseif ($from) {
            $query->where("created_at", ">=", $from);
        } elseif ($to) {
            $query->where("created_at", "<=", $to);
        }

        $sort = $filters["sort_by"] ?? "recent";
        if ($sort === "popular") {
            $query
                ->orderBy("total_like", "desc")
                ->orderBy("created_at", "desc");
        } else {
            $query->orderBy("created_at", "desc");
        }

        $posts = $page
            ? $query->paginate($perPage, ["*"], "page", (int) $page)
            : $query->paginate($perPage);

        return [
            "items" => $posts->items(),
            "total" => (int) $posts->total(),
            "current_page" => (int) $posts->currentPage(),
            "per_page" => (int) $posts->perPage(),
            "last_page" => (int) $posts->lastPage(),
        ];
    }
}
