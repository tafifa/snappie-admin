<?php

namespace App\Http\Controllers\Api\V2;

use App\Services\ArticlesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticlesController
{
    public function __construct(private ArticlesService $service) {}
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->query('per_page', 10));
        $filters = [];
        if ($request->query('author')) $filters['author'] = (string) $request->query('author');
        if ($request->query('category')) $filters['category'] = (string) $request->query('category');
        if ($request->query('search')) $filters['search'] = (string) $request->query('search');
        $page = $request->query('page') ? (int) $request->query('page') : null;
        $articles = $this->service->listDetailed($filters, $perPage, $page);
        return response()->json([
            'success' => true,
            'message' => 'Articles retrieved successfully',
            'data' => $articles,
        ]);
    }

    public function show(string $article_id): JsonResponse
    {
        $article = $this->service->detail($article_id);
        if (!$article) {
            return response()->json(['success' => false, 'message' => 'Article not found'], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Article retrieved successfully',
            'data' => $article,
        ]);
    }
}
