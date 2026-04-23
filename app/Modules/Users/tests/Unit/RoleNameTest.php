<?php

declare(strict_types=1);

namespace Modules\Users\tests\Unit;

use Modules\Users\ValueObjects\RoleName;
use Tests\TestCase;

class RoleNameTest extends TestCase
{
    public function testCreateWithValidName(): void
    {
        $role = new RoleName('admin');

        $this->assertEquals('admin', $role->getValue());
    }

    public function testCreateWithUppercase(): void
    {
        $role = new RoleName('Admin');

        $this->assertEquals('Admin', $role->getValue());
    }

    public function testCreateWithNumbers(): void
    {
        $role = new RoleName('admin123');

        $this->assertEquals('admin123', $role->getValue());
    }

    public function testCreateWithSpacesInside(): void
    {
        $role = new RoleName('super admin');

        $this->assertEquals('super admin', $role->getValue());
    }

    public function testToStringReturnValue(): void
    {
        $role = new RoleName('admin');

        $this->assertEquals('admin', (string) $role);
    }

    public function testThrowsExceptionForEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('rolename cannot be empty');

        new RoleName('');
    }

    public function testThrowsExceptionForWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('rolename cannot be empty');

        new RoleName('   ');
    }

    public function testThrowsExceptionForTabOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RoleName("\t");
    }

    public function testThrowsExceptionForNewlineOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RoleName("\n");
    }
}
