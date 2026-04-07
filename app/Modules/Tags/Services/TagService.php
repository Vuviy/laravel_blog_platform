<?php

namespace Modules\Tags\Services;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Tags\Entities\Tag;
use Modules\Tags\Repositories\Contracts\TagRepositoryInterface;
use Modules\Tags\ValueObjects\TagTitle;


class TagService
{
    public function __construct(
        private TagRepositoryInterface $repository
    ) {}


    public function getById(Id $id): ?Tag
    {
        return $this->repository->get($id);
    }

    public function getAll(): LengthAwarePaginator
    {
        return $this->repository->getAll();
    }
    public function create(array $data): string
    {
        $article = new Tag(
            title: new TagTitle($data['title']),
        );

        return $this->repository->save($article);
    }

    public function update(Id $id, array $data): void
    {
        $article = $this->repository->get($id);

        $article = new Tag(
            id: $article->id,
            title: array_key_exists('title', $data) ? new TagTitle($data['title']) : $article->title,
            created_at: $article->created_at,
        );

        $this->repository->save($article);
    }

    public function delete(Id $id): void
    {
        $this->repository->delete($id);
    }
}
