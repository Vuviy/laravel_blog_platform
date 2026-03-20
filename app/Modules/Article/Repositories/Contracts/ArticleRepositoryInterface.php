<?php

namespace Modules\Article\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Article\Entities\Article;
use Modules\Article\ValueObjects\ArticleId;

interface ArticleRepositoryInterface
{
    public function getAll(): Collection;
    public function get(ArticleId $articleId): ?Article;
    public function save(Article $article): void;
    public function delete(ArticleId $articleId): void;
}
