<?php
declare(strict_types=1);

namespace Modules\Users\tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Enums\Permission;
use Modules\Users\Services\RoleService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class AdminRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private RoleService $roleService;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->roleService = $this->createMock(RoleService::class);
        $this->app->instance(RoleService::class, $this->roleService);

        $userService = $this->createMock(\Modules\Users\Services\UserService::class);
        $userService->method('getById')->willReturn($this->makeAdminUser());
        $this->app->instance(\Modules\Users\Services\UserService::class, $userService);

        session(['user_id' => 'admin-uuid']);
    }

    private function makeAdminUser(): User
    {
        return new User(
            id: new Id('admin-uuid'),
            username: new Username('admin'),
            email: new Email('admin@example.com'),
            password: Password::fromPlain('secret123'),
            roles: [
                new Role(
                    id: new Id('role-uuid'),
                    name: new RoleName('admin'),
                    createdAt: new \DateTimeImmutable(),
                    updatedAt: new \DateTimeImmutable(),
                )
            ],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    private function makeRole(string $id = 'role-uuid', string $name = 'editor', array $permissions = []): Role
    {
        return new Role(
            id: new Id($id),
            name: new RoleName($name),
            permissions: $permissions,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function testIndexReturns200(): void
    {
        $this->roleService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.roles.index'));

        $response->assertStatus(200);
    }

    public function testIndexPassesRolesToView(): void
    {
        $role = $this->makeRole();
        $paginator = new LengthAwarePaginator([$role], 1, 10);

        $this->roleService
            ->method('getAll')
            ->willReturn($paginator);

        $response = $this->get(route('admin.roles.index'));

        $response->assertViewHas('roles');
        $response->assertViewHas('title', 'Roles');
    }

    public function testCreateReturns200(): void
    {
        $response = $this->get(route('admin.roles.create'));

        $response->assertStatus(200);
    }

    public function testCreatePassesPermissionsToView(): void
    {
        $response = $this->get(route('admin.roles.create'));

        $response->assertViewHas('permissions', Permission::cases());
    }

    public function testStoreCreatesRoleAndRedirects(): void
    {
        $this->roleService
            ->expects($this->once())
            ->method('create')
            ->willReturn('new-role-uuid');

        $response = $this->post(route('admin.roles.store'), [
            'name' => 'editor',
        ]);

        $response->assertRedirect(route('admin.roles.edit', ['role' => 'new-role-uuid']));
        $response->assertSessionHas('success');
    }

    public function testEditReturns200(): void
    {
        $this->roleService
            ->method('getById')
            ->willReturn($this->makeRole());

        $response = $this->get(route('admin.roles.edit', ['role' => 'role-uuid']));

        $response->assertStatus(200);
    }

    public function testEditPassesRoleAndPermissionsToView(): void
    {
        $role = $this->makeRole();

        $this->roleService
            ->method('getById')
            ->willReturn($role);

        $response = $this->get(route('admin.roles.edit', ['role' => 'role-uuid']));

        $response->assertViewHas('role', fn($r) => $r->id->getValue() === 'role-uuid');
        $response->assertViewHas('permissions', Permission::cases());
    }

    public function testEditPassesCorrectSelectedPermissionKeys(): void
    {
        $role = $this->makeRole(permissions: [Permission::USER_CREATE]);

        $this->roleService
            ->method('getById')
            ->willReturn($role);

        $response = $this->get(route('admin.roles.edit', ['role' => 'role-uuid']));

        $response->assertViewHas(
            'selectedPermissionKeys',
            [Permission::USER_CREATE->value]
        );
    }

    public function testEditPassesEmptySelectedPermissionKeysWhenNoPermissions(): void
    {
        $role = $this->makeRole(permissions: []);

        $this->roleService
            ->method('getById')
            ->willReturn($role);

        $response = $this->get(route('admin.roles.edit', ['role' => 'role-uuid']));

        $response->assertViewHas('selectedPermissionKeys', []);
    }

    public function testUpdateCallsServiceAndRedirects(): void
    {
        $this->roleService
            ->expects($this->once())
            ->method('update');

        $response = $this->put(route('admin.roles.update', ['role' => 'role-uuid']), [
            'name'        => 'editor',
            'permissions' => [],
        ]);

        $response->assertRedirect(route('admin.roles.edit', ['role' => 'role-uuid']));
        $response->assertSessionHas('success');
    }

    public function testDestroyCallsServiceAndRedirects(): void
    {
        $this->roleService
            ->expects($this->once())
            ->method('delete');

        $response = $this->delete(route('admin.roles.destroy', ['role' => 'role-uuid']));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');
    }
}
