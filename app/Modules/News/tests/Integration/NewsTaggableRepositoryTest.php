<?php
declare(strict_types=1);

namespace Modules\News\tests\Integration;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\News\Repositories\NewsRepository;
use Modules\News\Repositories\NewsTaggableRepository;
use Modules\Tags\DTO\TaggedEntityDTO;
use Symfony\Component\Uid\UuidV7;
use Tests\TestCase;

class NewsTaggableRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private const ENTITY_TYPE = 'Modules\News\Entities\News';

    private NewsRepository $repository;
    private NewsTaggableRepository $taggableRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(NewsRepository::class);
        $this->taggableRepository = app(NewsTaggableRepository::class);
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

    public function testGetById(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertNews($uuid);
        $this->insertTranslation($uuid, 'uk');

        $result = $this->taggableRepository->getByIds([new Id($uuid)]);

        $this->assertIsArray($result);
        $this->assertEquals($uuid, $result[0]->id);
        $this->assertEquals('news', $result[0]->type);
        $this->assertInstanceOf(TaggedEntityDTO::class, $result[0]);
    }

    public function testGetByIds(): void
    {
        $uuid1 = (string) new UuidV7();
        $uuid2 = (string) new UuidV7();
        $this->insertNews($uuid1);
        $this->insertNews($uuid2);
        $this->insertTranslation($uuid1, 'uk');
        $this->insertTranslation($uuid2, 'uk');

        $result = $this->taggableRepository->getByIds([new Id($uuid1), new Id($uuid2)]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetEntityType(): void
    {
        $result = $this->taggableRepository->getEntityType();

        $this->assertIsString($result);
        $this->assertSame(self::ENTITY_TYPE, $result);
    }

    public function testNotExistNews(): void
    {
        $uuid1 = (string) new UuidV7();

        $result = $this->taggableRepository->getByIds([new Id($uuid1)]);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

}
