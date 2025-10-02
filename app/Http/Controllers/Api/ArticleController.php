<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    use ApiResponseTrait;

    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * Get paginated articles with optional filters
     *
     * @param ArticleRequest $request
     * @return JsonResponse
     */
    public function index(ArticleRequest $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);

            // Handle different filtering options
            if ($request->has('search')) {
                $articles = $this->articleService->searchByTitle($request->search, $perPage);
            } elseif ($request->has('category')) {
                $articles = $this->articleService->getByCategory($request->category, $perPage);
            } elseif ($request->has('author_id')) {
                $articles = $this->articleService->getByAuthor($request->author_id, $perPage);
            } else {
                $articles = $this->articleService->getPaginated($perPage);
            }

            return $this->successResponse($articles, 'Articles retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve articles', 500, $e->getMessage());
        }
    }

    /**
     * Get a specific article by ID
     *
     * @param int $article_id
     * @return JsonResponse
     */
    public function show(int $article_id): JsonResponse
    {
        try {
            // Gunakan route parameter jika ada, atau ambil dari request
            $articleId = $article_id;
            
            $article = $this->articleService->getById($articleId);

            if (!$article) {
                return $this->errorResponse('Article not found', 404);
            }

            return $this->successResponse($article, 'Article retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve article', 500, $e->getMessage());
        }
    }
}