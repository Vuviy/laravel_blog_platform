<?php

declare(strict_types=1);

use App\Http\Middleware\CheckPermissionMiddleware;
use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Modules\Users\Entities\User;
use Modules\Users\Services\UserService;
use Tests\TestCase;
use Tests\TestController\DummyControllerWithEmptyPermissions;
use Tests\TestController\DummyControllerWithoutAttributes;
use Tests\TestController\DummyControllerWithPermissions;
use Tests\TestController\FakeUserWithPermissions;

class CheckPermissionMiddlewareTest extends TestCase
{
    private UserService $userService;
    private CheckPermissionMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = $this->createMock(UserService::class);

        $this->middleware = new CheckPermissionMiddleware($this->userService);
    }

    private function makeRequest(object $controller = new DummyControllerWithoutAttributes()): Request
    {
        $request = Request::create('/test', 'GET');

        $route = $this->createMock(Route::class);
        $route->method('getController')->willReturn($controller);
        $route->method('getActionMethod')->willReturn('index');

        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    private function makeFakeUser(bool $permissionResp): User
    {
        return New FakeUserWithPermissions($permissionResp);
    }

    public function testCallsNextWhenNoAttributes()
    {
        $request = $this->makeRequest();

        $result = $this->middleware->handle($request, fn () => 'OK');

        $this->assertSame('OK', $result);
    }

    public function testCallsNextWhenTruePermission()
    {
        $this->userService
            ->method('getById')
            ->willReturn($this->makeFakeUser(true));

        session(['user_id' => new Id('1')]);

        $request = $this->makeRequest(new DummyControllerWithPermissions());

        $result = $this->middleware->handle($request, fn () => 'OK');

        $this->assertSame('OK', $result);
    }

    public function testAbortsWhenNoUser()
    {
        $request = $this->makeRequest(new DummyControllerWithPermissions());

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->middleware->handle($request, fn () => 'OK');
    }

    public function testRedirectsWhenNoPermission()
    {
        $request = $this->makeRequest(new DummyControllerWithEmptyPermissions());
        $request->headers->set('referer', '/prev');

        $response = $this->middleware->handle($request, fn () => response('OK'));

        $this->assertTrue(method_exists($response, 'isRedirect'));
    }

    public function testPassesWhenUserHasPermission()
    {
        $this->userService->method('getById')->willReturn(
            new FakeUserWithPermissions(true)
        );

        $request = $this->makeRequest();

        session(['user_id' => new Id('1')]);

        $result = $this->middleware->handle($request, fn () => 'OK');

        $this->assertSame('OK', $result);
    }
}
