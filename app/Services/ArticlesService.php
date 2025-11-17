<?php

namespace App\Services;

use App\Models\Article;

class ArticlesService
{
    public function list(int $perPage = 10): array
    {
        $articles = Article::query()->paginate($perPage);
        return $articles->items();
    }

    public function detail(string $articleId): ?array
    {
        $article = Article::find($articleId);
        return $article?->toArray();
    }

    public function listDetailed(array $filters = [], int $perPage = 10, ?int $page = null): array
    {
        $query = Article::query();

        if (isset($filters['category'])) {
            $query->where('category', (string) $filters['category']);
        }

        $search = isset($filters['search']) ? trim((string) $filters['search']) : null;
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('author', 'like', '%' . $search . '%');
            });
        }

        $articles = $page ? $query->paginate($perPage, ['*'], 'page', (int) $page) : $query->paginate($perPage);
        return [
            'items' => $articles->items(),
            'total' => (int) $articles->total(),
            'current_page' => (int) $articles->currentPage(),
            'per_page' => (int) $articles->perPage(),
            'last_page' => (int) $articles->lastPage(),
        ];
    }
}
