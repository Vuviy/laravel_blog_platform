<?php

declare(strict_types=1);

namespace Modules\Users\tests\Unit;

use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class UsernameTest extends TestCase
{
    public function testCreateWithValidName(): void
    {
        $username = new Username('alex');

        $this->assertEquals('alex', $username->getValue());
    }

    public function testCreateWithUppercase(): void
    {
        $username = new Username('Alex');

        $this->assertEquals('Alex', $username->getValue());
    }

    public function testCreateWithNumbers(): void
    {
        $username = new Username('alex123');

        $this->assertEquals('alex123', $username->getValue());
    }

    public function testCreateWithSpacesInside(): void
    {
        $username = new Username('peter parker');

        $this->assertEquals('peter parker', $username->getValue());
    }

    public function testToStringReturnValue(): void
    {
        $username = new Username('peter');

        $this->assertEquals('peter', (string) $username);
    }

    public function testThrowsExceptionForEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('username cannot be empty');

        new Username('');
    }

    public function testThrowsExceptionForLessTwoSymbolsString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('username cannot be empty');

        new Username('a');
    }

    public function testThrowsExceptionForWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('username cannot be empty');

        new Username('   ');
    }

    public function testThrowsExceptionForTabOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Username("\t");
    }

    public function testThrowsExceptionForNewlineOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Username("\n");
    }
}
