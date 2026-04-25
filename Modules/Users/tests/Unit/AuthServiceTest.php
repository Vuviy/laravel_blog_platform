<?php
declare(strict_types=1);

namespace Modules\Users\tests\Unit;

use App\Contracts\SessionManagerInterface;
use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Modules\Users\Entities\User;
use Modules\Users\Services\AuthService;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private SessionManagerInterface $sessionManager;
    private UserService $userService;
    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->userService = $this->createMock(UserService::class);
        $this->service = new AuthService($this->sessionManager, $this->userService);
    }

    private function makeUser(string $plainPassword = 'secret123'): User
    {
        return new User(
            username: new Username('alex'),
            email: new Email('user@example.com'),
            password: Password::fromPlain($plainPassword),
            id: new Id('some-uuid'),
        );
    }


    public function testLoginReturnsEmptyErrorsOnSuccess(): void
    {
        $user = $this->makeUser('secret123');

        $this->userService
            ->method('getByEmail')
            ->willReturn($user);

        $errors = $this->service->login([
            'email'    => 'user@example.com',
            'password' => 'secret123',
        ]);

        $this->assertEmpty($errors);
    }

    public function testLoginReturnsErrorWhenUserNotFound(): void
    {
        $this->userService
            ->method('getByEmail')
            ->willReturn(null);

        $errors = $this->service->login([
            'email'    => 'notfound@example.com',
            'password' => 'secret123',
        ]);

        $this->assertArrayHasKey('message', $errors);
        $this->assertEquals('Invalid login credentials.', $errors['message']);
    }

    public function testLoginReturnsErrorWhenPasswordIsWrong(): void
    {
        $user = $this->makeUser('secret123');

        $this->userService
            ->method('getByEmail')
            ->willReturn($user);

        $errors = $this->service->login([
            'email'    => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertArrayHasKey('message', $errors);
        $this->assertEquals('Invalid login credentials.', $errors['message']);
    }

    public function testLoginStoresUserIdInSession(): void
    {
        $user = $this->makeUser('secret123');

        $this->userService
            ->method('getByEmail')
            ->willReturn($user);

        $calls = [];

        $this->sessionManager
            ->expects($this->atLeastOnce())
            ->method('store')
            ->willReturnCallback(function (string $key, mixed $value) use (&$calls) {
                $calls[$key] = $value;
            });

        $this->service->login([
            'email'    => 'user@example.com',
            'password' => 'secret123',
        ]);

        $this->assertArrayHasKey('user_id', $calls);
        $this->assertEquals('some-uuid', $calls['user_id']);

        $this->assertArrayHasKey('user', $calls);
        $this->assertSame($user, $calls['user']);
    }

    public function testLoginRegeneratesSession(): void
    {
        $user = $this->makeUser('secret123');

        $this->userService
            ->method('getByEmail')
            ->willReturn($user);

        $this->sessionManager
            ->expects($this->once())
            ->method('regenerate');

        $this->service->login([
            'email'    => 'user@example.com',
            'password' => 'secret123',
        ]);
    }

    public function testRegisterCallsUserServiceCreate(): void
    {
        $data = [
            'email'    => 'newuser@example.com',
            'password' => 'secret123',
        ];

        $this->userService
            ->expects($this->once())
            ->method('create')
            ->with($data);

        $this->service->register($data);
    }

    public function testLogoutFlushesSession(): void
    {
        $this->sessionManager
            ->expects($this->once())
            ->method('flush');

        $this->service->logout(Request::create('/logout'));
    }

    public function testLogoutRegeneratesSession(): void
    {
        $this->sessionManager
            ->expects($this->once())
            ->method('regenerate');

        $this->service->logout(Request::create('/logout'));
    }

    public function testLogoutFlushesBeforeRegenerate(): void
    {
        $order = [];

        $this->sessionManager
            ->method('flush')
            ->willReturnCallback(function () use (&$order) {
                $order[] = 'flush';
            });

        $this->sessionManager
            ->method('regenerate')
            ->willReturnCallback(function () use (&$order) {
                $order[] = 'regenerate';
            });

        $this->service->logout(Request::create('/logout'));

        $this->assertEquals(['flush', 'regenerate'], $order);
    }
}
