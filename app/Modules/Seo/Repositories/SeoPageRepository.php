<?php

declare(strict_types=1);

namespace Modules\Seo\Repositories;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Seo\Entities\Contracts\SeoPageInterface;
use Modules\Seo\Entities\SeoPage;
use Modules\Seo\Entities\SeoPageTranslation;
use Modules\Seo\Repositories\Contracts\SeoPageRepositoryInterface;
use Symfony\Component\Uid\UuidV7;

class SeoPageRepository implements SeoPageRepositoryInterface
{
    private const TABLE_NAME = 'seo_pages';
    private const TRANSLATIONS_TABLE = 'seo_pages_translations';


    public function get(Id $seoPageId): ?SeoPage
    {
        $seoPage = DB::table(self::TABLE_NAME)->find($seoPageId);
        if (null === $seoPage) {
            return null;
        }

        return new SeoPage(
            id: new Id($seoPage->id),
            url: $seoPage->url,
            created_at: new \DateTimeImmutable($seoPage->created_at),
            updated_at: new \DateTimeImmutable($seoPage->updated_at),
            translations: $this->getTranslationsForSeoPages($seoPage->id),
        );
    }

    public function getByUrl(string $url): ?SeoPageInterface
    {
        $seoPage = DB::table(self::TABLE_NAME)->where('url', '=', $url)->first();
        if (null === $seoPage) {
            return null;
        }

        return new SeoPage(
            id: new Id($seoPage->id),
            url: $seoPage->url,
            created_at: new \DateTimeImmutable($seoPage->created_at),
            updated_at: new \DateTimeImmutable($seoPage->updated_at),
            translations: $this->getTranslationsForSeoPages($seoPage->id),
        );
    }


    public function save(SeoPage $seoPage): string
    {
        if ($seoPage->id === null) {
            $id = $this->nextId();
            DB::table(self::TABLE_NAME)->insert([
                'id' => $id,
                'url' => $seoPage->url,
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return (string)$id;
        }

        DB::table(self::TABLE_NAME)->where('id', $seoPage->id->getValue())->update([
            'url' => $seoPage->url,
            'updated_at' => new \DateTimeImmutable(),
        ]);
        return $seoPage->id->getValue();

    }

    public function delete(Id $id): void
    {
        DB::table(self::TABLE_NAME)->delete($id);
    }

    public function getAll(): LengthAwarePaginator
    {
        $paginator = DB::table(self::TABLE_NAME)->orderBy('created_at')->paginate(10);
        $collection = new Collection();
        foreach ($paginator->items() as $seoPage) {
            $articleEntity = new SeoPage(
                id: new Id($seoPage->id),
                url: $seoPage->url,
                created_at: new \DateTimeImmutable($seoPage->created_at),
                updated_at: new \DateTimeImmutable($seoPage->updated_at),
                translations: $this->getTranslationsForSeoPages($seoPage->id),
            );
            $collection->push($articleEntity);
        }

        $paginator->setCollection($collection);

        return $paginator;
    }

    public function getTranslationsForSeoPages(string $seoPageId): array
    {
        if (empty($seoPageId)) return [];

        $rows = DB::table(self::TRANSLATIONS_TABLE)
            ->where('seo_page_id', $seoPageId)
            ->get();

        $translations = [];
        foreach ($rows as $row) {
            $translations[$row->locale] = new SeoPageTranslation(
                id: new Id($row->id),
                seoPageId: new Id($row->seo_page_id),
                locale: $row->locale,
                seoTitle: $row->seo_title,
                seoDescription: $row->seo_description,
                seoKeywords: $row->seo_keywords,
                seoOgImage: $row->seo_og_image,
            );
        }
        return $translations;
    }

    public function saveTranslation(SeoPageTranslation $translation): void
    {
        DB::table(self::TRANSLATIONS_TABLE)
            ->updateOrInsert(
                [
                    'seo_page_id' => $translation->seoPageId->getValue(),
                    'locale' => $translation->locale,
                ],
                [
                    'id' => $translation->id?->getValue() ?? (string) $this->nextId(),
                    'seo_title' => $translation->seoTitle,
                    'seo_description' => $translation->seoDescription,
                    'seo_keywords' => $translation->seoKeywords,
                    'seo_og_image' => $translation->seoOgImage,
                    'updated_at' => new \DateTimeImmutable(),
                    'created_at' => new \DateTimeImmutable(),
                ]
            );
    }

    public function nextId(): Id
    {
        return new Id((string)new UuidV7());
    }
}
