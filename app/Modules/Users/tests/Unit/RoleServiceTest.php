<?php
declare(strict_types=1);

namespace Modules\Users\tests\Unit;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\Role;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\Services\RoleService;
use Modules\Users\ValueObjects\RoleName;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    private RoleRepositoryInterface $repository;
    private RoleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(RoleRepositoryInterface::class);
        $this->service = new RoleService($this->repository);
    }


    public function testGetByIdReturnsRole(): void
    {
        $id = new Id('some-uuid');
        $role = new Role(id: $id, name: new RoleName('admin'));

        $this->repository
            ->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($role);

        $result = $this->service->getById($id);

        $this->assertSame($role, $result);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $id = new Id('non-existent-uuid');

        $this->repository
            ->method('getById')
            ->willReturn(null);

        $result = $this->service->getById($id);

        $this->assertNull($result);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($paginator);

        $result = $this->service->getAll();

        $this->assertSame($paginator, $result);
    }


    public function testCreateSavesRoleAndReturnsId(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturn('new-role-uuid');

        $result = $this->service->create(['name' => 'editor']);

        $this->assertEquals('new-role-uuid', $result);
    }

    public function testCreateSyncsPermissionsWhenProvided(): void
    {
        $permissions = ['articles.create', 'articles.delete'];

        $this->repository
            ->method('save')
            ->willReturn('new-role-uuid');

        $this->repository
            ->expects($this->once())
            ->method('syncPermissions')
            ->with(
                $this->isInstanceOf(Id::class),
                $permissions
            );

        $this->service->create([
            'name'        => 'editor',
            'permissions' => $permissions,
        ]);
    }

    public function testCreateDoesNotSyncPermissionsWhenNotProvided(): void
    {
        $this->repository
            ->method('save')
            ->willReturn('new-role-uuid');

        $this->repository
            ->expects($this->never())
            ->method('syncPermissions');

        $this->service->create(['name' => 'editor']);
    }


    public function testUpdateSavesUpdatedRole(): void
    {
        $id = new Id('some-uuid');
        $existingRole = new Role(id: $id, name: new RoleName('editor'));

        $this->repository
            ->method('getById')
            ->willReturn($existingRole);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Role $role) {
                return $role->name->getValue() === 'admin';
            }));

        $this->repository
            ->method('syncPermissions');

        $this->service->update($id, [
            'name'        => 'admin',
            'permissions' => [],
        ]);
    }

    public function testUpdateSyncsPermissions(): void
    {
        $id = new Id('some-uuid');
        $permissions = ['articles.create'];
        $existingRole = new Role(id: $id, name: new RoleName('editor'));

        $this->repository
            ->method('getById')
            ->willReturn($existingRole);

        $this->repository
            ->method('save');

        $this->repository
            ->expects($this->once())
            ->method('syncPermissions')
            ->with(
                $this->isInstanceOf(Id::class),
                $permissions
            );

        $this->service->update($id, [
            'name'        => 'admin',
            'permissions' => $permissions,
        ]);
    }


    public function testDeleteCallsRepository(): void
    {
        $id = new Id('some-uuid');

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id);

        $this->service->delete($id);
    }

    public function testSyncPermissionsCallsRepository(): void
    {
        $id = new Id('some-uuid');
        $permissions = ['articles.create', 'news.delete'];

        $this->repository
            ->expects($this->once())
            ->method('syncPermissions')
            ->with($id, $permissions);

        $this->service->syncPermissions($id, $permissions);
    }
}
