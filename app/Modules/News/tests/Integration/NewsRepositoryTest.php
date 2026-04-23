<?php
declare(strict_types=1);

namespace Modules\News\tests\Integration;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\News\Entities\News;
use Modules\News\Entities\NewsTranslation;
use Modules\News\Filter\NewsFilter;
use Modules\News\Repositories\NewsRepository;
use Modules\News\ValueObjects\NewsText;
use Modules\News\ValueObjects\NewsTitle;
use Symfony\Component\Uid\UuidV7;
use Tests\TestCase;

class NewsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private NewsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(NewsRepository::class);
    }

    private function insertNews(string $id, string $slug = 'test-news', bool $status = true): void
    {
        DB::table('news')->insert([
            'id'         => $id,
            'slug'       => $slug,
            'status'     => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertTranslation(string $newsId, string $locale = 'uk', string $title = 'Тест'): void
    {
        DB::table('news_translations')->insert([
            'id'      => (string) new UuidV7(),
            'news_id' => $newsId,
            'locale'  => $locale,
            'title'   => $title,
            'text'    => 'Текст новини',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeNews(?Id $id = null, string $slug = 'test-news'): News
    {
        return new News(
            id: $id,
            status: true,
            slug: $slug,
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
    }

    private function makeTranslation(Id $newsId, string $locale = 'uk'): NewsTranslation
    {
        return new NewsTranslation(
            newId: $newsId,
            locale: $locale,
            title: new NewsTitle('Тест'),
            text: new NewsText('Текст'),
            seoTitle: 'SEO title',
            seoDescription: 'SEO description',
            seoKeywords: 'keywords',
            seoOgImage: null,
        );
    }

    public function testGetReturnsNews(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertNews($uuid);

        $result = $this->repository->get(new Id($uuid));

        $this->assertInstanceOf(News::class, $result);
        $this->assertEquals($uuid, $result->id->getValue());
        $this->assertEquals('test-news', $result->slug);
    }

    public function testGetReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->get(new Id('non-existent'));

        $this->assertNull($result);
    }

    public function testGetBySlugReturnsNews(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertNews($uuid, 'my-slug');

        $result = $this->repository->getBySlug('my-slug');

        $this->assertInstanceOf(News::class, $result);
        $this->assertEquals('my-slug', $result->slug);
    }

    public function testGetBySlugReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getBySlug('non-existent');

        $this->assertNull($result);
    }

    public function testSaveCreatesNewsAndReturnsId(): void
    {
        $news = $this->makeNews();
        $id = $this->repository->save($news);

        $this->assertNotEmpty($id);
        $this->assertDatabaseHas('news', ['id' => $id, 'slug' => 'test-news']);
    }

    public function testSaveUpdatesExistingNews(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertNews($uuid, 'old-slug');

        $news = new News(
            id: new Id($uuid),
            status: true,
            slug: 'new-slug',
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );

        $this->repository->save($news);

        $this->assertDatabaseHas('news', ['id' => $uuid, 'slug' => 'new-slug']);
    }

    public function testDeleteRemovesNews(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertNews($uuid);

        $this->repository->delete(new Id($uuid));

        $this->assertDatabaseMissing('news', ['id' => $uuid]);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $this->insertNews((string) new UuidV7(), 'news-1');
        $this->insertNews((string) new UuidV7(), 'news-2');

        $paginator = $this->repository->getAll(new NewsFilter());

        $this->assertEquals(2, $paginator->total());
        $this->assertInstanceOf(News::class, $paginator->items()[0]);
    }

    public function testGetAllFiltersByStatus(): void
    {
        $this->insertNews((string) new UuidV7(), 'active-news', true);
        $this->insertNews((string) new UuidV7(), 'inactive-news', false);

        $filter = new NewsFilter(status: 1);
        $paginator = $this->repository->getAll($filter);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals('active-news', $paginator->items()[0]->slug);
    }

    public function testGetAllFiltersByDateFrom(): void
    {
        $this->insertNews((string) new UuidV7(), 'old-news');
        DB::table('news')->where('slug', 'old-news')->update(['created_at' => '2023-01-01 00:00:00']);

        $this->insertNews((string) new UuidV7(), 'new-news');
        DB::table('news')->where('slug', 'new-news')->update(['created_at' => '2024-06-01 00:00:00']);

        $filter = new NewsFilter(dateFrom: '2024-01-01');
        $paginator = $this->repository->getAll($filter);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals('new-news', $paginator->items()[0]->slug);
    }

    public function testGetAllFiltersByDateTo(): void
    {
        $this->insertNews((string) new UuidV7(), 'old-news');
        DB::table('news')->where('slug', 'old-news')->update(['created_at' => '2023-01-01 00:00:00']);

        $this->insertNews((string) new UuidV7(), 'new-news');
        DB::table('news')->where('slug', 'new-news')->update(['created_at' => '2024-06-01 00:00:00']);

        $filter = new NewsFilter(dateTo: '2023-12-31');
        $paginator = $this->repository->getAll($filter);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals('old-news', $paginator->items()[0]->slug);
    }

    public function testGetAllPublishedReturnsOnlyPublished(): void
    {
        $this->insertNews((string) new UuidV7(), 'published', true);
        $this->insertNews((string) new UuidV7(), 'unpublished', false);

        $collection = $this->repository->getAllPublished();

        $this->assertCount(1, $collection);
        $this->assertEquals('published', $collection->first()->slug);
    }

    public function testGetAllPublishedReturnsNewsInstances(): void
    {
        $this->insertNews((string) new UuidV7(), 'published', true);

        $collection = $this->repository->getAllPublished();

        $this->assertInstanceOf(News::class, $collection->first());
    }

    public function testGetByIdsReturnsMatchingNews(): void
    {
        $uuid1 = (string) new UuidV7();
        $uuid2 = (string) new UuidV7();
        $uuid3 = (string) new UuidV7();

        $this->insertNews($uuid1, 'news-1');
        $this->insertNews($uuid2, 'news-2');
        $this->insertNews($uuid3, 'news-3');

        $collection = $this->repository->getByIds([$uuid1, $uuid2]);

        $this->assertCount(2, $collection);
    }

    public function testGetByIdsReturnsOnlyPublished(): void
    {
        $uuid1 = (string) new UuidV7();
        $uuid2 = (string) new UuidV7();

        $this->insertNews($uuid1, 'published', true);
        $this->insertNews($uuid2, 'unpublished', false);

        $collection = $this->repository->getByIds([$uuid1, $uuid2]);

        $this->assertCount(1, $collection);
        $this->assertEquals('published', $collection->first()->slug);
    }

    public function testSaveTranslationInsertsTranslation(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertNews($uuid);

        $translation = $this->makeTranslation(new Id($uuid));
        $this->repository->saveTranslation($translation);

        $this->assertDatabaseHas('news_translations', [
            'news_id' => $uuid,
            'locale'  => 'uk',
            'title'   => 'Тест',
        ]);
    }

    public function testSaveTranslationUpdatesExistingTranslation(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertNews($uuid);
        $this->insertTranslation($uuid, 'uk', 'Старий заголовок');

        $translation = new NewsTranslation(
            newId: new Id($uuid),
            locale: 'uk',
            title: new NewsTitle('Новий заголовок'),
            text: new NewsText('Текст'),
        );

        $this->repository->saveTranslation($translation);

        $this->assertDatabaseHas('news_translations', [
            'news_id' => $uuid,
            'locale'  => 'uk',
            'title'   => 'Новий заголовок',
        ]);
    }

    public function testGetTranslationsForNewsReturnsTranslations(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertNews($uuid);
        $this->insertTranslation($uuid, 'uk', 'Тест');
        $this->insertTranslation($uuid, 'en', 'Test');

        $translations = $this->repository->getTranslationsForNews($uuid);

        $this->assertArrayHasKey('uk', $translations);
        $this->assertArrayHasKey('en', $translations);
        $this->assertInstanceOf(NewsTranslation::class, $translations['uk']);
    }

    public function testGetTranslationsForNewsReturnsEmptyArrayWhenNoTranslations(): void
    {
        $translations = $this->repository->getTranslationsForNews('');

        $this->assertEmpty($translations);
    }

    public function testSyncTagsInsertsTags(): void
    {
        $uuid = (string) new UuidV7();
        $tagId = (string) new UuidV7();
        $this->insertNews($uuid);

        DB::table('tags')->insert([
            'id'         => $tagId,
            'title'      => 'laravel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->repository->syncTags(new Id($uuid), [$tagId]);

        $this->assertDatabaseHas('taggables', [
            'entity_id'   => $uuid,
            'tag_id'      => $tagId,
            'entity_type' => 'Modules\News\Entities\News',
        ]);
    }

    public function testSyncTagsClearsExistingTagsBeforeInserting(): void
    {
        $uuid = (string) new UuidV7();
        $oldTagId = (string) new UuidV7();
        $newTagId = (string) new UuidV7();

        $this->insertNews($uuid);

        DB::table('tags')->insert([
            ['id' => $oldTagId, 'title' => 'old', 'created_at' => now(), 'updated_at' => now()],
            ['id' => $newTagId, 'title' => 'new', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->repository->syncTags(new Id($uuid), [$oldTagId]);
        $this->repository->syncTags(new Id($uuid), [$newTagId]);

        $this->assertDatabaseMissing('taggables', ['tag_id' => $oldTagId]);
        $this->assertDatabaseHas('taggables', ['tag_id' => $newTagId]);
    }

    public function testSyncTagsWithEmptyArrayDeletesAllTags(): void
    {
        $uuid = (string) new UuidV7();
        $tagId = (string) new UuidV7();

        $this->insertNews($uuid);
        DB::table('tags')->insert(['id' => $tagId, 'title' => 'laravel', 'created_at' => now(), 'updated_at' => now()]);

        $this->repository->syncTags(new Id($uuid), [$tagId]);
        $this->repository->syncTags(new Id($uuid), []);

        $this->assertDatabaseMissing('taggables', ['entity_id' => $uuid]);
    }
}
