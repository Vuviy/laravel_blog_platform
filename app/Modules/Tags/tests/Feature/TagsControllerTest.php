<?php

declare(strict_types=1);

namespace Modules\Tags\tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Tags\Entities\Tag;
use Modules\Tags\Services\TagService;
use Modules\Tags\ValueObjects\TagTitle;
use Tests\TestCase;

class TagsControllerTest extends TestCase
{
    use RefreshDatabase;

    private TagService $tagService;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->tagService = $this->createMock(TagService::class);
        $this->app->instance(TagService::class, $this->tagService);
    }

    private function makeTag(string $id = 'tag-uuid', string $title = 'laravel'): Tag
    {
        return new Tag(
            id: new Id($id),
            title: new TagTitle($title),
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
    }

    public function testIndexReturns200WhenTagExists(): void
    {
        $tag = $this->makeTag();

        $this->tagService
            ->method('getByTagName')
            ->with('laravel')
            ->willReturn($tag);

        $this->tagService
            ->method('getEntitiesByTag')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('tags.index', ['locale' => 'uk', 'tagName' => 'laravel']));

        $response->assertStatus(200);
    }

    public function testIndexReturns404WhenTagNotFound(): void
    {
        $this->tagService
            ->method('getByTagName')
            ->willReturn(null);

        $response = $this->get(route('tags.index', ['locale' => 'uk', 'tagName' => 'non-existent']));

        $response->assertStatus(404);
    }

    public function testIndexPassesTagToView(): void
    {
        $tag = $this->makeTag();

        $this->tagService
            ->method('getByTagName')
            ->willReturn($tag);

        $this->tagService
            ->method('getEntitiesByTag')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('tags.index', ['locale' => 'uk', 'tagName' => 'laravel']));

        $response->assertViewHas('tag', fn($t) => $t->id->getValue() === 'tag-uuid');
    }

    public function testIndexPassesEntitiesToView(): void
    {
        $tag = $this->makeTag();
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->tagService
            ->method('getByTagName')
            ->willReturn($tag);

        $this->tagService
            ->method('getEntitiesByTag')
            ->with($tag)
            ->willReturn($paginator);

        $response = $this->get(route('tags.index', ['locale' => 'uk', 'tagName' => 'laravel']));

        $response->assertViewHas('entities', $paginator);
    }

    public function testIndexCallsGetEntitiesByTagWithCorrectTag(): void
    {
        $tag = $this->makeTag();

        $this->tagService
            ->method('getByTagName')
            ->willReturn($tag);

        $this->tagService
            ->expects($this->once())
            ->method('getEntitiesByTag')
            ->with($tag)
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $this->get(route('tags.index', ['locale' => 'uk', 'tagName' => 'laravel']));
    }

    public function testIndexDoesNotCallGetEntitiesByTagWhenTagNotFound(): void
    {
        $this->tagService
            ->method('getByTagName')
            ->willReturn(null);

        $this->tagService
            ->expects($this->never())
            ->method('getEntitiesByTag');

        $this->get(route('tags.index', ['locale' => 'uk', 'tagName' => 'non-existent']));
    }
}
