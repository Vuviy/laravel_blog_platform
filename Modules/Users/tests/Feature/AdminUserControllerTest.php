<?php
declare(strict_types=1);

namespace Modules\Users\tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;
    private RoleRepositoryInterface $roleRepository;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->userService = $this->createMock(UserService::class);
        $this->roleRepository = $this->createMock(RoleRepositoryInterface::class);

        $this->app->instance(UserService::class, $this->userService);
        $this->app->instance(RoleRepositoryInterface::class, $this->roleRepository);

        $adminUser = new User(
            id: new Id('admin-uuid'),
            username: new Username('admin'),
            email: new Email('admin@example.com'),
            password: Password::fromPlain('secret123'),
            roles: [
                new Role(
                    id: new Id('role-uuid'),
                    name: new RoleName('admin'),
                )
            ],
        );

        $this->userService
            ->method('getById')
            ->willReturnCallback(function (Id $id) {
                if ($id->getValue() === 'admin-uuid') {
                    return $this->makeAdminUser();
                }
                return $this->makeUser($id->getValue());
            });

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

    private function makeUser(string $id = 'some-uuid'): User
    {
        return new User(
            id: new Id($id),
            username: new Username('testuser'),
            email: new Email('user@example.com'),
            password: Password::fromPlain('secret123'),
            roles: [],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    private function makeRole(string $id = 'role-uuid', string $name = 'admin'): Role
    {
        return new Role(
            id: new Id($id),
            name: new RoleName($name),
        );
    }

    public function testIndexReturns200(): void
    {
        $this->userService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    public function testIndexPassesUsersToView(): void
    {
        $user = $this->makeUser();
        $paginator = new LengthAwarePaginator([$user], 1, 10);

        $this->userService
            ->method('getAll')
            ->willReturn($paginator);

        $response = $this->get(route('admin.users.index'));

        $response->assertViewHas('users');
        $response->assertViewHas('title', 'Users');
    }

    public function testCreateReturns200(): void
    {
        $this->roleRepository
            ->method('getAllList')
            ->willReturn(new Collection());

        $response = $this->get(route('admin.users.create'));

        $response->assertStatus(200);
    }

    public function testCreatePassesRolesToView(): void
    {
        $roles = new Collection([$this->makeRole()]);

        $this->roleRepository
            ->method('getAllList')
            ->willReturn($roles);

        $response = $this->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertViewHas('roles', fn($viewRoles) => $viewRoles->count() === 1);
    }

    public function testStoreCreatesUserAndRedirects(): void
    {
        $this->userService
            ->expects($this->once())
            ->method('create')
            ->willReturn('new-user-uuid');

        $response = $this->post(route('admin.users.store'), [
            'email'    => 'newuser@example.com',
            'username' => 'newuser',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.users.edit', ['user' => 'new-user-uuid']));
        $response->assertSessionHas('success');
    }

    public function testEditReturns200(): void
    {
        $user = $this->makeUser();

        $this->userService
            ->method('getById')
            ->willReturn($user);

        $this->roleRepository
            ->method('getAllList')
            ->willReturn(new Collection());

        $response = $this->get(route('admin.users.edit', ['user' => 'some-uuid']));

        $response->assertStatus(200);
    }


    public function testEditPassesUserAndRolesToView(): void
    {
        $roles = new Collection([$this->makeRole()]);

        $this->roleRepository
            ->method('getAllList')
            ->willReturn($roles);

        $response = $this->get(route('admin.users.edit', ['user' => 'some-uuid']));

        $response->assertStatus(200);
        $response->assertViewHas('user', fn($u) => $u->id->getValue() === 'some-uuid');
        $response->assertViewHas('roles', fn($r) => $r->count() === 1);
        $response->assertViewHas('selectedRoleIds', []);
    }


    public function testEditPassesCorrectSelectedRoleIds(): void
    {
        $roleId = 'role-uuid';
        $role = $this->makeRole($roleId);

        $userWithRole = new User(
            id: new Id('some-uuid'),
            username: new Username('testuser'),
            email: new Email('user@example.com'),
            password: Password::fromPlain('secret123'),
            roles: [$role],
        );

        $userService = $this->createMock(UserService::class);
        $userService
            ->method('getById')
            ->willReturnCallback(function (Id $id) use ($userWithRole) {
                if ($id->getValue() === 'admin-uuid') {
                    return $this->makeAdminUser();
                }
                return $userWithRole;
            });

        $this->app->instance(UserService::class, $userService);

        $this->roleRepository
            ->method('getAllList')
            ->willReturn(new Collection([$role]));

        $response = $this->get(route('admin.users.edit', ['user' => 'some-uuid']));

        $response->assertViewHas('selectedRoleIds', [$roleId]);
    }

    public function testUpdateCallsServiceAndRedirects(): void
    {
        $this->userService
            ->expects($this->once())
            ->method('update');

        $response = $this->put(route('admin.users.update', ['user' => 'some-uuid']), [
            'email'    => 'updated@example.com',
            'username' => 'updateduser',
        ]);

        $response->assertRedirect(route('admin.users.edit', ['user' => 'some-uuid']));
        $response->assertSessionHas('success');
    }

    public function testDestroyCallsServiceAndRedirects(): void
    {
        $this->userService
            ->expects($this->once())
            ->method('delete');

        $response = $this->delete(route('admin.users.destroy', ['user' => 'some-uuid']));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
    }
}
