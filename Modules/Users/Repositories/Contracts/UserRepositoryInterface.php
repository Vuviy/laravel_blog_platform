<?php

namespace Modules\Users\Repositories\Contracts;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Article\Entities\Article;
use Modules\Users\Entities\User;
use Modules\Users\ValueObjects\Email;

interface UserRepositoryInterface
{
    public function getAll(): LengthAwarePaginator;
    public function getById(Id $userId): ?User;
    public function getByEmail(Email $email): ?User;
    public function save(User $user): string;
    public function delete(Id $id): void;
    public function nextId(): Id;

    public function syncRoles(Id $userId, array $roleIds): void;
}
