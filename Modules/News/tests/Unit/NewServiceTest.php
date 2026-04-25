<?php
declare(strict_types=1);

namespace Modules\News\tests\Unit;

use App\ValueObjects\Id;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Modules\News\Entities\News;
use Modules\News\Entities\NewsTranslation;
use Modules\News\Filter\NewsFilter;
use Modules\News\Repositories\Contracts\NewsRepositoryInterface;
use Modules\News\Services\NewsService;
use Modules\News\ValueObjects\NewsText;
use Modules\News\ValueObjects\NewsTitle;
use Tests\TestCase;

class NewServiceTest extends TestCase
{
    private NewsRepositoryInterface $repository;
    private NewsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(NewsRepositoryInterface::class);
        $this->service = new NewsService($this->repository);
    }

    private function makeNews(string $id = 'news-uuid'): News
    {
        return new News(
            id: new Id($id),
            status: true,
            slug: 'test-news',
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
    }

    private function makeTranslationData(array $override = []): array
    {
        return array_merge([
            'title'           => 'Test title',
            'text'            => 'Test text',
            'seo_title'       => 'SEO title',
            'seo_description' => 'SEO description',
            'seo_keywords'    => 'keywords',
            'seo_og_image'    => null,
        ], $override);
    }

    private function makeCreateData(array $override = []): array
    {
        return array_merge([
            'status'       => true,
            'slug'         => 'test-news',
            'translations' => [
                'uk' => $this->makeTranslationData(),
                'en' => $this->makeTranslationData(['title' => 'Test title EN']),
            ],
        ], $override);
    }

    public function testGetNewByIdReturnsNews(): void
    {
        $id = new Id('news-uuid');
        $news = $this->makeNews();

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($news);

        $result = $this->service->getNewById($id);

        $this->assertSame($news, $result);
    }

    public function testGetNewByIdReturnsNullWhenNotFound(): void
    {
        $this->repository
            ->method('get')
            ->willReturn(null);

        $result = $this->service->getNewById(new Id('non-existent'));

        $this->assertNull($result);
    }

    public function testGetNewBySlugReturnsNews(): void
    {
        $news = $this->makeNews();

        $this->repository
            ->expects($this->once())
            ->method('getBySlug')
            ->with('test-news')
            ->willReturn($news);

        $result = $this->service->getNewBySlug('test-news');

        $this->assertSame($news, $result);
    }

    public function testGetNewBySlugReturnsNullWhenNotFound(): void
    {
        $this->repository
            ->method('getBySlug')
            ->willReturn(null);

        $result = $this->service->getNewBySlug('non-existent');

        $this->assertNull($result);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $filter = new NewsFilter();
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->with($filter)
            ->willReturn($paginator);

        $result = $this->service->getAll($filter);

        $this->assertSame($paginator, $result);
    }

    public function testCreateSavesNewsAndReturnsId(): void
    {
        $this->repository
            ->method('save')
            ->willReturn('new-news-uuid');

        $this->repository
            ->method('saveTranslation');

        $result = $this->service->create($this->makeCreateData());

        $this->assertEquals('new-news-uuid', $result);
    }

    public function testCreateSavesTranslationsForEachLocale(): void
    {
        $this->repository
            ->method('save')
            ->willReturn('new-news-uuid');

        $this->repository
            ->expects($this->exactly(2))
            ->method('saveTranslation');

        $this->service->create($this->makeCreateData());
    }

    public function testCreateSyncsTagsWhenProvided(): void
    {
        $tags = ['tag-uuid-1', 'tag-uuid-2'];

        $this->repository
            ->method('save')
            ->willReturn('new-news-uuid');

        $this->repository
            ->method('saveTranslation');

        $this->repository
            ->expects($this->once())
            ->method('syncTags')
            ->with(
                $this->isInstanceOf(Id::class),
                $tags
            );

        $this->service->create(array_merge($this->makeCreateData(), ['tags' => $tags]));
    }

    public function testCreateDoesNotSyncTagsWhenNotProvided(): void
    {
        $this->repository
            ->method('save')
            ->willReturn('new-news-uuid');

        $this->repository
            ->method('saveTranslation');

        $this->repository
            ->expects($this->never())
            ->method('syncTags');

        $this->service->create($this->makeCreateData());
    }

    public function testCreateStoresUploadedSeoOgImage(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('og.jpg');

        $this->repository
            ->method('save')
            ->willReturn('new-news-uuid');

        $this->repository
            ->expects($this->exactly(2))
            ->method('saveTranslation')
            ->with($this->callback(function (NewsTranslation $translation) {
                if ($translation->locale === 'uk') {
                    return $translation->seoOgImage !== null;
                }
                return true;
            }));

        $data = $this->makeCreateData();
        $data['translations']['uk']['seo_og_image'] = $file;

        $this->service->create($data);
    }

    public function testUpdateSavesNewsWithNewData(): void
    {
        $id = new Id('news-uuid');
        $news = $this->makeNews();

        $this->repository
            ->method('get')
            ->willReturn($news);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(News $n) => $n->slug === 'updated-slug'));

        $this->repository
            ->method('getTranslationsForNews')
            ->willReturn([]);

        $this->repository
            ->method('saveTranslation');

        $data = $this->makeCreateData(['slug' => 'updated-slug']);
        $this->service->update($id, $data);
    }

    public function testUpdateKeepsOldSlugWhenNotProvided(): void
    {
        $id = new Id('news-uuid');
        $news = $this->makeNews();

        $this->repository
            ->method('get')
            ->willReturn($news);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(News $n) => $n->slug === 'test-news'));

        $this->repository
            ->method('getTranslationsForNews')
            ->willReturn([]);

        $this->repository
            ->method('saveTranslation');

        $data = $this->makeCreateData();
        unset($data['slug']);

        $this->service->update($id, $data);
    }

    public function testUpdateKeepsExistingSeoOgImageWhenNotUploaded(): void
    {
        $id = new Id('news-uuid');
        $news = $this->makeNews();

        $existingTranslation = new NewsTranslation(
            newId: $id,
            locale: 'uk',
            title: new NewsTitle('Old title'),
            text: new NewsText('Old text'),
            seoOgImage: 'news/seo/uk/existing.jpg',
        );

        $this->repository
            ->method('get')
            ->willReturn($news);

        $this->repository
            ->method('save');

        $this->repository
            ->method('getTranslationsForNews')
            ->willReturn(['uk' => $existingTranslation, 'en' => null]);

        $this->repository
            ->expects($this->exactly(2))
            ->method('saveTranslation')
            ->with($this->callback(function (NewsTranslation $translation) {
                if ($translation->locale === 'uk') {
                    return $translation->seoOgImage === 'news/seo/uk/existing.jpg';
                }
                return true;
            }));

        $this->service->update($id, $this->makeCreateData());
    }

    public function testUpdateDeletesOldSeoOgImageWhenNewUploaded(): void
    {
        Storage::fake('public');

        $id = new Id('news-uuid');
        $news = $this->makeNews();
        $file = UploadedFile::fake()->image('new-og.jpg');

        $existingTranslation = new NewsTranslation(
            newId: $id,
            locale: 'uk',
            title: new NewsTitle('Old title'),
            text: new NewsText('Old text'),
            seoOgImage: 'news/seo/uk/old.jpg',
        );

        Storage::disk('public')->put('news/seo/uk/old.jpg', 'content');

        $this->repository
            ->method('get')
            ->willReturn($news);

        $this->repository
            ->method('save');

        $this->repository
            ->method('getTranslationsForNews')
            ->willReturn(['uk' => $existingTranslation, 'en' => null]);

        $this->repository
            ->method('saveTranslation');

        $data = $this->makeCreateData();
        $data['translations']['uk']['seo_og_image'] = $file;

        $this->service->update($id, $data);

        Storage::disk('public')->assertMissing('news/seo/uk/old.jpg');
    }

    public function testUpdateSyncsTagsWhenProvided(): void
    {
        $id = new Id('news-uuid');
        $tags = ['tag-uuid-1'];

        $this->repository
            ->method('get')
            ->willReturn($this->makeNews());

        $this->repository
            ->method('save');

        $this->repository
            ->method('getTranslationsForNews')
            ->willReturn([]);

        $this->repository
            ->method('saveTranslation');

        $this->repository
            ->expects($this->once())
            ->method('syncTags')
            ->with($this->isInstanceOf(Id::class), $tags);

        $this->service->update($id, array_merge($this->makeCreateData(), ['tags' => $tags]));
    }

    public function testDeleteCallsRepository(): void
    {
        $id = new Id('news-uuid');

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id);

        $this->service->delete($id);
    }

    public function testSyncTagsCallsRepository(): void
    {
        $id = new Id('news-uuid');
        $tags = ['tag-uuid-1', 'tag-uuid-2'];

        $this->repository
            ->expects($this->once())
            ->method('syncTags')
            ->with($id, $tags);

        $this->service->syncTags($id, $tags);
    }
}
