<?php

namespace Modules\Users\Services;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\Role;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\ValueObjects\RoleName;

class RoleService
{
    public function __construct(
        private RoleRepositoryInterface $repository
    )
    {
    }


    public function getById(Id $id): ?Role
    {
        return $this->repository->getById($id);
    }

    public function getAll(): LengthAwarePaginator
    {
        return $this->repository->getAll();
    }

    public function create(array $data): string
    {
        $role = new Role(
            name: new RoleName($data['name']),
        );

        $roleId = $this->repository->save($role);

        if (array_key_exists('permissions', $data)) {
            $this->syncPermissions(new Id($roleId), $data['permissions']);
        }
        return $roleId;
    }

    public function syncPermissions(Id $roleId, array $permissionKeys): void
    {
        $this->repository->syncPermissions($roleId, $permissionKeys);
    }

    public function update(Id $id, array $data): void
    {
        $role = $this->repository->getById($id);

        $role = new Role(
            id: $role->id,
            name: new RoleName($data['name']),
        );

        $this->repository->save($role);

        $this->syncPermissions($role->id, $data['permissions']);
    }

    public function delete(Id $id): void
    {
        $this->repository->delete($id);
    }

}
