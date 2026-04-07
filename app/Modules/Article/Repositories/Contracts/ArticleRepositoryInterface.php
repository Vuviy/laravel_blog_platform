<?php

namespace Modules\Article\Repositories\Contracts;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Article\Entities\Article;

interface ArticleRepositoryInterface
{
    public function getAll(): LengthAwarePaginator;
    public function get(Id $articleId): ?Article;
    public function save(Article $article): string;
    public function delete(Id $id): void;

    public function nextId(): Id;
}
