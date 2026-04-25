<?php
declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Seo\Services\SeoService;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $seoService = $this->createMock(SeoService::class);
        $seoService->method('seoForStaticPage')->willReturnSelf();
        $this->app->instance(SeoService::class, $seoService);
    }

    public function testIndexReturns200(): void
    {
        $response = $this->get(route('home', ['locale' => 'uk']));

        $response->assertStatus(200);
    }

    public function testIndexRendersHomeView(): void
    {
        $response = $this->get(route('home', ['locale' => 'uk']));

        $response->assertViewIs('home');
    }

    public function testIndexCallsSeoForStaticPage(): void
    {
        $seoService = $this->createMock(SeoService::class);
        $seoService
            ->expects($this->once())
            ->method('seoForStaticPage')
            ->with('/')
            ->willReturnSelf();

        $this->app->instance(SeoService::class, $seoService);

        $this->get(route('home', ['locale' => 'uk']));
    }

    public function testRootRedirectsToLocale(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/' . config('app.locale'));
    }

    public function testRootRedirectsToSessionLocale(): void
    {
        session(['locale' => 'en']);

        $response = $this->get('/');

        $response->assertRedirect('/en');
    }

    public function testRootRedirectsToDefaultLocaleWhenNoSession(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/' . config('app.locale'));
    }

    public function testIndexWorksForEnLocale(): void
    {
        $response = $this->get(route('home', ['locale' => 'en']));

        $response->assertStatus(200);
    }
}
