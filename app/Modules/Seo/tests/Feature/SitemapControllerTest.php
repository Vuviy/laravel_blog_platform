<?php

declare(strict_types=1);

namespace Modules\Seo\tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Article\Entities\Article;
use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;
use Modules\News\Entities\News;
use Modules\News\Repositories\Contracts\NewsRepositoryInterface;
use Modules\Seo\Repositories\Contracts\SeoPageRepositoryInterface;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Symfony\Component\Uid\UuidV7;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $sitemapPath;
    private ArticleRepositoryInterface $articleRepository;
    private NewsRepositoryInterface $newsRepository;
    private SeoPageRepositoryInterface $seoPageRepository;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->sitemapPath = sys_get_temp_dir() . '/sitemap_test.xml';
        config(['seo.sitemap_path' => $this->sitemapPath]);
        config(['app.available_locales' => ['uk', 'en']]);

        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->newsRepository = $this->createMock(NewsRepositoryInterface::class);
        $this->seoPageRepository = $this->createMock(SeoPageRepositoryInterface::class);

        $this->app->instance(ArticleRepositoryInterface::class, $this->articleRepository);
        $this->app->instance(NewsRepositoryInterface::class, $this->newsRepository);
        $this->app->instance(SeoPageRepositoryInterface::class, $this->seoPageRepository);

        $userService = $this->createMock(UserService::class);
        $userService->method('getById')->willReturn($this->makeAdminUser());
        $this->app->instance(UserService::class, $userService);

        session(['user_id' => 'admin-uuid']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($this->sitemapPath)) {
            unlink($this->sitemapPath);
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

    private function mockEmptyRepositories(): void
    {
        $this->articleRepository->method('getAllPublished')->willReturn(new Collection());
        $this->newsRepository->method('getAllPublished')->willReturn(new Collection());
        $this->seoPageRepository->method('getAll')->willReturn(new LengthAwarePaginator([], 0, 10));
    }

    public function testSitemapFormReturns200(): void
    {
        $response = $this->get(route('admin.sitemapForm'));

        $response->assertStatus(200);
    }

    public function testSitemapFormPassesEmptyContentWhenFileNotExists(): void
    {
        $response = $this->get(route('admin.sitemapForm'));

        $response->assertViewHas('content', '');
    }

    public function testSitemapFormPassesContentWhenFileExists(): void
    {
        $xmlContent = '<?xml version="1.0"?><urlset></urlset>';
        file_put_contents($this->sitemapPath, $xmlContent);

        $response = $this->get(route('admin.sitemapForm'));

        $response->assertViewHas('content', $xmlContent);
    }

    public function testGenerateSitemapCreatesFile(): void
    {
        $this->mockEmptyRepositories();

        $this->post(route('admin.generateSitemap'));

        $this->assertFileExists($this->sitemapPath);
    }

    public function testGenerateSitemapRedirectsWithMessage(): void
    {
        $this->mockEmptyRepositories();

        $response = $this->post(route('admin.generateSitemap'));

        $response->assertRedirect();
        $response->assertSessionHas('message', 'sitemap.xml згенеровано');
    }

    public function testGenerateSitemapIncludesArticleUrls(): void
    {
        $this->articleRepository->method('getAllPublished')->willReturn(new Collection([$this->makeArticle('test-article')]));
        $this->newsRepository->method('getAllPublished')->willReturn(new Collection());
        $this->seoPageRepository->method('getAll')->willReturn(new LengthAwarePaginator([], 0, 10));

        $this->post(route('admin.generateSitemap'));

        $content = file_get_contents($this->sitemapPath);
        $this->assertStringContainsString('uk/articles/test-article', $content);
        $this->assertStringContainsString('en/articles/test-article', $content);
    }

    public function testGenerateSitemapIncludesNewsUrls(): void
    {
        $this->articleRepository->method('getAllPublished')->willReturn(new Collection());
        $this->newsRepository
            ->method('getAllPublished')
            ->willReturn(new Collection([$this->makeNews('test-news')]));

        $this->seoPageRepository->method('getAll')->willReturn(new LengthAwarePaginator([], 0, 10));

        $this->post(route('admin.generateSitemap'));
        $content = file_get_contents($this->sitemapPath);
        $this->assertStringContainsString('uk/news/test-news', $content);
        $this->assertStringContainsString('en/news/test-news', $content);
    }

    public function testGenerateSitemapIncludesSeoPageUrls(): void
    {
        $seoPage = (object) [
            'url'        => '/contacts',
            'updated_at' => new \DateTimeImmutable('2024-01-01'),
        ];

        $this->articleRepository->method('getAllPublished')->willReturn(new Collection());
        $this->newsRepository->method('getAllPublished')->willReturn(new Collection());
        $this->seoPageRepository->method('getAll')->willReturn(new LengthAwarePaginator([$seoPage], 0, 10));

        $this->post(route('admin.generateSitemap'));

        $content = file_get_contents($this->sitemapPath);
        $this->assertStringContainsString('uk/contacts', $content);
        $this->assertStringContainsString('en/contacts', $content);
    }

    public function testGenerateSitemapProducesValidXml(): void
    {
        $this->mockEmptyRepositories();

        $this->post(route('admin.generateSitemap'));

        $content = file_get_contents($this->sitemapPath);
        $xml = simplexml_load_string($content);

        $this->assertNotFalse($xml);
    }

    public function testIndexReturns200WhenFileExists(): void
    {
        file_put_contents($this->sitemapPath, '<?xml version="1.0"?><urlset></urlset>');

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function testIndexReturns404WhenFileNotExists(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(404);
    }

    private function makeArticle(string $slug): Article
    {
        return new Article(
            id: new Id((string) new UuidV7()),
            slug: $slug,
            status: true,
            created_at: new \DateTimeImmutable('2024-01-01'),
            updated_at: new \DateTimeImmutable('2024-01-01'),
            tags: [],
            translations: [],
            comments: [],
        );
    }

    private function makeNews(string $slug): News
    {
        return new News(
            id: new Id((string) new UuidV7()),
            slug: $slug,
            status: true,
            created_at: new \DateTimeImmutable('2024-01-01'),
            updated_at: new \DateTimeImmutable('2024-01-01'),
            translations: [],
        );
    }
}
