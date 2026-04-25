<?php

namespace Modules\Users\Repositories;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Symfony\Component\Uid\UuidV7;

class UserRepository implements UserRepositoryInterface
{
    private const TABLE_NAME = 'users';
    private const ROLE_TABLE = 'user_roles';

    public function syncRoles(Id $userId, array $roleIds): void
    {
        DB::table(self::ROLE_TABLE)
            ->where('user_id', $userId->getValue())
            ->delete();

        if (empty($roleIds)) {
            return;
        }

        $rows = array_map(fn($roleId) => [
            'user_id' => $userId->getValue(),
            'role_id' => $roleId,
        ], $roleIds);

        DB::table(self::ROLE_TABLE)->insert($rows);
    }

    private function getRolesForUser(string $id): array
    {
        return $this->getRolesForUsers([$id])[$id] ?? [];
    }

    private function getRolesForUsers(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $rows = DB::table('roles')
            ->join(self::ROLE_TABLE, 'roles.id', '=', self::ROLE_TABLE . '.role_id')
            ->leftJoin('permission_role', 'roles.id', '=', 'permission_role.role_id')
            ->whereIn(self::ROLE_TABLE . '.user_id', $ids)
            ->select(
                'roles.id as role_id',
                'roles.name as role_name',
                'roles.created_at as role_created_at',
                'roles.updated_at as role_updated_at',
                self::ROLE_TABLE . '.user_id',
                'permission_role.permission as permission'
            )
            ->get();

        $rolesMap = [];
        foreach ($rows as $row) {
            $key = $row->user_id . '_' . $row->role_id;

            if (!array_key_exists($key, $rolesMap)) {
                $rolesMap[$key] = [
                    'user_id' => $row->user_id,
                    'role_id' => $row->role_id,
                    'role_name' => $row->role_name,
                    'created_at' => $row->role_created_at,
                    'updated_at' => $row->role_updated_at,
                    'permissions' => [],
                ];
            }

            if ($row->permission !== null) {
                $rolesMap[$key]['permissions'][] = \Modules\Users\Enums\Permission::from($row->permission);
            }
        }

        $grouped = [];
        foreach ($rolesMap as $entry) {
            $grouped[$entry['user_id']][] = new Role(
                id: new Id($entry['role_id']),
                name: new RoleName($entry['role_name']),
                permissions: $entry['permissions'],
                createdAt: new \DateTimeImmutable($entry['created_at']),
                updatedAt: new \DateTimeImmutable($entry['updated_at']),
            );
        }
        return $grouped;
    }

    public function getAll(): LengthAwarePaginator
    {
        $paginator = DB::table(self::TABLE_NAME)->orderBy('created_at')->paginate(5);
        $userIds = array_column($paginator->items(), 'id');
        $rolesGrouped = $this->getRolesForUsers($userIds);
        $collection = new Collection();

        foreach ($paginator->items() as $user) {
            $userEntity = new User(
                id: new Id($user->id),
                username: new Username($user->username),
                email: new Email($user->email),
                password: Password::fromHash($user->password),
                roles: $rolesGrouped[$user->id] ?? [],
                createdAt: new \DateTimeImmutable($user->created_at),
                updatedAt: new \DateTimeImmutable($user->updated_at),
            );
            $collection->push($userEntity);
        }

        $paginator->setCollection($collection);

        return $paginator;
    }


    public function save(User $user): string
    {
        if ($user->id === null) {
            $id = $this->nextId();
            DB::table(self::TABLE_NAME)->insert([
                'id' => $id,
                'username' => $user->username,
                'email' => $user->email,
                'password' => $user->password,
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return (string)$id;
        }

        DB::table(self::TABLE_NAME)->where('id', $user->id->getValue())->update([
            'username' => $user->username,
            'email' => $user->email,
            'password' => $user->password,
            'updated_at' => new \DateTimeImmutable(),
        ]);
        return $user->id->getValue();
    }

    public function delete(Id $id): void
    {
        DB::table(self::TABLE_NAME)->delete($id);
    }

    public function nextId(): Id
    {
        return new Id((string)new UuidV7());
    }

    public function getById(Id $userId): ?User
    {
        $user = DB::table(self::TABLE_NAME)->find($userId);

        if (null === $user) {
            return null;
        }
        $roles = $this->getRolesForUser($user->id);

        return new User(
            id: new Id($user->id),
            username: new Username($user->username),
            email: new Email($user->email),
            roles: $roles,
            password: Password::fromHash($user->password),
            createdAt: new \DateTimeImmutable($user->created_at),
            updatedAt: new \DateTimeImmutable($user->updated_at),
        );
    }

    public function getByEmail(Email $email): ?User
    {
        $user = DB::table(self::TABLE_NAME)->where('email', $email)->first();

        if (null === $user) {
            return null;
        }
        $roles = $this->getRolesForUser($user->id);

        return new User(
            id: new Id($user->id),
            username: new Username($user->username),
            email: new Email($user->email),
            roles: $roles,
            password: Password::fromHash($user->password),
            createdAt: new \DateTimeImmutable($user->created_at),
            updatedAt: new \DateTimeImmutable($user->updated_at),
        );
    }
}
