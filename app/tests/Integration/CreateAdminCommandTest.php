<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\ValueObjects\Id;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Enums\Permission;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private RoleRepositoryInterface $roleRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->roleRepository = $this->createMock(RoleRepositoryInterface::class);

        $this->app->instance(UserRepositoryInterface::class, $this->userRepository);
        $this->app->instance(RoleRepositoryInterface::class, $this->roleRepository);
    }

    public function testCommandCreatesUserAndAdminRoles(): void
    {
        $this->roleRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnOnConsecutiveCalls('user-role-uuid', 'admin-role-uuid');

        $this->roleRepository->method('syncPermissions');
        $this->userRepository->method('save')->willReturn('user-uuid');
        $this->userRepository->method('syncRoles');

        $this->artisan('admin:create')->assertSuccessful();
    }

    public function testCommandSavesUserRoleFirst(): void
    {
        $calls = [];

        $this->roleRepository
            ->method('save')
            ->willReturnCallback(function (Role $role) use (&$calls) {
                $calls[] = $role->name->getValue();
                return $role->name->getValue() === 'user' ? 'user-role-uuid' : 'admin-role-uuid';
            });

        $this->roleRepository->method('syncPermissions');
        $this->userRepository->method('save')->willReturn('user-uuid');
        $this->userRepository->method('syncRoles');

        $this->artisan('admin:create');

        $this->assertEquals(['user', 'admin'], $calls);
    }

    public function testCommandSyncsAllPermissionsToAdminRole(): void
    {
        $this->roleRepository
            ->method('save')
            ->willReturnOnConsecutiveCalls('user-role-uuid', 'admin-role-uuid');

        $this->roleRepository
            ->expects($this->once())
            ->method('syncPermissions')
            ->with(
                $this->callback(fn(Id $id) => $id->getValue() === 'admin-role-uuid'),
                $this->callback(fn(array $permissions) => $permissions === array_column(Permission::cases(), 'value'))
            );

        $this->userRepository->method('save')->willReturn('user-uuid');
        $this->userRepository->method('syncRoles');

        $this->artisan('admin:create');
    }

    public function testCommandCreatesAdminUserWithCorrectCredentials(): void
    {
        $this->roleRepository
            ->method('save')
            ->willReturnOnConsecutiveCalls('user-role-uuid', 'admin-role-uuid');

        $this->roleRepository->method('syncPermissions');

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) {
                return $user->email->getValue() === 'admin@admin.com'
                    && $user->username->getValue() === 'admin'
                    && $user->password->verify('password');
            }))
            ->willReturn('user-uuid');

        $this->userRepository->method('syncRoles');

        $this->artisan('admin:create');
    }

    public function testCommandAssignsAdminRoleToUser(): void
    {
        $this->roleRepository
            ->method('save')
            ->willReturnOnConsecutiveCalls('user-role-uuid', 'admin-role-uuid');

        $this->roleRepository->method('syncPermissions');

        $this->userRepository
            ->method('save')
            ->willReturn('user-uuid');

        $this->userRepository
            ->expects($this->once())
            ->method('syncRoles')
            ->with(
                $this->callback(fn(Id $id) => $id->getValue() === 'user-uuid'),
                ['admin-role-uuid']
            );

        $this->artisan('admin:create');
    }

    public function testCommandOutputsSuccessMessages(): void
    {
        $this->roleRepository
            ->method('save')
            ->willReturnOnConsecutiveCalls('user-role-uuid', 'admin-role-uuid');

        $this->roleRepository->method('syncPermissions');
        $this->userRepository->method('save')->willReturn('user-uuid');
        $this->userRepository->method('syncRoles');

        $this->artisan('admin:create')
            ->expectsOutput('Created role user')
            ->expectsOutput('Created role admin')
            ->expectsOutput('Created admin user with credentials: email admin@admin.com, password password')
            ->assertSuccessful();
    }

    public function testCommandOutputsErrorWhenExceptionThrown(): void
    {
        $this->roleRepository
            ->method('save')
            ->willThrowException(new \Exception('Database error'));

        $this->artisan('admin:create')
            ->expectsOutput('Database error')
            ->assertSuccessful();
    }

    public function testCommandDoesNotSyncRolesWhenExceptionThrown(): void
    {
        $this->roleRepository
            ->method('save')
            ->willThrowException(new \Exception('Database error'));

        $this->userRepository
            ->expects($this->never())
            ->method('syncRoles');

        $this->artisan('admin:create');
    }
}
