<?php

namespace Modules\Seo\Repositories\Contracts;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Seo\Entities\Contracts\SeoPageInterface;
use Modules\Seo\Entities\SeoPage;
use Modules\Seo\Entities\SeoPageTranslation;

interface SeoPageRepositoryInterface
{
    public function getAll(): LengthAwarePaginator;
    public function get(Id $seoPageId): ?SeoPage;
    public function save(SeoPage $seoPage): string;
    public function delete(Id $id): void;
    public function nextId(): Id;

    public function getByUrl(string $url): ?SeoPageInterface;

    public function saveTranslation(SeoPageTranslation $translation): void;

    public function getTranslationsForSeoPages(string $seoPageId): array;
}
