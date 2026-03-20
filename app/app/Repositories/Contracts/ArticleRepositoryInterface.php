<?php

namespace App\Repositories\Contracts;


use App\Entity\Article;
use App\ValueObjects\ArticleId;
use Illuminate\Database\Eloquent\Collection;

interface ArticleRepositoryInterface
{
    public function getAll(): Collection;
    public function get(ArticleId $articleId): ?Article;
    public function save(Article $article): void;
    public function delete(ArticleId $articleId): void;
}
