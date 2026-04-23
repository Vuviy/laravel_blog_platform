<?php
declare(strict_types=1);

namespace Modules\News\Services;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Modules\News\Entities\News;
use Modules\News\Entities\NewsTranslation;
use Modules\News\Filter\NewsFilter;
use Modules\News\Repositories\Contracts\NewsRepositoryInterface;
use Modules\News\ValueObjects\NewsText;
use Modules\News\ValueObjects\NewsTitle;

class NewsService
{
    public function __construct(private NewsRepositoryInterface $repository)
    {
    }

    public function getNewById(Id $id): ?News
    {
        return $this->repository->get($id);
    }

    public function getNewBySlug(string $slug): ?News
    {
        return $this->repository->getBySlug($slug);
    }

    public function getAll(NewsFilter $filter): LengthAwarePaginator
    {
        return $this->repository->getAll($filter);
    }

    public function create(array $data): string
    {
        $new = new News(
            status: $data['status'],
            slug: $data['slug'],
        );

        $newId = $this->repository->save($new);

        foreach ($data['translations'] as $locale => $translation) {

            $seoOgImage = null;

            if (isset($translation['seo_og_image']) && $translation['seo_og_image'] instanceof \Illuminate\Http\UploadedFile) {
                $seoOgImage = $translation['seo_og_image']->store("news/seo/{$locale}", 'public');
            }

            $translation = new NewsTranslation(
                newId: new Id($newId),
                locale: $locale,
                title: new NewsTitle($translation['title']),
                text: new NewsText($translation['text']),

                seoTitle: $translation['seo_title'],
                seoDescription: $translation['seo_description'],
                seoKeywords: $translation['seo_keywords'],
                seoOgImage: $seoOgImage,
            );

            $this->repository->saveTranslation($translation);
        }
        if (array_key_exists('tags', $data)) {
            $this->syncTags(new Id($newId), $data['tags']);
        }
        return $newId;
    }

    public function syncTags(Id $newsId, array $tagIds): void
    {
        $this->repository->syncTags(
            $newsId,
            $tagIds
        );
    }

    public function update(Id $id, array $data): void
    {
        $new = $this->repository->get($id);
        $new = new News(
            id: $new->id,
            status: array_key_exists('status', $data) ? $data['status'] : $new->status,
            slug: array_key_exists('slug', $data) ? $data['slug'] : $new->slug,
            created_at: $new->created_at,
        );

        $this->repository->save($new);

        foreach ($data['translations'] as $locale => $translation) {

            $existing = $this->repository->getTranslationsForNews($id->getValue());
            $existingTranslation = $existing[$locale] ?? null;

            if (array_key_exists('seo_og_image', $translation) && $translation['seo_og_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($existingTranslation?->seoOgImage) {
                    Storage::disk('public')->delete($existingTranslation->seoOgImage);
                }
                $seoOgImage = $translation['seo_og_image']->store("news/seo/{$locale}", 'public');
            } else {
                $seoOgImage = $existingTranslation?->seoOgImage;
            }


            $translation = new NewsTranslation(
                newId: $new->id,
                locale: $locale,
                title: new NewsTitle($translation['title']),
                text: new NewsText($translation['text']),

                seoTitle: $translation['seo_title'],
                seoDescription: $translation['seo_description'],
                seoKeywords: $translation['seo_keywords'],
                seoOgImage: $seoOgImage,
            );

            $this->repository->saveTranslation($translation);
        }
        if (array_key_exists('tags', $data)) {
            $this->syncTags($new->id, $data['tags']);
        }
    }

    public function delete(Id $id): void
    {
        $this->repository->delete($id);
    }
}
