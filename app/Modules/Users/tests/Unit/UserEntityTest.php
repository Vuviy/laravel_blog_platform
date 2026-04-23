<?php

declare(strict_types=1);

namespace Modules\Users\tests\Unit;

use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Enums\Permission;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class UserEntityTest extends TestCase
{
    public function testHasRoleReturnsTrueWhenRoleExists(): void
    {
        $role = $this->createRole('admin');

        $user = new User(
            username: $this->createUsername(),
            email: $this->createEmail(),
            password: $this->createPassword(),
            roles: [$role]
        );

        $this->assertTrue($user->hasRole('admin'));
    }

    public function testHasRoleReturnsFalseWhenRoleDoesNotExist(): void
    {
        $role = $this->createRole('user');

        $user = new User(
            username: $this->createUsername(),
            email: $this->createEmail(),
            password: $this->createPassword(),
            roles: [$role]
        );

        $this->assertFalse($user->hasRole('admin'));
    }

    public function testHasPermissionReturnsTrueWhenPermissionExists(): void
    {
        $permission = $this->createPermission('user.update');

        $role = $this->createRole('admin', [$permission]);

        $user = new User(
            username: $this->createUsername(),
            email: $this->createEmail(),
            password: $this->createPassword(),
            roles: [$role]
        );

        $this->assertTrue($user->hasPermission('user.update'));
    }

    public function testHasPermissionReturnsFalseWhenPermissionDoesNotExist(): void
    {
        $user = new User(
            username: $this->createUsername(),
            email: $this->createEmail(),
            password: $this->createPassword(),
            roles: []
        );

        $this->assertFalse($user->hasPermission('user.update'));
    }

    public function testOptimizePermissionsMergesPermissionsFromRoles(): void
    {
        $permission1 = $this->createPermission('user.update');
        $permission2 = $this->createPermission('user.delete');

        $role1 = $this->createRole('admin', [$permission1]);
        $role2 = $this->createRole('moderator', [$permission2]);

        $user = new User(
            username: $this->createUsername(),
            email: $this->createEmail(),
            password: $this->createPassword(),
            roles: [$role1, $role2]
        );

        $this->assertTrue($user->hasPermission('user.update'));
        $this->assertTrue($user->hasPermission('user.delete'));
    }

    public function testOptimizePermissionsOverridesDuplicateKeys(): void
    {
        $permission1 = $this->createPermission('user.update');
        $permission2 = $this->createPermission('user.update');

        $role1 = $this->createRole('admin', [$permission1]);
        $role2 = $this->createRole('moderator', [$permission2]);

        $user = new User(
            username: $this->createUsername(),
            email: $this->createEmail(),
            password: $this->createPassword(),
            roles: [$role1, $role2]
        );

        $this->assertCount(1, $user->permissions);
        $this->assertTrue($user->hasPermission('user.update'));
    }


    private function createRole(string $name, array $permission = []): Role
    {
        return new Role(
            id: null,
            name: new RoleName($name),
            permissions: $permission,
            createdAt: null,
            updatedAt: null,
        );
    }

    private function createPermission(string $key): Permission
    {
        return Permission::from($key);
    }

    private function createUsername(): Username
    {
        return $this->createMock(Username::class);
    }

    private function createEmail(): Email
    {
        return $this->createMock(Email::class);
    }

    private function createPassword(): Password
    {
        return $this->createMock(Password::class);
    }
}
