<?php
declare(strict_types=1);

namespace Modules\Users\tests\Feature;

use App\Contracts\SessionManagerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Users\Entities\User;
use Modules\Users\Services\AuthService;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('uk');
    }

    public function testLoginFormReturns200(): void
    {
        $response = $this->get(route('loginForm', ['locale' => 'uk']));

        $response->assertStatus(200);
    }

    public function testRegisterFormReturns200(): void
    {
        $response = $this->get(route('registerForm', ['locale' => 'uk']));

        $response->assertStatus(200);
    }

    public function testLoginRedirectsToHomeForRegularUser(): void
    {
        $authService = $this->createMock(AuthService::class);
        $authService->method('login')->willReturn([]);

        $user = $this->createMock(User::class);
        $user->method('hasRole')->with('admin')->willReturn(false);

        $sessionManager = $this->createMock(SessionManagerInterface::class);
        $sessionManager->method('get')->with('user')->willReturn($user);

        $this->app->instance(AuthService::class, $authService);
        $this->app->instance(SessionManagerInterface::class, $sessionManager);

        $response = $this->post(route('login', ['locale' => 'uk']), [
            'email'    => 'user@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('home', ['locale' => 'uk']));
    }

    public function testLoginRedirectsToAdminDashboardForAdminUser(): void
    {
        $authService = $this->createMock(AuthService::class);
        $authService->method('login')->willReturn([]);

        $user = $this->createMock(User::class);
        $user->method('hasRole')->with('admin')->willReturn(true);

        $sessionManager = $this->createMock(SessionManagerInterface::class);
        $sessionManager->method('get')->with('user')->willReturn($user);

        $this->app->instance(AuthService::class, $authService);
        $this->app->instance(SessionManagerInterface::class, $sessionManager);

        $response = $this->post(route('login', ['locale' => 'uk']), [
            'email'    => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function testLoginRedirectsBackWithErrorsOnFailure(): void
    {
        $authService = $this->createMock(AuthService::class);
        $authService->method('login')->willReturn([
            'message' => 'Invalid login credentials.'
        ]);

        $this->app->instance(AuthService::class, $authService);

        $response = $this->post(route('login', ['locale' => 'uk']), [
            'email'    => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['message']);
    }

    public function testLoginValidationFailsWithoutEmail(): void
    {
        $response = $this->post(route('login', ['locale' => 'uk']), [
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function testLoginValidationFailsWithoutPassword(): void
    {
        $response = $this->post(route('login', ['locale' => 'uk']), [
            'email' => 'user@example.com',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function testRegisterCallsAuthServiceAndRedirects(): void
    {
        $authService = $this->createMock(AuthService::class);
        $authService->expects($this->once())->method('register');

        $this->app->instance(AuthService::class, $authService);

        $response = $this->post(route('register', ['locale' => 'uk']), [
            'email'                 => 'newuser@example.com',
            'username'              => 'newuser',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('loginForm', ['locale' => 'uk']));
    }

    public function testRegisterValidationFailsWithInvalidEmail(): void
    {
        $response = $this->post(route('register', ['locale' => 'uk']), [
            'email'                 => 'not-an-email',
            'username'              => 'newuser',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function testLogoutCallsAuthServiceAndRedirectsToHome(): void
    {
        $authService = $this->createMock(AuthService::class);
        $authService->expects($this->once())->method('logout');

        $this->app->instance(AuthService::class, $authService);

        $response = $this->post(route('logout', ['locale' => 'uk']));

        $response->assertRedirect(route('home', ['locale' => 'uk']));
    }
}
