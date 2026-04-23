<?php

declare(strict_types=1);

namespace Modules\Users\tests\Unit;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Users\Entities\User;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UserRepositoryInterface $repository;
    private RoleRepositoryInterface $roleRepository;
    private UserService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->roleRepository = $this->createMock(RoleRepositoryInterface::class);

        $this->service = new UserService(
            $this->repository,
            $this->roleRepository
        );
    }

    public function testGetById(): void
    {
        $id = $this->createMock(Id::class);
        $user = $this->createMock(User::class);

        $this->repository
            ->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($user);

        $result = $this->service->getById($id);

        $this->assertSame($user, $result);
    }

    public function testGetByEmail(): void
    {
        $email = $this->createMock(Email::class);
        $user = $this->createMock(User::class);

        $this->repository
            ->expects($this->once())
            ->method('getByEmail')
            ->with($email)
            ->willReturn($user);

        $result = $this->service->getByEmail($email);

        $this->assertSame($user, $result);
    }


    public function testGetAll(): void
    {
        $paginator = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 10
        );

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($paginator);

        $result = $this->service->getAll();

        $this->assertSame($paginator, $result);
    }


    public function testCreateWithDefaultRole(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class))
            ->willReturn('user_id');

        $this->roleRepository
            ->expects($this->once())
            ->method('getUserRoleId')
            ->willReturn(['role_user']);

        $this->repository
            ->expects($this->once())
            ->method('syncRoles')
            ->with(
                $this->callback(fn($id) => $id instanceof Id),
                ['role_user']
            );

        $result = $this->service->create([
            'username' => 'test',
            'email' => 'test@test.com',
            'password' => '123456',
        ]);

        $this->assertEquals('user_id', $result);
    }

    public function testCreateWithExplicitRoles(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturn('user_id');

        $this->roleRepository
            ->expects($this->never())
            ->method('getUserRoleId');

        $this->repository
            ->expects($this->once())
            ->method('syncRoles')
            ->with(
                $this->callback(fn($id) => $id instanceof Id),
                ['admin', 'moderator']
            );

        $this->service->create([
            'username' => 'test',
            'email' => 'test@test.com',
            'password' => '123456',
            'roles' => ['admin', 'moderator'],
        ]);
    }

    public function testSyncRoles(): void
    {
        $id = $this->createMock(Id::class);

        $this->repository
            ->expects($this->once())
            ->method('syncRoles')
            ->with($id, ['role1']);

        $this->service->syncRoles($id, ['role1']);
    }

    public function testUpdateWithoutRoles(): void
    {
        $id = $this->createMock(Id::class);

        $existingUser = new User(
            username: $this->createMock(Username::class),
            email: $this->createMock(Email::class),
            password: $this->createMock(Password::class),
            id: $id
        );

        $this->repository
            ->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($existingUser);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) use ($id) {
                return $user->id === $id
                    && $user->updatedAt instanceof \DateTimeImmutable;
            }));

        $this->repository
            ->expects($this->never())
            ->method('syncRoles');

        $this->service->update($id, []);
    }

    public function testUpdateWithRoles(): void
    {
        $id = $this->createMock(Id::class);

        $existingUser = new User(
            username: $this->createMock(Username::class),
            email: $this->createMock(Email::class),
            password: $this->createMock(Password::class),
            id: $id
        );

        $this->repository->method('getById')->willReturn($existingUser);

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->repository
            ->expects($this->once())
            ->method('syncRoles')
            ->with($id, ['admin']);

        $this->service->update($id, [
            'roles' => ['admin']
        ]);
    }

    public function testUpdateChangesFields(): void
    {
        $id = $this->createMock(Id::class);

        $existingUser = new User(
            username: new Username('old'),
            email: new Email('old@test.com'),
            password: Password::fromPlain('old'),
            id: $id
        );

        $this->repository->method('getById')->willReturn($existingUser);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) {
                return $user->username !== null
                    && $user->email !== null
                    && $user->password !== null;
            }));

        $this->service->update($id, [
            'username' => 'new',
            'email' => 'new@test.com',
            'password' => '123456',
        ]);
    }

    public function testDelete(): void
    {
        $id = $this->createMock(Id::class);

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id);

        $this->service->delete($id);
    }
}
