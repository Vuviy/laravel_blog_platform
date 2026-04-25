<?php

declare(strict_types=1);

namespace Modules\Users\tests\Unit;

use Modules\Users\ValueObjects\Email;
use Tests\TestCase;

class EmailTest extends TestCase
{
    /**
     * A basic test example.
     */

    public function testCreateWithValidEmail(): void
    {
        $email = new Email('user@example.com');

        $this->assertEquals('user@example.com', $email->getValue());
    }

    public function testTrimsWhitespace(): void
    {
        $email = new Email('  user@example.com  ');

        $this->assertEquals('user@example.com', $email->getValue());
    }

    public function testToStringReturnsEmail(): void
    {
        $email = new Email('user@example.com');

        $this->assertEquals('user@example.com', (string) $email);
    }

    public function testThrowsExceptionForEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email');

        new Email('');
    }

    public function testThrowsExceptionForWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('   ');
    }

    public function testThrowsExceptionForMissingAtSign(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('userexample.com');
    }

    public function testThrowsExceptionForMissingDomain(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('user@');
    }

    public function testThrowsExceptionForInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('not-an-email');
    }

    public function testExceptionMessageContainsInvalidEmail(): void
    {
        $invalidEmail = 'bad-email';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($invalidEmail);

        new Email($invalidEmail);
    }
}
