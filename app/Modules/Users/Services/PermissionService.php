<?php

namespace Modules\Users\Services;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\Permission;
use Modules\Users\Repositories\Contracts\PermissionRepositoryInterface;
use Modules\Users\ValueObjects\PermissionKey;

class PermissionService
{
    public function __construct(
        private PermissionRepositoryInterface $repository
    ) {}


    public function getById(Id $id): ?Permission
    {
        return $this->repository->getById($id);
    }

    public function getAll(): LengthAwarePaginator
    {
        return $this->repository->getAll();
    }
    public function create(array $data): string
    {
        $permission = new Permission(
            key: new PermissionKey($data['key']),
        );

        return $this->repository->save($permission);
    }

    public function update(Id $id, array $data): void
    {
        $permission = $this->repository->getById($id);

        $permission = new Permission(
            id: $permission->id,
            key: new PermissionKey($data['key']),
            createdAt: $permission->createdAt,
        );

        $this->repository->save($permission);
    }

    public function delete(Id $id): void
    {
        $this->repository->delete($id);
    }
}
