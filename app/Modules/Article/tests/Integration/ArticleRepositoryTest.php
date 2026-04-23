<?php
declare(strict_types=1);

namespace Modules\Article\Tests\Integration;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Filter\ArticleFilter;
use Modules\Article\Repositories\ArticleRepository;
use Modules\Article\ValueObjects\ArticleText;
use Modules\Article\ValueObjects\ArticleTitle;
use Symfony\Component\Uid\UuidV7;
use Tests\TestCase;

class ArticleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ArticleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.sqlite.foreign_key_constraints' => false]);

        $this->repository = app(ArticleRepository::class);
    }

    private function insertArticle(string $id, string $slug = 'test-article', bool $status = true): void
    {
        DB::table('articles')->insert([
            'id'         => $id,
            'slug'       => $slug,
            'status'     => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertTranslation(string $articleId, string $locale = 'uk', string $title = 'Тест'): void
    {
        DB::table('article_translations')->insert([
            'id'         => (string) new UuidV7(),
            'article_id' => $articleId,
            'locale'     => $locale,
            'title'      => $title,
            'text'       => 'Текст статті',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeArticle(?Id $id = null, string $slug = 'test-article'): Article
    {
        return new Article(
            id: $id,
            status: true,
            slug: $slug,
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
    }

    private function makeTranslation(Id $articleId, string $locale = 'uk'): ArticleTranslation
    {
        return new ArticleTranslation(
            articleId: $articleId,
            locale: $locale,
            title: new ArticleTitle('Тест'),
            text: new ArticleText('Текст'),
            seoTitle: 'SEO title',
            seoDescription: 'SEO description',
            seoKeywords: 'keywords',
            seoOgImage: null,
        );
    }

    public function testGetReturnsArticle(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertArticle($uuid);

        $result = $this->repository->get(new Id($uuid));

        $this->assertInstanceOf(Article::class, $result);
        $this->assertEquals($uuid, $result->id->getValue());
        $this->assertEquals('test-article', $result->slug);
    }

    public function testGetReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->get(new Id('non-existent'));

        $this->assertNull($result);
    }

    public function testGetReturnsArticleWithTranslations(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertArticle($uuid);
        $this->insertTranslation($uuid, 'uk', 'Тест');

        $result = $this->repository->get(new Id($uuid));

        $this->assertArrayHasKey('uk', $result->translations);
    }

    public function testGetBySlugReturnsArticle(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertArticle($uuid, 'my-slug');

        $result = $this->repository->getBySlug('my-slug');

        $this->assertInstanceOf(Article::class, $result);
        $this->assertEquals('my-slug', $result->slug);
    }

    public function testGetBySlugReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getBySlug('non-existent');

        $this->assertNull($result);
    }

    public function testSaveCreatesArticleAndReturnsId(): void
    {
        $article = $this->makeArticle();
        $id = $this->repository->save($article);

        $this->assertNotEmpty($id);
        $this->assertDatabaseHas('articles', ['id' => $id, 'slug' => 'test-article']);
    }

    public function testSaveStoresCorrectStatus(): void
    {
        $article = $this->makeArticle();
        $id = $this->repository->save($article);

        $this->assertDatabaseHas('articles', ['id' => $id, 'status' => true]);
    }

    public function testSaveUpdatesExistingArticle(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertArticle($uuid, 'old-slug');

        $article = new Article(
            id: new Id($uuid),
            status: true,
            slug: 'new-slug',
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );

        $this->repository->save($article);

        $this->assertDatabaseHas('articles', ['id' => $uuid, 'slug' => 'new-slug']);
    }

    public function testDeleteRemovesArticle(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertArticle($uuid);

        $this->repository->delete(new Id($uuid));

        $this->assertDatabaseMissing('articles', ['id' => $uuid]);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $this->insertArticle((string) new UuidV7(), 'article-1');
        $this->insertArticle((string) new UuidV7(), 'article-2');

        $paginator = $this->repository->getAll(new ArticleFilter());

        $this->assertEquals(2, $paginator->total());
        $this->assertInstanceOf(Article::class, $paginator->items()[0]);
    }

    public function testGetAllFiltersByStatus(): void
    {
        $this->insertArticle((string) new UuidV7(), 'active', true);
        $this->insertArticle((string) new UuidV7(), 'inactive', false);

        $filter = new ArticleFilter(status: 1);
        $paginator = $this->repository->getAll($filter);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals('active', $paginator->items()[0]->slug);
    }

    public function testGetAllFiltersByDateFrom(): void
    {
        $this->insertArticle((string) new UuidV7(), 'old-article');
        DB::table('articles')->where('slug', 'old-article')->update(['created_at' => '2023-01-01 00:00:00']);

        $this->insertArticle((string) new UuidV7(), 'new-article');
        DB::table('articles')->where('slug', 'new-article')->update(['created_at' => '2024-06-01 00:00:00']);

        $filter = new ArticleFilter(dateFrom: '2024-01-01');
        $paginator = $this->repository->getAll($filter);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals('new-article', $paginator->items()[0]->slug);
    }

    public function testGetAllFiltersByDateTo(): void
    {
        $this->insertArticle((string) new UuidV7(), 'old-article');
        DB::table('articles')->where('slug', 'old-article')->update(['created_at' => '2023-01-01 00:00:00']);

        $this->insertArticle((string) new UuidV7(), 'new-article');
        DB::table('articles')->where('slug', 'new-article')->update(['created_at' => '2024-06-01 00:00:00']);

        $filter = new ArticleFilter(dateTo: '2023-12-31');
        $paginator = $this->repository->getAll($filter);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals('old-article', $paginator->items()[0]->slug);
    }

    public function testGetAllAppendsFilterToLinks(): void
    {
        $this->insertArticle((string) new UuidV7());

        $filter = new ArticleFilter(search: 'тест');
        $paginator = $this->repository->getAll($filter);

        $this->assertStringContainsString('search', $paginator->url(1));
    }

    public function testGetAllPublishedReturnsOnlyPublished(): void
    {
        $this->insertArticle((string) new UuidV7(), 'published', true);
        $this->insertArticle((string) new UuidV7(), 'unpublished', false);

        $collection = $this->repository->getAllPublished();

        $this->assertCount(1, $collection);
        $this->assertEquals('published', $collection->first()->slug);
    }

    public function testGetAllPublishedReturnsArticleInstances(): void
    {
        $this->insertArticle((string) new UuidV7(), 'published', true);

        $collection = $this->repository->getAllPublished();

        $this->assertInstanceOf(Article::class, $collection->first());
    }

    public function testGetByIdsReturnsMatchingArticles(): void
    {
        $uuid1 = (string) new UuidV7();
        $uuid2 = (string) new UuidV7();
        $uuid3 = (string) new UuidV7();

        $this->insertArticle($uuid1, 'article-1');
        $this->insertArticle($uuid2, 'article-2');
        $this->insertArticle($uuid3, 'article-3');

        $collection = $this->repository->getByIds([$uuid1, $uuid2]);

        $this->assertCount(2, $collection);
    }

    public function testGetByIdsReturnsOnlyPublished(): void
    {
        $uuid1 = (string) new UuidV7();
        $uuid2 = (string) new UuidV7();

        $this->insertArticle($uuid1, 'published', true);
        $this->insertArticle($uuid2, 'unpublished', false);

        $collection = $this->repository->getByIds([$uuid1, $uuid2]);

        $this->assertCount(1, $collection);
        $this->assertEquals('published', $collection->first()->slug);
    }

    public function testSaveTranslationInsertsTranslation(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertArticle($uuid);

        $translation = $this->makeTranslation(new Id($uuid));
        $this->repository->saveTranslation($translation);

        $this->assertDatabaseHas('article_translations', [
            'article_id' => $uuid,
            'locale'     => 'uk',
            'title'      => 'Тест',
        ]);
    }

    public function testSaveTranslationUpdatesExistingTranslation(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertArticle($uuid);
        $this->insertTranslation($uuid, 'uk', 'Старий заголовок');

        $translation = new ArticleTranslation(
            articleId: new Id($uuid),
            locale: 'uk',
            title: new ArticleTitle('Новий заголовок'),
            text: new ArticleText('Текст'),
        );

        $this->repository->saveTranslation($translation);

        $this->assertDatabaseHas('article_translations', [
            'article_id' => $uuid,
            'locale'     => 'uk',
            'title'      => 'Новий заголовок',
        ]);
    }

    public function testGetTranslationsForArticlesReturnsTranslations(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertArticle($uuid);
        $this->insertTranslation($uuid, 'uk', 'Тест');
        $this->insertTranslation($uuid, 'en', 'Test');

        $translations = $this->repository->getTranslationsForArticles($uuid);

        $this->assertArrayHasKey('uk', $translations);
        $this->assertArrayHasKey('en', $translations);
        $this->assertInstanceOf(ArticleTranslation::class, $translations['uk']);
    }

    public function testGetTranslationsForArticlesReturnsEmptyArrayWhenEmpty(): void
    {
        $translations = $this->repository->getTranslationsForArticles('');

        $this->assertEmpty($translations);
    }

    public function testSyncTagsInsertsTags(): void
    {
        $uuid = (string) new UuidV7();
        $tagId = (string) new UuidV7();

        $this->insertArticle($uuid);
        DB::table('tags')->insert([
            'id'         => $tagId,
            'title'      => 'laravel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->repository->syncTags(new Id($uuid), [$tagId]);

        $this->assertDatabaseHas('added_tags', [
            'entity_id'   => $uuid,
            'tag_id'      => $tagId,
            'entity_type' => 'Modules\Article\Entities\Article',
        ]);
    }

    public function testSyncTagsClearsExistingTagsBeforeInserting(): void
    {
        $uuid = (string) new UuidV7();
        $oldTagId = (string) new UuidV7();
        $newTagId = (string) new UuidV7();

        $this->insertArticle($uuid);
        DB::table('tags')->insert([
            ['id' => $oldTagId, 'title' => 'old', 'created_at' => now(), 'updated_at' => now()],
            ['id' => $newTagId, 'title' => 'new', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->repository->syncTags(new Id($uuid), [$oldTagId]);
        $this->repository->syncTags(new Id($uuid), [$newTagId]);

        $this->assertDatabaseMissing('added_tags', ['tag_id' => $oldTagId]);
        $this->assertDatabaseHas('added_tags', ['tag_id' => $newTagId]);
    }

    public function testSyncTagsWithEmptyArrayDeletesAllTags(): void
    {
        $uuid = (string) new UuidV7();
        $tagId = (string) new UuidV7();

        $this->insertArticle($uuid);
        DB::table('tags')->insert(['id' => $tagId, 'title' => 'laravel', 'created_at' => now(), 'updated_at' => now()]);

        $this->repository->syncTags(new Id($uuid), [$tagId]);
        $this->repository->syncTags(new Id($uuid), []);

        $this->assertDatabaseMissing('added_tags', ['entity_id' => $uuid]);
    }
}
