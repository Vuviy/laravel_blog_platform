<?php

declare(strict_types=1);

namespace Modules\Seo\tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Seo\Entities\SeoPage;
use Modules\Seo\Services\SeoPageService;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class AdminSeoControllerTest extends TestCase
{
    use RefreshDatabase;

    private SeoPageService $seoPageService;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->seoPageService = $this->createMock(SeoPageService::class);
        $this->app->instance(SeoPageService::class, $this->seoPageService);

        $userService = $this->createMock(UserService::class);
        $userService->method('getById')->willReturn($this->makeAdminUser());
        $this->app->instance(UserService::class, $userService);

        session(['user_id' => 'admin-uuid']);
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

    private function makeSeoPage(string $id = 'seo-uuid'): SeoPage
    {
        return new SeoPage(
            id: new Id($id),
            url: '/contacts',
            translations: [],
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
    }

    private function validData(): array
    {
        return [
            'url' => '/contacts',
            'translations' => [
                'uk' => [
                    'seo_title'       => 'Контакти',
                    'seo_description' => 'Сторінка контактів',
                    'seo_keywords'    => 'контакти',
                ],
                'en' => [
                    'seo_title'       => 'Contacts',
                    'seo_description' => 'Contacts page',
                    'seo_keywords'    => 'contacts',
                ],
            ],
        ];
    }

    public function testIndexReturns200(): void
    {
        $this->seoPageService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.seo.index'));

        $response->assertStatus(200);
    }

    public function testIndexPassesSeoPagesTitleToView(): void
    {
        $this->seoPageService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.seo.index'));

        $response->assertViewHas('seoPages');
        $response->assertViewHas('title', 'SEO');
    }

    public function testCreateReturns200(): void
    {
        $response = $this->get(route('admin.seo.create'));

        $response->assertStatus(200);
    }

    public function testStoreCreatesAndRedirects(): void
    {
        $this->seoPageService
            ->expects($this->once())
            ->method('create')
            ->willReturn('new-seo-uuid');

        $response = $this->post(route('admin.seo.store'), $this->validData());

        $response->assertRedirect(route('admin.seo.edit', ['seo' => 'new-seo-uuid']));
        $response->assertSessionHas('success');
    }

    public function testStoreValidationFailsWithoutUrl(): void
    {
        $data = $this->validData();
        unset($data['url']);

        $response = $this->post(route('admin.seo.store'), $data);

        $response->assertSessionHasErrors(['url']);
    }

    public function testStoreValidationFailsWhenSeoTitleTooLong(): void
    {
        $data = $this->validData();
        $data['translations']['uk']['seo_title'] = str_repeat('a', 61);

        $response = $this->post(route('admin.seo.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.seo_title']);
    }

    public function testStoreValidationFailsWhenSeoDescriptionTooLong(): void
    {
        $data = $this->validData();
        $data['translations']['uk']['seo_description'] = str_repeat('a', 161);

        $response = $this->post(route('admin.seo.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.seo_description']);
    }

    public function testEditReturns200(): void
    {
        $this->seoPageService
            ->method('getSeoPageById')
            ->willReturn($this->makeSeoPage());

        $response = $this->get(route('admin.seo.edit', ['seo' => 'seo-uuid']));

        $response->assertStatus(200);
    }

    public function testEditPassesSeoPageToView(): void
    {
        $seoPage = $this->makeSeoPage();

        $this->seoPageService
            ->method('getSeoPageById')
            ->willReturn($seoPage);

        $response = $this->get(route('admin.seo.edit', ['seo' => 'seo-uuid']));

        $response->assertViewHas('seoPage', fn($s) => $s->id->getValue() === 'seo-uuid');
    }

    public function testUpdateCallsServiceAndRedirects(): void
    {
        $this->seoPageService
            ->expects($this->once())
            ->method('update');

        $response = $this->put(route('admin.seo.update', ['seo' => 'seo-uuid']), $this->validData());

        $response->assertRedirect(route('admin.seo.edit', ['seo' => 'seo-uuid']));
        $response->assertSessionHas('success');
    }

    public function testUpdateValidationFailsWithoutUrl(): void
    {
        $data = $this->validData();
        unset($data['url']);

        $response = $this->put(route('admin.seo.update', ['seo' => 'seo-uuid']), $data);

        $response->assertSessionHasErrors(['url']);
    }

    public function testDestroyCallsServiceAndRedirects(): void
    {
        $this->seoPageService
            ->expects($this->once())
            ->method('delete');

        $response = $this->delete(route('admin.seo.destroy', ['seo' => 'seo-uuid']));

        $response->assertRedirect(route('admin.seo.index'));
        $response->assertSessionHas('success');
    }
}
