<?php

namespace Modules\Article\Services;

use App\Contracts\FilterInterface;
use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Filter\ArticleFilter;
use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;
use Modules\Article\ValueObjects\ArticleTitle;
use Modules\Article\ValueObjects\ArticleText;

class ArticleService
{
    public function __construct(private ArticleRepositoryInterface $repository)
    {
    }

    public function getArticleById(Id $id): ?Article
    {
        return $this->repository->get($id);
    }

    public function getArticleBySlug(string $slug): ?Article
    {
        return $this->repository->getBySlug($slug);
    }

    public function getAll(FilterInterface $filter): LengthAwarePaginator
    {
        return $this->repository->getAll($filter);
    }

    public function create(array $data): string
    {
        $article = new Article(
            status: $data['status'],
            slug: $data['slug'],
        );

        $articleId = $this->repository->save($article);
        foreach ($data['translations'] as $locale => $translation) {

            $seoOgImage = null;

            if (isset($translation['seo_og_image']) && $translation['seo_og_image'] instanceof \Illuminate\Http\UploadedFile) {
                $seoOgImage = $translation['seo_og_image']->store("articles/seo/{$locale}", 'public');
            }


            $translation = new ArticleTranslation(
                articleId: new Id($articleId),
                locale: $locale,
                title: new ArticleTitle($translation['title']),
                text: new ArticleText($translation['text']),

                seoTitle: $translation['seo_title'],
                seoDescription: $translation['seo_description'],
                seoKeywords: $translation['seo_keywords'],
                seoOgImage: $seoOgImage,
            );

            $this->repository->saveTranslation($translation);
        }
        if (array_key_exists('tags', $data)) {
            $this->syncTags(new Id($articleId), $data['tags']);
        }
        return $articleId;
    }

    public function syncTags(Id $articleId, array $tagIds): void
    {
        $this->repository->syncTags(
            $articleId,
            $tagIds
        );
    }

    public function update(Id $id, array $data): void
    {
        $article = $this->repository->get($id);

        $article = new Article(
            id: $article->id,
            status: array_key_exists('status', $data) ? $data['status'] : $article->status,
            slug: array_key_exists('slug', $data) ? $data['slug'] : $article->slug,
            created_at: $article->created_at,
        );

        $this->repository->save($article);

        foreach ($data['translations'] as $locale => $translation) {
            $existing = $this->repository->getTranslationsForArticles($id->getValue());
            $existingTranslation = $existing[$locale] ?? null;

            if (array_key_exists('seo_og_image', $translation) && $translation['seo_og_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($existingTranslation?->seoOgImage) {
                    Storage::disk('public')->delete($existingTranslation->seoOgImage);
                }
                $seoOgImage = $translation['seo_og_image']->store("articles/seo/{$locale}", 'public');
            } else {
                $seoOgImage = $existingTranslation?->seoOgImage;
            }

            $translation = new ArticleTranslation(
                articleId: $article->id,
                locale: $locale,
                title: new ArticleTitle($translation['title']),
                text: new ArticleText($translation['text']),

                seoTitle: $translation['seo_title'],
                seoDescription: $translation['seo_description'],
                seoKeywords: $translation['seo_keywords'],
                seoOgImage: $seoOgImage,
            );

            $this->repository->saveTranslation($translation);
        }
        if (array_key_exists('tags', $data)) {
            $this->syncTags($article->id, $data['tags']);

        }
    }

    public function delete(Id $id): void
    {
        $this->repository->delete($id);
    }
}
