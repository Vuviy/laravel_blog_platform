<?php

declare(strict_types=1);

namespace Modules\Tags\tests\Unit;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Tags\Entities\Tag;
use Modules\Tags\Repositories\Contracts\TagRepositoryInterface;
use Modules\Tags\Services\TagService;
use Modules\Tags\ValueObjects\TagTitle;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    private TagRepositoryInterface $repository;
    private TagService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(TagRepositoryInterface::class);
        $this->service = new TagService($this->repository);
    }

    private function makeTag(string $id = 'tag-uuid', string $title = 'laravel'): Tag
    {
        return new Tag(
            id: new Id($id),
            title: new TagTitle($title),
            created_at: new \DateTimeImmutable(),
        );
    }

    public function testGetByIdReturnsTag(): void
    {
        $id = new Id('tag-uuid');
        $tag = $this->makeTag();

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($tag);

        $result = $this->service->getById($id);

        $this->assertSame($tag, $result);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $this->repository
            ->method('get')
            ->willReturn(null);

        $result = $this->service->getById(new Id('non-existent'));

        $this->assertNull($result);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($paginator);

        $result = $this->service->getAll();

        $this->assertSame($paginator, $result);
    }

    public function testCreateSavesTagAndReturnsId(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturn('new-tag-uuid');

        $result = $this->service->create(['title' => 'laravel']);

        $this->assertEquals('new-tag-uuid', $result);
    }

    public function testCreateSavesTagWithCorrectTitle(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Tag $tag) => $tag->title->getValue() === 'laravel'))
            ->willReturn('new-tag-uuid');

        $this->service->create(['title' => 'laravel']);
    }

    public function testUpdateSavesTagWithNewTitle(): void
    {
        $id = new Id('tag-uuid');
        $tag = $this->makeTag('tag-uuid', 'old-title');

        $this->repository
            ->method('get')
            ->willReturn($tag);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Tag $t) => $t->title->getValue() === 'new-title'));

        $this->service->update($id, ['title' => 'new-title']);
    }

    public function testUpdateKeepsOldTitleWhenNotProvided(): void
    {
        $id = new Id('tag-uuid');
        $tag = $this->makeTag('tag-uuid', 'old-title');

        $this->repository
            ->method('get')
            ->willReturn($tag);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Tag $t) => $t->title->getValue() === 'old-title'));

        $this->service->update($id, []);
    }

    public function testUpdateKeepsOriginalId(): void
    {
        $id = new Id('tag-uuid');
        $tag = $this->makeTag('tag-uuid');

        $this->repository
            ->method('get')
            ->willReturn($tag);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Tag $t) => $t->id->getValue() === 'tag-uuid'));

        $this->service->update($id, ['title' => 'new-title']);
    }

    public function testDeleteCallsRepository(): void
    {
        $id = new Id('tag-uuid');

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id);

        $this->service->delete($id);
    }

    public function testGetByTagNameReturnsTag(): void
    {
        $tag = $this->makeTag();

        $this->repository
            ->expects($this->once())
            ->method('getByTagName')
            ->with('laravel')
            ->willReturn($tag);

        $result = $this->service->getByTagName('laravel');

        $this->assertSame($tag, $result);
    }

    public function testGetByTagNameReturnsNullWhenNotFound(): void
    {
        $this->repository
            ->method('getByTagName')
            ->willReturn(null);

        $result = $this->service->getByTagName('non-existent');

        $this->assertNull($result);
    }

    public function testGetEntitiesByTagReturnsPaginator(): void
    {
        $tag = $this->makeTag();
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository
            ->expects($this->once())
            ->method('getEntitiesByTagId')
            ->with($tag->id)
            ->willReturn($paginator);

        $result = $this->service->getEntitiesByTag($tag);

        $this->assertSame($paginator, $result);
    }
}
