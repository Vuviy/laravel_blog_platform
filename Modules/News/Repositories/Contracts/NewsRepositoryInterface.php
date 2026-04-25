<?php
declare(strict_types=1);

namespace Modules\News\Repositories\Contracts;

use App\Contracts\FilterInterface;
use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\News\Entities\News;
use Modules\News\Entities\NewsTranslation;
use Modules\News\Filter\NewsFilter;

interface NewsRepositoryInterface
{
    public function getAll(FilterInterface $filter): LengthAwarePaginator;
    public function get(Id $newId): ?News;
    public function save(News $new): string;
    public function delete(Id $id): void;
    public function getAllPublished(): Collection;
    public function syncTags(Id $newId, array $tagIds): void;
    public function getTranslationsForNews(string $newId): array;
    public function getBySlug(string $slug): ?News;
    public function saveTranslation(NewsTranslation $translation): void;
    public function nextId(): Id;
}
