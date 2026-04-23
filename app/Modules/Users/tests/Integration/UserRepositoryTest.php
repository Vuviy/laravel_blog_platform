<?php
declare(strict_types=1);

namespace Modules\Users\tests\Integration;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Users\Entities\User;
use Modules\Users\Repositories\UserRepository;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(UserRepository::class);
    }

    private function makeUser(?Id $id = null): User
    {
        return new User(
            id: $id,
            username: new Username('testuser'),
            email: new Email('user@example.com'),
            password: Password::fromPlain('secret123'),
        );
    }

    private function insertUser(string $id, string $email = 'user@example.com'): void
    {
        DB::table('users')->insert([
            'id'         => $id,
            'username'   => 'testuser',
            'email'      => $email,
            'password'   => password_hash('secret123', PASSWORD_ARGON2ID),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function testSaveCreatesNewUserAndReturnsId(): void
    {
        $user = $this->makeUser();
        $id = $this->repository->save($user);

        $this->assertNotEmpty($id);
        $this->assertDatabaseHas('users', ['id' => $id]);
    }

    public function testSaveStoresCorrectEmail(): void
    {
        $user = $this->makeUser();
        $id = $this->repository->save($user);

        $this->assertDatabaseHas('users', [
            'id'    => $id,
            'email' => 'user@example.com',
        ]);
    }


    public function testSaveUpdatesExistingUser(): void
    {
        $uuid = (string) \Symfony\Component\Uid\UuidV7::generate();
        $this->insertUser($uuid);

        $updated = new User(
            id: new Id($uuid),
            username: new Username('updateduser'),
            email: new Email('updated@example.com'),
            password: Password::fromPlain('newpassword'),
        );

        $this->repository->save($updated);

        $this->assertDatabaseHas('users', [
            'id'       => $uuid,
            'username' => 'updateduser',
            'email'    => 'updated@example.com',
        ]);
    }

    public function testGetByIdReturnsUser(): void
    {
        $uuid = (string) \Symfony\Component\Uid\UuidV7::generate();
        $this->insertUser($uuid);

        $user = $this->repository->getById(new Id($uuid));

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($uuid, $user->id->getValue());
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $user = $this->repository->getById(new Id('non-existent-uuid'));

        $this->assertNull($user);
    }

    public function testGetByEmailReturnsUser(): void
    {
        $uuid = (string) \Symfony\Component\Uid\UuidV7::generate();
        $this->insertUser($uuid, 'find@example.com');

        $user = $this->repository->getByEmail(new Email('find@example.com'));

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('find@example.com', $user->email->getValue());
    }

    public function testGetByEmailReturnsNullWhenNotFound(): void
    {
        $user = $this->repository->getByEmail(new Email('notfound@example.com'));

        $this->assertNull($user);
    }

    public function testDeleteRemovesUser(): void
    {
        $uuid = (string) \Symfony\Component\Uid\UuidV7::generate();
        $this->insertUser($uuid);

        $this->repository->delete(new Id($uuid));

        $this->assertDatabaseMissing('users', ['id' => $uuid]);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $this->insertUser((string) \Symfony\Component\Uid\UuidV7::generate(), 'one@example.com');
        $this->insertUser((string) \Symfony\Component\Uid\UuidV7::generate(), 'two@example.com');

        $paginator = $this->repository->getAll();

        $this->assertEquals(2, $paginator->total());
        $this->assertInstanceOf(User::class, $paginator->items()[0]);
    }


    public function testSyncRolesInsertsRoles(): void
    {
        $uuid = (string) \Symfony\Component\Uid\UuidV7::generate();
        $this->insertUser($uuid);

        $roleId = (string) \Symfony\Component\Uid\UuidV7::generate();
        DB::table('roles')->insert([
            'id'         => $roleId,
            'name'       => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->repository->syncRoles(new Id($uuid), [$roleId]);

        $this->assertDatabaseHas('user_roles', [
            'user_id' => $uuid,
            'role_id' => $roleId,
        ]);
    }

    public function testSyncRolesClearsExistingRolesBeforeInserting(): void
    {
        $uuid = (string) \Symfony\Component\Uid\UuidV7::generate();
        $this->insertUser($uuid);

        $oldRoleId = (string) \Symfony\Component\Uid\UuidV7::generate();
        $newRoleId = (string) \Symfony\Component\Uid\UuidV7::generate();

        DB::table('roles')->insert([
            ['id' => $oldRoleId, 'name' => 'editor', 'created_at' => now(), 'updated_at' => now()],
            ['id' => $newRoleId, 'name' => 'admin',  'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->repository->syncRoles(new Id($uuid), [$oldRoleId]);

        $this->repository->syncRoles(new Id($uuid), [$newRoleId]);


        $this->assertDatabaseMissing('user_roles', ['role_id' => $oldRoleId]);
        $this->assertDatabaseHas('user_roles', ['role_id' => $newRoleId]);
    }

    public function testSyncRolesWithEmptyArrayDeletesAllRoles(): void
    {
        $uuid = (string) \Symfony\Component\Uid\UuidV7::generate();
        $this->insertUser($uuid);

        $roleId = (string) \Symfony\Component\Uid\UuidV7::generate();
        DB::table('roles')->insert([
            'id' => $roleId, 'name' => 'admin', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->repository->syncRoles(new Id($uuid), [$roleId]);
        $this->repository->syncRoles(new Id($uuid), []);

        $this->assertDatabaseMissing('user_roles', ['user_id' => $uuid]);
    }
}
