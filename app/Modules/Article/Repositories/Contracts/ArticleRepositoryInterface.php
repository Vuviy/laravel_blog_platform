<?php

namespace Modules\Article\Repositories\Contracts;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Filter\ArticleFilter;

interface ArticleRepositoryInterface
{
    public function getAll(ArticleFilter $filter): LengthAwarePaginator;
    public function get(Id $articleId): ?Article;
    public function save(Article $article): string;
    public function delete(Id $id): void;
    public function syncTags(Id $articleId, array $tagIds): void;
    public function getBySlug(string $slug): ?Article;
    public function getByIds(array $ids): Collection;
    public function saveTranslation(ArticleTranslation $translation): void;
    public function getTranslationsForArticles(string $articleId): array;
    public function getAllPublished(): Collection;
    public function nextId(): Id;
}
