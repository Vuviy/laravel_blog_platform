<?php

namespace Modules\Users\Repositories;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Users\Entities\Role;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\ValueObjects\RoleName;
use Symfony\Component\Uid\UuidV7;

class RoleRepository implements RoleRepositoryInterface
{
    private const TABLE_NAME = 'roles';
    private const PIVOT_TABLE = 'permission_role';


    public function syncPermissions(Id $roleId, array $permissionKeys): void
    {
        DB::table(self::PIVOT_TABLE)
            ->where('role_id', $roleId->getValue())
            ->delete();

        if (empty($permissionKeys)) {
            return;
        }

        $rows = array_map(fn($permissionKey) => [
            'role_id' => $roleId->getValue(),
            'permission' => $permissionKey,
        ], $permissionKeys);

        DB::table(self::PIVOT_TABLE)->insert($rows);
    }

    private function getPermissionsForRole(string $id): array
    {
        return $this->getPermissionsForRoles([$id])[$id] ?? [];
    }

    private function getPermissionsForRoles(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $rows = DB::table(self::PIVOT_TABLE)
            ->whereIn(self::PIVOT_TABLE . '.role_id', $ids)
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->role_id][] = \Modules\Users\Enums\Permission::from($row->permission);
        }
        return $grouped;
    }


    public function getAll(): LengthAwarePaginator
    {
        $paginator = DB::table(self::TABLE_NAME)->orderBy('created_at')->paginate(5);
        $roleIds = array_column($paginator->items(), 'id');
        $permissionsGrouped = $this->getPermissionsForRoles($roleIds);
        $collection = new Collection();

        foreach ($paginator->items() as $role) {
            $roleEntity = new Role(
                id: new Id($role->id),
                name: new RoleName($role->name),
                permissions: $permissionsGrouped[$role->id] ?? [],
                createdAt: new \DateTimeImmutable($role->created_at),
                updatedAt: new \DateTimeImmutable($role->updated_at),
            );
            $collection->push($roleEntity);
        }

        $paginator->setCollection($collection);

        return $paginator;
    }


    public function getUserRoleId(): array
    {
        $row = DB::table(self::TABLE_NAME)->where('name', '=','user')->select('id')->first();

        if(null === $row) {
            return [];
        }
        return [$row->id];
    }

    public function save(Role $role): string
    {
        if ($role->id === null) {
            $id = $this->nextId();
            DB::table(self::TABLE_NAME)->insert([
                'id' => $id,
                'name' => $role->name,
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return (string)$id;
        }

        DB::table(self::TABLE_NAME)->where('id', $role->id->getValue())->update([
            'name' => $role->name,
            'updated_at' => new \DateTimeImmutable(),
        ]);
        return $role->id->getValue();
    }

    public function delete(Id $id): void
    {
        DB::table(self::TABLE_NAME)->delete($id);
    }

    public function nextId(): Id
    {
        return new Id((string)new UuidV7());
    }

    public function getById(Id $id): ?Role
    {
        $role = DB::table(self::TABLE_NAME)->find($id);

        if (null === $role) {
            return null;
        }
        $permissions = $this->getPermissionsForRole($role->id);

        return new Role(
            id: new Id($role->id),
            name: new RoleName($role->name),
            permissions: $permissions,
            createdAt: new \DateTimeImmutable($role->created_at),
            updatedAt: new \DateTimeImmutable($role->updated_at),

        );
    }

    public function getAllList(): Collection
    {
        $collection = new Collection();

        $roles = DB::table('roles')->orderBy('name')->get();

        foreach ($roles as $role) {
            $collection->push(new Role(
                id: new Id($role->id),
                name:  new RoleName($role->name),
                permissions: [],
                createdAt: new \DateTimeImmutable($role->created_at),
                updatedAt: new \DateTimeImmutable($role->updated_at)
            ));
        }

        return $collection;
    }
}
