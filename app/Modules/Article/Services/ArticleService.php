<?php

namespace Modules\Article\Services;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;
use Modules\Article\ValueObjects\ArticleTitle;
use Modules\Article\ValueObjects\ArticleText;

class ArticleService
{
    public function __construct(private ArticleRepositoryInterface $repository){}

    public function getArticleById(Id $id): ?Article
    {
        return $this->repository->get($id);
    }

    public function getAll(): LengthAwarePaginator
    {
        return $this->repository->getAll();
    }

    public function create(array $data): string
    {
        $article = new Article(
            status: $data['status'],
        );

        $articleId = $this->repository->save($article);
        foreach ($data['translations'] as $locale => $translation) {

            $translation = new ArticleTranslation(
                articleId: new Id($articleId),
                locale: $locale,
                title: new ArticleTitle($translation['title']),
                text: new ArticleText($translation['text']),
            );

            $this->repository->saveTranslation($translation);
        }
        $this->syncTags(new Id($articleId), $data['tags']);
        return $articleId;
    }

    public function syncTags(Id $articleId, array $tagIds): void
    {
        $this->repository->syncTags(
            new Id($articleId),
            $tagIds
        );
    }

    public function update(Id $id, array $data): void
    {
        $article = $this->repository->get($id);

        $article = new Article(
            id: $article->id,
//            title: array_key_exists('title', $data) ? new ArticleTitle($data['title']) : $article->title,
//            text: array_key_exists('text', $data) ? new ArticleText($data['text']) : $article->text,
            status: array_key_exists('status', $data) ? $data['status'] : $article->status,
            created_at: $article->created_at,
        );

        $this->repository->save($article);

        foreach ($data['translations'] as $locale => $translation) {

            $translation = new ArticleTranslation(
                articleId: $article->id,
                locale: $locale,
                title: new ArticleTitle($translation['title']),
                text: new ArticleText($translation['text']),
            );

            $this->repository->saveTranslation($translation);
        }
        $this->syncTags($article->id, $data['tags']);
    }

    public function delete(Id $id): void
    {
        $this->repository->delete($id);
    }
}
