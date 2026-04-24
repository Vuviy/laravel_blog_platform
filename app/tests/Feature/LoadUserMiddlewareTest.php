<?php

declare(strict_types=1);

use App\Http\Middleware\LoadUser;
use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Modules\Users\Entities\User;
use Modules\Users\Services\UserService;
use Tests\TestCase;

class LoadUserMiddlewareTest extends TestCase
{

    private UserService $userService;
    private LoadUser $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = $this->createMock(UserService::class);

        $this->middleware = new LoadUser($this->userService);
    }


    public function testSetsNullUserWhenNoUserId(): void
    {
        $request = Request::create('/test');

        session()->forget('user_id');

        \Illuminate\Support\Facades\View::shouldReceive('share')
            ->once()
            ->with('user', null);

        $nextCalled = false;

        $this->middleware->handle($request, function ($req) use (&$nextCalled) {
            $nextCalled = true;
            $this->assertNull($req->attributes->get('user'));
            return 'next';
        });

        $this->assertTrue($nextCalled);
    }

    public function testLoadsUserFromServiceWhenUserIdExists(): void
    {
        $user = $this->createMock(User::class);

        $this->userService
            ->expects($this->once())
            ->method('getById')
            ->with($this->isInstanceOf(Id::class))
            ->willReturn($user);


        $request = Request::create('/test');

        session(['user_id' => '123']);

        $this->middleware->handle($request, function ($req) use ($user) {
            $this->assertSame($user, $req->attributes->get('user'));
            return 'next';
        });
    }

    public function testSharesUserToView(): void
    {
        \Illuminate\Support\Facades\View::shouldReceive('share')
            ->once()
            ->with('user', $this->anything());

        $user = $this->createMock(User::class);


        $this->userService->method('getById')->willReturn($user);


        session(['user_id' => '123']);

        $request = Request::create('/test');

        $this->middleware->handle($request, fn ($req) => 'ok');
    }
}
