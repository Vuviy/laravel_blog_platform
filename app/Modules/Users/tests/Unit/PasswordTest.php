<?php

declare(strict_types=1);

namespace Modules\Users\tests\Unit;

use Modules\Users\ValueObjects\Password;
use Tests\TestCase;

class PasswordTest extends TestCase
{

    public function testCreateFromPlainPassword(): void
    {
        $password = Password::fromPlain('secret123');

        $this->assertNotEmpty($password->getHash());
    }

    public function testPlainPasswordIsHashedNotStored(): void
    {
        $password = Password::fromPlain('secret123');

        $this->assertNotEquals('secret123', $password->getHash());
    }

    public function testUsesArgon2idAlgorithm(): void
    {
        $password = Password::fromPlain('secret123');

        $this->assertStringStartsWith('$argon2id$', $password->getHash());
    }

    public function testTwoSamePlainsProduceDifferentHashes(): void
    {
        $password1 = Password::fromPlain('secret123');
        $password2 = Password::fromPlain('secret123');

        $this->assertNotEquals($password1->getHash(), $password2->getHash());
    }

    public function testCreateFromExistingHash(): void
    {
        $hash = password_hash('secret123', PASSWORD_ARGON2ID);
        $password = Password::fromHash($hash);

        $this->assertEquals($hash, $password->getHash());
    }

    public function testThrowsExceptionForEmptyHash(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty password hash provided.');

        Password::fromHash('');
    }

    public function testVerifiesCorrectPlainPassword(): void
    {
        $password = Password::fromPlain('secret123');

        $this->assertTrue($password->verify('secret123'));
    }

    public function testRejectsWrongPlainPassword(): void
    {
        $password = Password::fromPlain('secret123');

        $this->assertFalse($password->verify('wrongpassword'));
    }

    public function testVerifiesAgainstHashCreatedExternally(): void
    {
        $hash = password_hash('secret123', PASSWORD_ARGON2ID);
        $password = Password::fromHash($hash);

        $this->assertTrue($password->verify('secret123'));
    }


    public function testGetHashReturnHash(): void
    {
        $hash = password_hash('secret123', PASSWORD_ARGON2ID);
        $password = Password::fromHash($hash);

        $this->assertEquals($hash, $password->getHash());
    }

    public function testToStringReturnHash(): void
    {
        $password = Password::fromPlain('secret123');

        $this->assertEquals($password->getHash(), (string) $password);
    }
}
