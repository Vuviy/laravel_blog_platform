<?php

namespace Modules\Users\Repositories;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Users\Entities\Permission;
use Modules\Users\Repositories\Contracts\PermissionRepositoryInterface;
use Modules\Users\ValueObjects\PermissionKey;
use Symfony\Component\Uid\UuidV7;

class PermissionRepository implements PermissionRepositoryInterface
{
    private CONST TABLE_NAME = 'permissions';

    public function getAll(): LengthAwarePaginator
    {
        $paginator = DB::table(self::TABLE_NAME)->paginate(10);

        $collection = new Collection();

        foreach ($paginator->items() as $permission) {

            $permissionEntity = new Permission(
                id: new Id($permission->id),
                key: new PermissionKey($permission->key),
                createdAt: new \DateTimeImmutable($permission->created_at),
                updatedAt: new \DateTimeImmutable($permission->updated_at),
            );
            $collection->push($permissionEntity);
        }

        $paginator->setCollection($collection);

        return $paginator;
    }


    public function save(Permission $permission): string
    {
        if ($permission->id === null) {
            $id = $this->nextId();
            DB::table(self::TABLE_NAME)->insert([
                'id' =>  $id,
                'key' => $permission->key->getValue(),
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return (string)$id;
        } else {
            DB::table(self::TABLE_NAME)->where('id', $permission->id->getValue())->update([
                'key' => $permission->key->getValue(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return $permission->id->getValue();
        }
    }

    public function delete(Id $id): void
    {
        DB::table(self::TABLE_NAME)->delete($id);
    }

    public function nextId(): Id
    {
        return new Id((string) new UuidV7());
    }

    public function getById(Id $id): ?Permission
    {
        $permission = DB::table(self::TABLE_NAME)->find($id);

        if(null === $permission) {
            return null;
        }

        return new Permission(
            id: new Id($permission->id),
            key:  new PermissionKey($permission->key),
            createdAt: new \DateTimeImmutable($permission->created_at),
            updatedAt: new \DateTimeImmutable($permission->updated_at)
        );
    }

    public function getAllList(): Collection
    {
        $collection = new Collection();

        $permissions = DB::table('permissions')->orderBy('key')->get();

        foreach ($permissions as $permission) {
            $collection->push(new Permission(
                id: new Id($permission->id),
                key:  new PermissionKey($permission->key),
                createdAt: new \DateTimeImmutable($permission->created_at),
                updatedAt: new \DateTimeImmutable($permission->updated_at)
            ));
        }

        return $collection;
    }

}
