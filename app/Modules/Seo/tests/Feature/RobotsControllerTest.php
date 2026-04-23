<?php

declare(strict_types=1);

namespace Modules\Seo\tests\Feature;

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

class RobotsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $userService = $this->createMock(UserService::class);
        $userService->method('getById')->willReturn($this->makeAdminUser());
        $this->app->instance(UserService::class, $userService);

        session(['user_id' => 'admin-uuid']);

        $this->robotsPath = sys_get_temp_dir() . '/robots_test.txt';
        file_put_contents($this->robotsPath, "User-agent: *\nDisallow: /admin\n");

        config(['seo.robots_path' => $this->robotsPath]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($this->robotsPath)) {
            unlink($this->robotsPath);
        }
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

    public function testRobotFormReturns200(): void
    {
        $response = $this->get(route('admin.robotForm'));

        $response->assertStatus(200);
    }

    public function testRobotFormPassesContentToView(): void
    {
        $response = $this->get(route('admin.robotForm'));

        $response->assertViewHas('content', "User-agent: *\nDisallow: /admin\n");
    }

    public function testSaveRobotWritesContentToFile(): void
    {
        $newContent = "User-agent: *\nDisallow: /admin\nDisallow: /api";

        $this->post(route('admin.saveRobot'), ['content' => $newContent]);

        $this->assertEquals($newContent, file_get_contents($this->robotsPath));
    }

    public function testSaveRobotRedirectsBack(): void
    {
        $response = $this->post(route('admin.saveRobot'), [
            'content' => "User-agent: *\nDisallow: /admin\n",
        ]);

        $response->assertRedirect();
    }

    public function testIndexReturnsRobotsFileContent(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent: *');
    }

    public function testIndexReturnsPlainTextContentType(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }


}
