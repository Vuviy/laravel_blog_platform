<?php

declare(strict_types=1);

namespace Modules\Seo\tests\Unit;

use App\ValueObjects\Id;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Modules\Seo\Entities\SeoPage;
use Modules\Seo\Entities\SeoPageTranslation;
use Modules\Seo\Repositories\Contracts\SeoPageRepositoryInterface;
use Modules\Seo\Services\SeoPageService;
use Tests\TestCase;

class SeoPageServiceTest extends TestCase
{
    private $repository;
    private SeoPageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(SeoPageRepositoryInterface::class);

        $this->service = new SeoPageService($this->repository);
    }

    public function testGetSeoPageById()
    {
        $id = new Id('1');
        $seoPage = new SeoPage(url: 'test');

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($seoPage);

        $result = $this->service->getSeoPageById($id);

        $this->assertSame($seoPage, $result);
    }

    public function testGetAll()
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($paginator);

        $result = $this->service->getAll();

        $this->assertSame($paginator, $result);
    }

    public function testCreateWithImage()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $data = [
            'url' => 'test-url',
            'translations' => [
                'en' => [
                    'seo_title' => 'title',
                    'seo_description' => 'desc',
                    'seo_keywords' => 'kw',
                    'seo_og_image' => $file,
                ]
            ]
        ];

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturn('123');

        $this->repository
            ->expects($this->once())
            ->method('saveTranslation')
            ->with($this->callback(function ($translation) {
                return $translation->seoOgImage !== null
                    && $translation->locale  === 'en';
            }));

        $result = $this->service->create($data);

        $this->assertEquals('123', $result);
    }

    public function testCreateWithoutImage()
    {
        $data = [
            'url' => 'test-url',
            'translations' => [
                'en' => [
                    'seo_title' => 'title',
                    'seo_description' => 'desc',
                    'seo_keywords' => 'kw',
                ]
            ]
        ];

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturn('123');

        $this->repository
            ->expects($this->once())
            ->method('saveTranslation')
            ->with($this->callback(function ($translation) {
                return $translation->seoOgImage === null;
            }));

        $this->service->create($data);
    }

    public function testUpdateWithNewImageDeletesOld()
    {
        Storage::fake('public');

        $id = new Id('1');

        $existingPage = new SeoPage(id: $id, url: 'old');

        $existingTranslation = new SeoPageTranslation(
            seoPageId: $id,
            locale: 'en',
            seoTitle: 'old',
            seoDescription: 'old',
            seoKeywords: 'old',
            seoOgImage: 'old/path.jpg'
        );

        $file = UploadedFile::fake()->image('new.jpg');

        $this->repository
            ->method('get')
            ->willReturn($existingPage);

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->repository
            ->method('getTranslationsForSeoPages')
            ->willReturn(['en' => $existingTranslation]);

        $this->repository
            ->expects($this->once())
            ->method('saveTranslation')
            ->with($this->callback(function ($translation) {
                return $translation->seoOgImage !== 'old/path.jpg';
            }));

        Storage::disk('public')->put('old/path.jpg', 'dummy');

        $this->service->update($id, [
            'translations' => [
                'en' => [
                    'seo_title' => 'new',
                    'seo_description' => 'new',
                    'seo_keywords' => 'new',
                    'seo_og_image' => $file,
                ]
            ]
        ]);

        Storage::disk('public')->assertMissing('old/path.jpg');
    }

    public function testUpdateWithoutNewImageKeepsOld()
    {
        $id = new Id('1');

        $existingPage = new SeoPage(id: $id, url: 'old');

        $existingTranslation = new SeoPageTranslation(
            seoPageId: $id,
            locale: 'en',
            seoTitle: 'old',
            seoDescription: 'old',
            seoKeywords: 'old',
            seoOgImage: 'old/path.jpg'
        );

        $this->repository
            ->method('get')
            ->willReturn($existingPage);

        $this->repository
            ->expects($this->once())
            ->method('save');


        $this->repository
            ->method('getTranslationsForSeoPages')
            ->willReturn(['en' => $existingTranslation]);


        $this->repository
            ->expects($this->once())
            ->method('saveTranslation')
            ->with($this->callback(function ($translation) {
                return $translation->seoOgImage === 'old/path.jpg';
            }));


        $this->service->update($id, [
            'translations' => [
                'en' => [
                    'seo_title' => 'new',
                    'seo_description' => 'new',
                    'seo_keywords' => 'new',
                ]
            ]
        ]);
    }

    public function testDelete()
    {
        $id = new Id('1');

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id);

        $this->service->delete($id);
    }
}
