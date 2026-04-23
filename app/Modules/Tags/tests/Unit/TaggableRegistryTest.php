<?php

declare(strict_types=1);

namespace Modules\Tags\tests\Unit;

use Modules\Tags\Repositories\Contracts\TaggableRepositoryInterface;
use Modules\Tags\Services\TaggableRegistry;
use Tests\TestCase;

class TaggableRegistryTest extends TestCase
{
    private TaggableRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new TaggableRegistry();
    }

    public function testRegistersAndResolvesRepository(): void
    {
        $repo = $this->createMock(TaggableRepositoryInterface::class);
        $repo->method('getEntityType')->willReturn('Modules\Article\Entities\Article');

        $this->registry->register($repo);

        $result = $this->registry->resolve('Modules\Article\Entities\Article');

        $this->assertSame($repo, $result);
    }

    public function testResolvesCorrectRepositoryWhenMultipleRegistered(): void
    {
        $articleRepo = $this->createMock(TaggableRepositoryInterface::class);
        $articleRepo->method('getEntityType')->willReturn('Modules\Article\Entities\Article');

        $newsRepo = $this->createMock(TaggableRepositoryInterface::class);
        $newsRepo->method('getEntityType')->willReturn('Modules\News\Entities\News');

        $this->registry->register($articleRepo);
        $this->registry->register($newsRepo);

        $this->assertSame($articleRepo, $this->registry->resolve('Modules\Article\Entities\Article'));
        $this->assertSame($newsRepo, $this->registry->resolve('Modules\News\Entities\News'));
    }

    public function testThrowsExceptionForUnknownEntityType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->registry->resolve('Unknown\Entity');
    }

    public function testExceptionMessageContainsEntityType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown\Entity');

        $this->registry->resolve('Unknown\Entity');
    }

    public function testRegisterOverwritesPreviousRepository(): void
    {
        $firstRepo = $this->createMock(TaggableRepositoryInterface::class);
        $firstRepo->method('getEntityType')->willReturn('Modules\Article\Entities\Article');

        $secondRepo = $this->createMock(TaggableRepositoryInterface::class);
        $secondRepo->method('getEntityType')->willReturn('Modules\Article\Entities\Article');

        $this->registry->register($firstRepo);
        $this->registry->register($secondRepo);

        $this->assertSame($secondRepo, $this->registry->resolve('Modules\Article\Entities\Article'));
    }
}
