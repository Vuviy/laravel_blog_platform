<?php

namespace Modules\Users\Repositories\Contracts;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\Role;

interface RoleRepositoryInterface
{
    public function getAll(): LengthAwarePaginator;
    public function getById(Id $id): ?Role;

    public function save(Role $role): string;
    public function delete(Id $id): void;
    public function nextId(): Id;
}
