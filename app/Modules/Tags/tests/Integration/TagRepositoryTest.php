<?php

declare(strict_types=1);

namespace Modules\Tags\tests\Integration;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Tags\DTO\TaggedEntityDTO;
use Modules\Tags\Entities\Tag;
use Modules\Tags\Repositories\Contracts\TaggableRepositoryInterface;
use Modules\Tags\Repositories\TagRepository;
use Modules\Tags\Services\TaggableRegistry;
use Modules\Tags\ValueObjects\TagTitle;
use Symfony\Component\Uid\UuidV7;
use Tests\TestCase;

class TagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TagRepository $repository;
    private TaggableRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new TaggableRegistry();
        $this->repository = new TagRepository($this->registry);
    }

    private function insertTag(string $id, string $title = 'laravel'): void
    {
        DB::table('tags')->insert([
            'id'         => $id,
            'title'      => $title,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeDto(string $id, \DateTimeImmutable $createdAt): TaggedEntityDTO
    {
        return new TaggedEntityDTO(
            id:        $id,
            type:      'article',
            title:     'Test Article',
            text:     'Test Article Text',
            url:       'http://localhost/uk/articles/' . $id,

            createdAt: $createdAt,
        );
    }

    public function testGetReturnsTag(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertTag($uuid);

        $result = $this->repository->get(new Id($uuid));

        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals($uuid, $result->id->getValue());
        $this->assertEquals('laravel', $result->title->getValue());
    }

    public function testGetReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->get(new Id('non-existent'));

        $this->assertNull($result);
    }

    public function testSaveCreatesTagAndReturnsId(): void
    {
        $tag = new Tag(title: new TagTitle('laravel'));

        $id = $this->repository->save($tag);

        $this->assertNotEmpty($id);
        $this->assertDatabaseHas('tags', ['id' => $id, 'title' => 'laravel']);
    }

    public function testSaveUpdatesExistingTag(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertTag($uuid, 'old-title');

        $tag = new Tag(id: new Id($uuid), title: new TagTitle('new-title'));
        $this->repository->save($tag);

        $this->assertDatabaseHas('tags', ['id' => $uuid, 'title' => 'new-title']);
    }

    public function testDeleteRemovesTag(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertTag($uuid);

        $this->repository->delete(new Id($uuid));

        $this->assertDatabaseMissing('tags', ['id' => $uuid]);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $this->insertTag((string) new UuidV7(), 'laravel');
        $this->insertTag((string) new UuidV7(), 'php');

        $paginator = $this->repository->getAll();

        $this->assertEquals(2, $paginator->total());
        $this->assertInstanceOf(Tag::class, $paginator->items()[0]);
    }

    public function testGetAllListReturnsCollectionSortedByTitle(): void
    {
        $this->insertTag((string) new UuidV7(), 'php');
        $this->insertTag((string) new UuidV7(), 'laravel');

        $collection = $this->repository->getAllList();

        $this->assertCount(2, $collection);
        $this->assertEquals('laravel', $collection->first()->title->getValue());
        $this->assertEquals('php', $collection->last()->title->getValue());
    }

    public function testGetByTagNameReturnsTag(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertTag($uuid, 'laravel');

        $result = $this->repository->getByTagName('laravel');

        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals('laravel', $result->title->getValue());
    }

    public function testGetByTagNameReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getByTagName('non-existent');

        $this->assertNull($result);
    }

    public function testGetEntitiesByTagIdReturnsPaginator(): void
    {
        $tagId = (string) new UuidV7();
        $entityId = (string) new UuidV7();
        $this->insertTag($tagId);

        DB::table('added_tags')->insert([
            'tag_id'      => $tagId,
            'entity_id'   => $entityId,
            'entity_type' => 'Modules\Article\Entities\Article',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $dto = $this->makeDto($entityId, new \DateTimeImmutable());

        $taggableRepo = $this->createMock(TaggableRepositoryInterface::class);
        $taggableRepo->method('getEntityType')->willReturn('Modules\Article\Entities\Article');
        $taggableRepo->method('getByIds')->willReturn([$dto]);

        $this->registry->register($taggableRepo);

        $paginator = $this->repository->getEntitiesByTagId(new Id($tagId));

        $this->assertEquals(1, $paginator->total());
        $this->assertSame($dto, $paginator->items()[0]);
    }

    public function testGetEntitiesByTagIdReturnsSortedByCreatedAtDesc(): void
    {
        $tagId = (string) new UuidV7();
        $this->insertTag($tagId);

        $oldId = (string) new UuidV7();
        $newId = (string) new UuidV7();

        DB::table('added_tags')->insert([
            ['tag_id' => $tagId, 'entity_id' => $oldId, 'entity_type' => 'Modules\Article\Entities\Article', 'created_at' => now(), 'updated_at' => now()],
            ['tag_id' => $tagId, 'entity_id' => $newId, 'entity_type' => 'Modules\Article\Entities\Article', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $oldDto = $this->makeDto($oldId, new \DateTimeImmutable('2024-01-01'));
        $newDto = $this->makeDto($newId, new \DateTimeImmutable('2024-06-01'));

        $taggableRepo = $this->createMock(TaggableRepositoryInterface::class);
        $taggableRepo->method('getEntityType')->willReturn('Modules\Article\Entities\Article');
        $taggableRepo->method('getByIds')->willReturn([$oldDto, $newDto]);

        $this->registry->register($taggableRepo);

        $paginator = $this->repository->getEntitiesByTagId(new Id($tagId));

        $this->assertSame($newDto, $paginator->items()[0]);
        $this->assertSame($oldDto, $paginator->items()[1]);
    }

    public function testGetEntitiesByTagIdReturnsEmptyWhenNoEntities(): void
    {
        $tagId = (string) new UuidV7();
        $this->insertTag($tagId);

        $paginator = $this->repository->getEntitiesByTagId(new Id($tagId));

        $this->assertEquals(0, $paginator->total());
        $this->assertEmpty($paginator->items());
    }
}
