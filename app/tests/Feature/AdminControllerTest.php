<?php

namespace Tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $userService = $this->createMock(UserService::class);
        $userService->method('getById')->willReturn($this->makeAdminUser());
        $this->app->instance(UserService::class, $userService);
    }

    private function makeAdminUser(): User
    {
        return new User(
            id: new Id('admin-uuid'),
            username: new Username('admin'),
            email: new Email('admin@example.com'),
            password: Password::fromPlain('secret123'),
            roles: [
                new Role(
                    id: new Id('role-uuid'),
                    name: new RoleName('admin'),
                    createdAt: new \DateTimeImmutable(),
                    updatedAt: new \DateTimeImmutable(),
                )
            ],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function testDashboardReturns200(): void
    {
        $adminUser = $this->makeAdminUser();
        session([
            'user_id' => 'admin-uuid',
            'user'    => $adminUser,
        ]);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function testDashboardPassesTitleToView(): void
    {
        $adminUser = $this->makeAdminUser();
        session([
            'user_id' => 'admin-uuid',
            'user'    => $adminUser,
        ]);

        $response = $this->get(route('admin.dashboard'));

        $response->assertViewHas('title', 'Admin');
    }

    public function testDashboardRedirectsWhenNotLoggedIn(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.loginForm'));
    }

    public function testDashboardRedirectsWhenNotAdmin(): void
    {
        $regularUser = new User(
            id: new Id('user-uuid'),
            username: new Username('user'),
            email: new Email('user@example.com'),
            password: Password::fromPlain('secret123'),
            roles: [
                new Role(
                    id: new Id('role-uuid'),
                    name: new RoleName('user'),
                    createdAt: new \DateTimeImmutable(),
                    updatedAt: new \DateTimeImmutable(),
                )
            ],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $userService = $this->createMock(UserService::class);
        $userService->method('getById')->willReturn($regularUser);
        $this->app->instance(UserService::class, $userService);

        session([
            'user_id' => 'user-uuid',
            'user'    => $regularUser,
        ]);

        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect();
    }

    public function testLoginFormReturns200(): void
    {
        $response = $this->get(route('admin.loginForm'));

        $response->assertStatus(200);
    }

    public function testLoginFormPassesTitleToView(): void
    {
        $response = $this->get(route('admin.loginForm'));

        $response->assertViewHas('title', 'Login');
    }

    public function testLoginFormAccessibleWithoutAuth(): void
    {
        $response = $this->get(route('admin.loginForm'));

        $response->assertStatus(200);
    }
}
