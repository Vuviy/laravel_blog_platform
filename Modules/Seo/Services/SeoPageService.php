<?php
declare(strict_types=1);

namespace Modules\Seo\Services;

use App\ValueObjects\Id;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Modules\Seo\Entities\SeoPage;
use Modules\Seo\Entities\SeoPageTranslation;
use Modules\Seo\Repositories\Contracts\SeoPageRepositoryInterface;

class SeoPageService
{
    public function __construct(private SeoPageRepositoryInterface $repository)
    {
    }

    public function getSeoPageById(Id $id): ?SeoPage
    {
        return $this->repository->get($id);
    }

    public function getAll(): LengthAwarePaginator
    {
        return $this->repository->getAll();
    }

    public function create(array $data): string
    {
        $seoPage = new SeoPage(
            url: $data['url'],
        );

        $seoPageId = $this->repository->save($seoPage);
        foreach ($data['translations'] as $locale => $translation) {

            $seoOgImage = null;

            if (isset($translation['seo_og_image']) && $translation['seo_og_image'] instanceof \Illuminate\Http\UploadedFile) {
                $seoOgImage = $translation['seo_og_image']->store("seo_pages/seo/{$locale}", 'public');
            }

            $translation = new SeoPageTranslation(
                seoPageId: new Id($seoPageId),
                locale: $locale,
                seoTitle: $translation['seo_title'],
                seoDescription: $translation['seo_description'],
                seoKeywords: $translation['seo_keywords'],
                seoOgImage: $seoOgImage,
            );

            $this->repository->saveTranslation($translation);
        }

        return $seoPageId;
    }

    public function update(Id $id, array $data): void
    {
        $seoPage = $this->repository->get($id);

        $seoPage = new SeoPage(
            id: $seoPage->id,
            url: array_key_exists('url', $data) ? $data['url'] : $seoPage->url,
            created_at: $seoPage->created_at,
        );

        $this->repository->save($seoPage);

        foreach ($data['translations'] as $locale => $translation) {
            $existing = $this->repository->getTranslationsForSeoPages($id->getValue());
            $existingTranslation = $existing[$locale] ?? null;

            if (array_key_exists('seo_og_image', $translation) && $translation['seo_og_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($existingTranslation?->seoOgImage) {
                    Storage::disk('public')->delete($existingTranslation->seoOgImage);
                }
                $seoOgImage = $translation['seo_og_image']->store("seo_pages/seo/{$locale}", 'public');
            } else {
                $seoOgImage = $existingTranslation?->seoOgImage;
            }

            $translation = new SeoPageTranslation(
                seoPageId: $seoPage->id,
                locale: $locale,
                seoTitle: $translation['seo_title'],
                seoDescription: $translation['seo_description'],
                seoKeywords: $translation['seo_keywords'],
                seoOgImage: $seoOgImage,
            );

            $this->repository->saveTranslation($translation);
        }
    }

    public function delete(Id $id): void
    {
        $this->repository->delete($id);
    }
}
