<?php

declare(strict_types=1);

use App\Http\Middleware\AdminUserMiddleware;
use App\ValueObjects\Id;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class AdminUserMiddlewareTest extends TestCase
{
    private UserService $userService;
    private AdminUserMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->userService = $this->createMock(UserService::class);
        $this->middleware = new AdminUserMiddleware($this->userService);
    }

    private function makeUser(string $roleName = 'admin'): User
    {
        return new User(
            id: new Id('user-uuid'),
            username: new Username('user'),
            email: new Email('user@example.com'),
            password: Password::fromPlain('secret123'),
            roles: [
                new Role(
                    id: new Id('role-uuid'),
                    name: new RoleName($roleName),
                    createdAt: new \DateTimeImmutable(),
                    updatedAt: new \DateTimeImmutable(),
                )
            ],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    private function makeRequest(): Request
    {
        return Request::create('/admin');
    }

    public function testRedirectsToLoginFormWhenNoSessionUserId(): void
    {
        $request = $this->makeRequest();
        $next = fn($req) => response('ok');

        $response = $this->middleware->handle($request, $next);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('admin/login', $response->getTargetUrl());
    }

    public function testRedirectsToHomeWhenUserIsNotAdmin(): void
    {

        $this->withSession(['user_id' => 'user-uuid']);
        $request = $this->makeRequest();

        $regularUser = $this->makeUser('user');

        $this->userService
            ->method('getById')
            ->willReturn($regularUser);

        $next = fn($req) => response('ok');

        $response = $this->middleware->handle($request, $next);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('uk', $response->getTargetUrl());
    }

    public function testCallsNextWhenUserIsAdmin(): void
    {
        $this->withSession(['user_id' => 'user-uuid']);
        $request = $this->makeRequest();

        $adminUser = $this->makeUser('admin');

        $this->userService
            ->method('getById')
            ->willReturn($adminUser);

        $nextCalled = false;
        $next = function ($req) use (&$nextCalled) {
            $nextCalled = true;
            return response('ok');
        };

        $this->middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
    }

    public function testCallsGetByIdWithCorrectUserId(): void
    {
        $this->withSession(['user_id' => 'user-uuid']);
        $request = $this->makeRequest();

        $this->userService
            ->expects($this->once())
            ->method('getById')
            ->with($this->callback(fn(Id $id) => $id->getValue() === 'user-uuid'))
            ->willReturn($this->makeUser('admin'));

        $next = fn($req) => response('ok');

        $this->middleware->handle($request, $next);
    }

    public function testDoesNotCallGetByIdWhenNoSessionUserId(): void
    {
        $request = $this->makeRequest();

        $this->userService
            ->expects($this->never())
            ->method('getById');

        $next = fn($req) => response('ok');

        $this->middleware->handle($request, $next);
    }
}
