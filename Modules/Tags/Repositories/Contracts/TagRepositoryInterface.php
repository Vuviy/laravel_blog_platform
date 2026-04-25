<?php

namespace Modules\Tags\Repositories\Contracts;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Tags\Entities\Tag;
interface TagRepositoryInterface
{
    public function getAll(): LengthAwarePaginator;
    public function get(Id $articleId): ?Tag;
    public function save(Tag $article): string;
    public function delete(Id $articleId): void;
    public function getByTagName(string $tagName): ?Tag;
    public function getEntitiesByTagId(Id $tagId): LengthAwarePaginator;
}
