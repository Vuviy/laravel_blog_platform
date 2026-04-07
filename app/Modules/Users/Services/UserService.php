<?php

namespace Modules\Users\Services;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\Username;

class UserService
{
    public function __construct(private UserRepositoryInterface $repository, private RoleRepositoryInterface $roleRepository)
    {
    }

    public function getById(Id $id): ?User
    {
        return $this->repository->getById($id);
    }

    public function getByEmail(Email $email): ?User
    {
        return $this->repository->getByEmail($email);
    }

    public function getAll(): LengthAwarePaginator
    {
        return $this->repository->getAll();
    }

    public function create(array $data): string
    {
        $user = new User(
            username: new Username($data['username']),
            email: new Email($data['email']),
            password: Password::fromPlain($data['password']),
        );

        $userId = $this->repository->save($user);

        if (!array_key_exists('roles', $data)) {
            $userRoleId = $this->roleRepository->getUserRoleId();
            $this->syncRoles(new Id($userId), $userRoleId);
        } else {
            $this->syncRoles(new Id($userId), $data['roles']);
        }

        return $userId;
    }

    public function syncRoles(Id $userId, array $roleIds): void
    {
        $this->repository->syncRoles(
            $userId,
            $roleIds
        );
    }

    public function update(Id $id, array $data): void
    {
        $user = $this->repository->getById($id);

        $updated = new User(
            id: $user->id,
            username: array_key_exists('username', $data) ? new Username($data['username']) : $user->username,
            email: array_key_exists('email', $data) ? new Email($data['email']) : $user->email,
            password: array_key_exists('password', $data) ? Password::fromPlain($data['password']) : $user->password,
            updatedAt: new \DateTimeImmutable(),
        );

        $this->repository->save($updated);

        if (array_key_exists('roles', $data)) {
            $this->syncRoles($updated->id, $data['roles']);
        }
    }

    public function delete(Id $id): void
    {
        $this->repository->delete($id);
    }
}
