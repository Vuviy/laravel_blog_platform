<?php
declare(strict_types=1);

namespace Modules\Article\Tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Filter\ArticleFilter;
use Modules\Article\Services\ArticleService;
use Modules\Article\ValueObjects\ArticleText;
use Modules\Article\ValueObjects\ArticleTitle;
use Modules\Seo\Services\SeoService;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    private ArticleService $articleService;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->articleService = $this->createMock(ArticleService::class);
        $this->app->instance(ArticleService::class, $this->articleService);

        $seoService = $this->createMock(SeoService::class);
        $seoService->method('setTitle')->willReturnSelf();
        $seoService->method('setDescription')->willReturnSelf();
        $seoService->method('setCanonical')->willReturnSelf();
        $seoService->method('setOg')->willReturnSelf();
        $this->app->instance(SeoService::class, $seoService);
    }

    private function makeTranslation(string $locale = 'uk', array $override = []): ArticleTranslation
    {
        return new ArticleTranslation(
            id: new Id((string) \Symfony\Component\Uid\UuidV7::generate()),
            articleId: new Id('article-uuid'),
            locale: $locale,
            title: new ArticleTitle($override['title'] ?? 'Тестова стаття'),
            text: new ArticleText($override['text'] ?? 'Текст статті'),
            seoTitle: $override['seo_title'] ?? null,
            seoDescription: $override['seo_description'] ?? null,
            seoKeywords: $override['seo_keywords'] ?? null,
            seoOgImage: $override['seo_og_image'] ?? null,
        );
    }

    private function makeArticle(string $id = 'article-uuid', string $slug = 'test-article', array $translations = []): Article
    {
        return new Article(
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
        $this->articleService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('articles', ['locale' => 'uk']));

        $response->assertStatus(200);
    }

    public function testIndexPassesArticlesFilterTitleToView(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->articleService
            ->method('getAll')
            ->willReturn($paginator);

        $response = $this->get(route('articles', ['locale' => 'uk']));

        $response->assertViewHas('articles', $paginator);
        $response->assertViewHas('filter');
        $response->assertViewHas('title');
    }

    public function testIndexMergesStatusOneIntoFilter(): void
    {
        $this->articleService
            ->expects($this->once())
            ->method('getAll')
            ->with($this->callback(fn(ArticleFilter $filter) => $filter->status === 1))
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $this->get(route('articles', ['locale' => 'uk']));
    }

    public function testShowReturns200(): void
    {
        $article = $this->makeArticle();

        $this->articleService
            ->method('getArticleBySlug')
            ->willReturn($article);

        $response = $this->get(route('articles.show', ['locale' => 'uk', 'slug' => 'test-article']));

        $response->assertStatus(200);
    }

    public function testShowPassesArticleToView(): void
    {
        $article = $this->makeArticle();

        $this->articleService
            ->method('getArticleBySlug')
            ->with('test-article')
            ->willReturn($article);

        $response = $this->get(route('articles.show', ['locale' => 'uk', 'slug' => 'test-article']));

        $response->assertViewHas('article', fn($a) => $a->id->getValue() === 'article-uuid');
    }

    public function testShowUsesSeoTitleWhenProvided(): void
    {
        $article = $this->makeArticle(translations: [
            $this->makeTranslation('uk', ['seo_title' => 'Custom SEO Title']),
        ]);

        $this->articleService
            ->method('getArticleBySlug')
            ->willReturn($article);

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

        $this->get(route('articles.show', ['locale' => 'uk', 'slug' => 'test-article']));
    }

    public function testShowUsesTitleWhenSeoTitleIsNull(): void
    {
        $article = $this->makeArticle(translations: [
            $this->makeTranslation('uk', ['seo_title' => null, 'title' => 'Звичайний заголовок']),
        ]);

        $this->articleService
            ->method('getArticleBySlug')
            ->willReturn($article);

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

        $this->get(route('articles.show', ['locale' => 'uk', 'slug' => 'test-article']));
    }

    public function testShowSetsSeoOgImageWhenProvided(): void
    {
        $article = $this->makeArticle(translations: [
            $this->makeTranslation('uk', ['seo_og_image' => 'articles/seo/uk/image.jpg']),
        ]);

        $this->articleService
            ->method('getArticleBySlug')
            ->willReturn($article);

        $seoService = $this->createMock(SeoService::class);
        $seoService->method('setTitle')->willReturnSelf();
        $seoService->method('setDescription')->willReturnSelf();
        $seoService->method('setCanonical')->willReturnSelf();
        $seoService
            ->expects($this->atLeastOnce())
            ->method('setOg')
            ->willReturnCallback(function (string $property, string $value) use ($seoService) {
                if ($property === 'image') {
                    $this->assertStringContainsString('articles/seo/uk/image.jpg', $value);
                }
                return $seoService;
            });

        $this->app->instance(SeoService::class, $seoService);

        $this->get(route('articles.show', ['locale' => 'uk', 'slug' => 'test-article']));
    }

    public function testShowFallsBackToUkTranslation(): void
    {
        $article = $this->makeArticle(translations: [
            $this->makeTranslation('uk', ['title' => 'Українська стаття']),
        ]);

        $this->articleService
            ->method('getArticleBySlug')
            ->willReturn($article);

        app()->setLocale('en');

        $response = $this->get(route('articles.show', ['locale' => 'en', 'slug' => 'test-article']));

        $response->assertStatus(200);
    }
}
