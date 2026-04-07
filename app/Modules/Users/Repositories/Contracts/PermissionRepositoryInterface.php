<?php

namespace Modules\Users\Repositories\Contracts;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\Permission;

interface PermissionRepositoryInterface
{
    public function getAll(): LengthAwarePaginator;
    public function getById(Id $id): ?Permission;

    public function save(Permission $permission): string;
    public function delete(Id $id): void;
    public function nextId(): Id;
}
