<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleService
{
  private const PAGINATION_LIMIT = 10;

  /**
   * Get all articles with pagination.
   *
   * @param int $perPage
   * @return \Illuminate\Pagination\LengthAwarePaginator
   */
  public function getPaginated(int $perPage = self::PAGINATION_LIMIT): LengthAwarePaginator
  {
    return Article::with([
        'user:id,name,image_url',
        'likes.user:id,name,image_url',
        'comments.user:id,name,image_url'
      ])
      ->withCount(['likes', 'comments'])
      ->latest()
      ->paginate($perPage);
  }

  /**
   * Get an article by ID with relations.
   *
   * @param int $articleId
   * @return Article|null
   */
  public function getById(int $articleId): ?Article
  {
    return Article::where('id', $articleId)
      ->with([
        'user:id,name,image_url',
        'likes.user:id,name,image_url',
        'comments.user:id,name,image_url'
      ])
      ->withCount(['likes', 'comments'])
      ->first();
  }

  /**
   * Search articles by title with pagination.
   *
   * @param string $query
   * @param int $perPage
   * @return \Illuminate\Pagination\LengthAwarePaginator
   */
  public function searchByTitle(string $query, int $perPage = self::PAGINATION_LIMIT): LengthAwarePaginator
  {
    return Article::where('title', 'LIKE', "%{$query}%")
      ->with([
        'user:id,name,image_url',
        'likes.user:id,name,image_url',
        'comments.user:id,name,image_url'
      ])
      ->withCount(['likes', 'comments'])
      ->latest()
      ->paginate($perPage);
  }

  /**
   * Get articles by category with pagination.
   *
   * @param string $category
   * @param int $perPage
   * @return \Illuminate\Pagination\LengthAwarePaginator
   */
  public function getByCategory(string $category, int $perPage = self::PAGINATION_LIMIT): LengthAwarePaginator
  {
    return Article::where('category', $category)
      ->with([
        'user:id,name,image_url',
        'likes.user:id,name,image_url',
        'comments.user:id,name,image_url'
      ])
      ->withCount(['likes', 'comments'])
      ->latest()
      ->paginate($perPage);
  }

  /**
   * Get articles by author with pagination.
   *
   * @param int $authorId
   * @param int $perPage
   * @return \Illuminate\Pagination\LengthAwarePaginator
   */
  public function getByAuthor(int $authorId, int $perPage = self::PAGINATION_LIMIT): LengthAwarePaginator
  {
    return Article::where('user_id', $authorId)
      ->with([
        'user:id,name,image_url',
        'likes.user:id,name,image_url',
        'comments.user:id,name,image_url'
      ])
      ->withCount(['likes', 'comments'])
      ->latest()
      ->paginate($perPage);
  }
}
