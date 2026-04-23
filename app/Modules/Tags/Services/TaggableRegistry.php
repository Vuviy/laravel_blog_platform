<?php
declare(strict_types=1);

namespace Modules\Tags\Services;

use Modules\Tags\Repositories\Contracts\TaggableRepositoryInterface;

class TaggableRegistry
{
    private array $repositories = [];

    public function register(TaggableRepositoryInterface $repository): void
    {
        $this->repositories[$repository->getEntityType()] = $repository;
    }

    public function resolve(string $entityType): TaggableRepositoryInterface
    {
        if (!array_key_exists($entityType, $this->repositories)) {
            throw new \InvalidArgumentException(sprintf('Unknown entity type: %s', $entityType));
        }

        return $this->repositories[$entityType];
    }
}
