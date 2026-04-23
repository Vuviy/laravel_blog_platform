<?php

namespace Modules\Users\Repositories\Contracts;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\Role;

interface RoleRepositoryInterface
{
    public function getAll(): LengthAwarePaginator;
    public function getById(Id $id): ?Role;

    public function save(Role $role): string;
    public function delete(Id $id): void;
    public function nextId(): Id;

    public function getUserRoleId(): array;

    public function syncPermissions(Id $roleId, array $permissionKeys): void;

    public function getAllList(): Collection;
}
