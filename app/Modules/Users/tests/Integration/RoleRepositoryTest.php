<?php
declare(strict_types=1);

namespace Modules\Users\tests\Integration;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Users\Entities\Role;
use Modules\Users\Enums\Permission;
use Modules\Users\Repositories\RoleRepository;
use Modules\Users\ValueObjects\RoleName;
use Symfony\Component\Uid\UuidV7;
use Tests\TestCase;

class RoleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private RoleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(RoleRepository::class);
    }

    private function insertRole(string $id, string $name = 'admin'): void
    {
        DB::table('roles')->insert([
            'id'         => $id,
            'name'       => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeRole(?Id $id = null, string $name = 'admin'): Role
    {
        return new Role(
            id: $id,
            name: new RoleName($name),
        );
    }

    public function testSaveCreatesNewRoleAndReturnsId(): void
    {
        $role = $this->makeRole();
        $id = $this->repository->save($role);

        $this->assertNotEmpty($id);
        $this->assertDatabaseHas('roles', ['id' => $id]);
    }

    public function testSaveStoresCorrectName(): void
    {
        $role = $this->makeRole(name: 'editor');
        $id = $this->repository->save($role);

        $this->assertDatabaseHas('roles', [
            'id'   => $id,
            'name' => 'editor',
        ]);
    }

    public function testSaveUpdatesExistingRole(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertRole($uuid, 'editor');

        $updated = $this->makeRole(new Id($uuid), 'admin');
        $this->repository->save($updated);

        $this->assertDatabaseHas('roles', [
            'id'   => $uuid,
            'name' => 'admin',
        ]);
    }

    public function testGetByIdReturnsRole(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertRole($uuid);

        $role = $this->repository->getById(new Id($uuid));

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($uuid, $role->id->getValue());
        $this->assertEquals('admin', $role->name->getValue());
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $role = $this->repository->getById(new Id('non-existent-uuid'));

        $this->assertNull($role);
    }

    public function testGetByIdReturnsRoleWithPermissions(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertRole($uuid);

        DB::table('permission_role')->insert([
            'role_id'    => $uuid,
            'permission' => Permission::USER_CREATE->value,
        ]);

        $role = $this->repository->getById(new Id($uuid));

        $this->assertNotEmpty($role->permissions);
        $this->assertContains(Permission::USER_CREATE, $role->permissions);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $this->insertRole((string) new UuidV7(), 'admin');
        $this->insertRole((string) new UuidV7(), 'editor');

        $paginator = $this->repository->getAll();

        $this->assertEquals(2, $paginator->total());
        $this->assertInstanceOf(Role::class, $paginator->items()[0]);
    }

    public function testGetAllReturnsRolesWithPermissions(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertRole($uuid);

        DB::table('permission_role')->insert([
            'role_id'    => $uuid,
            'permission' => Permission::USER_CREATE->value,
        ]);

        $paginator = $this->repository->getAll();
        $role = $paginator->items()[0];

        $this->assertContains(Permission::USER_CREATE, $role->permissions);
    }

    public function testGetAllListReturnsCollection(): void
    {
        $this->insertRole((string) new UuidV7(), 'admin');
        $this->insertRole((string) new UuidV7(), 'editor');

        $collection = $this->repository->getAllList();

        $this->assertCount(2, $collection);
        $this->assertInstanceOf(Role::class, $collection->first());
    }

    public function testGetAllListReturnsRolesSortedByName(): void
    {
        $this->insertRole((string) new UuidV7(), 'editor');
        $this->insertRole((string) new UuidV7(), 'admin');

        $collection = $this->repository->getAllList();

        $this->assertEquals('admin', $collection->first()->name->getValue());
        $this->assertEquals('editor', $collection->last()->name->getValue());
    }

    public function testDeleteRemovesRole(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertRole($uuid);

        $this->repository->delete(new Id($uuid));

        $this->assertDatabaseMissing('roles', ['id' => $uuid]);
    }

    public function testSyncPermissionsInsertsPermissions(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertRole($uuid);

        $this->repository->syncPermissions(
            new Id($uuid),
            [Permission::USER_CREATE->value]
        );

        $this->assertDatabaseHas('permission_role', [
            'role_id'    => $uuid,
            'permission' => Permission::USER_CREATE->value,
        ]);
    }

    public function testSyncPermissionsClearsExistingBeforeInserting(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertRole($uuid);

        $this->repository->syncPermissions(
            new Id($uuid),
            [Permission::USER_CREATE->value]
        );

        $this->repository->syncPermissions(
            new Id($uuid),
            [Permission::USER_DELETE->value]
        );

        $this->assertDatabaseMissing('permission_role', [
            'permission' => Permission::USER_CREATE->value,
        ]);

        $this->assertDatabaseHas('permission_role', [
            'permission' => Permission::USER_DELETE->value,
        ]);
    }

    public function testSyncPermissionsWithEmptyArrayDeletesAll(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertRole($uuid);

        $this->repository->syncPermissions(
            new Id($uuid),
            [Permission::USER_UPDATE->value]
        );

        $this->repository->syncPermissions(new Id($uuid), []);

        $this->assertDatabaseMissing('permission_role', ['role_id' => $uuid]);
    }

    public function testGetUserRoleIdReturnsIdWhenUserRoleExists(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertRole($uuid, 'user');

        $result = $this->repository->getUserRoleId();

        $this->assertCount(1, $result);
        $this->assertEquals($uuid, $result[0]);
    }

    public function testGetUserRoleIdReturnsEmptyArrayWhenNotFound(): void
    {
        $result = $this->repository->getUserRoleId();

        $this->assertEmpty($result);
    }
}
