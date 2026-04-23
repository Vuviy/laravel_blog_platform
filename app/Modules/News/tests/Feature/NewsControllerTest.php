<?php
declare(strict_types=1);

namespace Modules\News\tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\News\Entities\News;
use Modules\News\Entities\NewsTranslation;
use Modules\News\Filter\NewsFilter;
use Modules\News\Services\NewsService;
use Modules\News\ValueObjects\NewsText;
use Modules\News\ValueObjects\NewsTitle;
use Modules\Seo\Services\SeoService;

use Tests\TestCase;

class NewsControllerTest extends TestCase
{
    use RefreshDatabase;

    private NewsService $newsService;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->newsService = $this->createMock(NewsService::class);
        $this->app->instance(NewsService::class, $this->newsService);

        $seoService = $this->createMock(SeoService::class);
        $seoService->method('setTitle')->willReturnSelf();
        $seoService->method('setDescription')->willReturnSelf();
        $seoService->method('setCanonical')->willReturnSelf();
        $seoService->method('setOg')->willReturnSelf();
        $this->app->instance(SeoService::class, $seoService);
    }

    private function makeTranslation(string $locale = 'uk', array $override = []): NewsTranslation
    {
        return new NewsTranslation(
            id: new Id((string) \Symfony\Component\Uid\UuidV7::generate()),
            newId: new Id('news-uuid'),
            locale: $locale,
            title: new NewsTitle($override['title'] ?? 'Тестова новина'),
            text: new NewsText($override['text'] ?? 'Текст новини'),
            seoTitle: $override['seo_title'] ?? null,
            seoDescription: $override['seo_description'] ?? null,
            seoKeywords: $override['seo_keywords'] ?? null,
            seoOgImage: $override['seo_og_image'] ?? null,
        );
    }

    private function makeNews(string $id = 'news-uuid', string $slug = 'test-news', array $translations = []): News
    {
        return new News(
            id: new Id($id),
            slug: $slug,
            status: true,
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
            translations: $translations ?: [$this->makeTranslation('uk')],
        );
    }

    public function testIndexReturns200(): void
    {
        $this->newsService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('news', ['locale' => 'uk']));

        $response->assertStatus(200);
    }

    public function testIndexPassesNewsAndFilterToView(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->newsService
            ->method('getAll')
            ->willReturn($paginator);

        $response = $this->get(route('news', ['locale' => 'uk']));

        $response->assertViewHas('news', $paginator);
        $response->assertViewHas('filter');
    }

    public function testIndexMergesStatusOneIntoFilter(): void
    {
        $this->newsService
            ->expects($this->once())
            ->method('getAll')
            ->with($this->callback(fn(NewsFilter $filter) => $filter->status === 1))
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $this->get(route('news', ['locale' => 'uk']));
    }

    public function testIndexPassesTitleToView(): void
    {
        $this->newsService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('news', ['locale' => 'uk']));

        $response->assertViewHas('title');
    }

    public function testShowReturns200(): void
    {
        $news = $this->makeNews();

        $this->newsService
            ->method('getNewBySlug')
            ->willReturn($news);

        $response = $this->get(route('news.show', ['locale' => 'uk', 'slug' => 'test-news']));

        $response->assertStatus(200);
    }

    public function testShowPassesNewsToView(): void
    {
        $news = $this->makeNews();

        $this->newsService
            ->method('getNewBySlug')
            ->with('test-news')
            ->willReturn($news);

        $response = $this->get(route('news.show', ['locale' => 'uk', 'slug' => 'test-news']));

        $response->assertViewHas('new', fn($n) => $n->id->getValue() === 'news-uuid');
    }

    public function testShowUsesSeoTitleWhenProvided(): void
    {
        $news = $this->makeNews(translations: [
            $this->makeTranslation('uk', ['seo_title' => 'Custom SEO Title']),
        ]);

        $this->newsService
            ->method('getNewBySlug')
            ->willReturn($news);

        $seoService = $this->createMock(SeoService::class);
        $seoService
            ->expects($this->once())
            ->method('setTitle')
            ->with('Custom SEO Title')
            ->willReturnSelf();
        $seoService->method('setDescription')->willReturnSelf();
        $seoService->method('setCanonical')->willReturnSelf();
        $seoService->method('setOg')->willReturnSelf();

        $this->app->instance(SeoService::class, $seoService);

        $this->get(route('news.show', ['locale' => 'uk', 'slug' => 'test-news']));
    }

    public function testShowUsesTitleWhenSeoTitleIsNull(): void
    {
        $news = $this->makeNews(translations: [
            $this->makeTranslation('uk', ['seo_title' => null, 'title' => 'Звичайний заголовок']),
        ]);

        $this->newsService
            ->method('getNewBySlug')
            ->willReturn($news);

        $seoService = $this->createMock(SeoService::class);
        $seoService
            ->expects($this->once())
            ->method('setTitle')
            ->with('Звичайний заголовок')
            ->willReturnSelf();
        $seoService->method('setDescription')->willReturnSelf();
        $seoService->method('setCanonical')->willReturnSelf();
        $seoService->method('setOg')->willReturnSelf();

        $this->app->instance(SeoService::class, $seoService);

        $this->get(route('news.show', ['locale' => 'uk', 'slug' => 'test-news']));
    }

    public function testShowFallsBackToUkTranslation(): void
    {
        $news = $this->makeNews(translations: [
            $this->makeTranslation('uk', ['title' => 'Українська новина']),
        ]);

        $this->newsService
            ->method('getNewBySlug')
            ->willReturn($news);

        app()->setLocale('en');

        $response = $this->get(route('news.show', ['locale' => 'en', 'slug' => 'test-news']));

        $response->assertStatus(200);
    }
}
