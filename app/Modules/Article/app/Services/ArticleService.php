<?php

namespace Modules\Artice\app\Services;

use App\Entity\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\ValueObjects\ArticleId;
use App\ValueObjects\ArticleText;
use App\ValueObjects\ArticleTitle;
use Illuminate\Database\Eloquent\Collection;

class ArticleService
{
    public function __construct(
        private ArticleRepositoryInterface $repository
    ) {}


    public function getArticleById(ArticleId $id): ?Article
    {
        return $this->repository->get($id);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }
    public function create(array $data): void
    {
        $article = new Article(
            title: new ArticleTitle($data['title']),
            text: new ArticleText($data['text']),
        );

        $this->repository->save($article);
    }

    public function update(ArticleId $id, array $data): void
    {
        $article = $this->repository->get($id);

        $article = new Article(
            id: $article->id,
            title: array_key_exists('title', $data) ? new ArticleTitle($data['title']) : $article->title,
            text: array_key_exists('text', $data) ? new ArticleText($data['text']) : $article->text,
            created_at: $article->created_at,
        );

        $this->repository->save($article);
    }
}
