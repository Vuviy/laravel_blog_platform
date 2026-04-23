<?php

declare(strict_types=1);

namespace Modules\Seo\tests\Integration;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Seo\Entities\SeoPage;
use Modules\Seo\Entities\SeoPageTranslation;
use Modules\Seo\Repositories\SeoPageRepository;

use Tests\TestCase;

class SeoPageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SeoPageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SeoPageRepository();
    }

    public function testSaveCreatesNewSeoPage()
    {
        $seoPage = new SeoPage(url: 'test-url');

        $id = $this->repository->save($seoPage);

        $this->assertDatabaseHas('seo_pages', [
            'id' => $id,
            'url' => 'test-url',
        ]);
    }

    public function testSaveUpdatesExistingSeoPage()
    {
        $id = (string) $this->repository->nextId();

        DB::table('seo_pages')->insert([
            'id' => $id,
            'url' => 'old-url',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $seoPage = new SeoPage(
            id: new Id($id),
            url: 'new-url'
        );

        $this->repository->save($seoPage);

        $this->assertDatabaseHas('seo_pages', [
            'id' => $id,
            'url' => 'new-url',
        ]);
    }

    public function testGetReturnsSeoPageWithTranslations()
    {
        $id = (string) $this->repository->nextId();

        DB::table('seo_pages')->insert([
            'id' => $id,
            'url' => 'test-url',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('seo_pages_translations')->insert([
            'id' => (string) $this->repository->nextId(),
            'seo_page_id' => $id,
            'locale' => 'en',
            'seo_title' => 'title',
            'seo_description' => 'desc',
            'seo_keywords' => 'kw',
            'seo_og_image' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->repository->get(new Id($id));

        $this->assertNotNull($result);
        $this->assertEquals('test-url', $result->url);
        $this->assertArrayHasKey('en', $result->translations);
    }

    public function testGetByUrlReturnsSeoPage()
    {
        $id = (string) $this->repository->nextId();

        DB::table('seo_pages')->insert([
            'id' => $id,
            'url' => 'test-url',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->repository->getByUrl('test-url');

        $this->assertNotNull($result);
        $this->assertEquals('test-url', $result->url);
    }

    public function testDeleteRemovesSeoPage()
    {
        $id = (string) $this->repository->nextId();

        DB::table('seo_pages')->insert([
            'id' => $id,
            'url' => 'test-url',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->repository->delete(new Id($id));

        $this->assertDatabaseMissing('seo_pages', [
            'id' => $id,
        ]);
    }

    public function testGetTranslationsForSeoPages()
    {
        $id = (string) $this->repository->nextId();

        DB::table('seo_pages')->insert([
            'id' => $id,
            'url' => 'test-url',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('seo_pages_translations')->insert([
            'id' => (string) $this->repository->nextId(),
            'seo_page_id' => $id,
            'locale' => 'en',
            'seo_title' => 'title',
            'seo_description' => 'desc',
            'seo_keywords' => 'kw',
            'seo_og_image' => 'img.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->repository->getTranslationsForSeoPages($id);

        $this->assertArrayHasKey('en', $result);
        $this->assertEquals('title', $result['en']->seoTitle);
    }

    public function testSaveTranslationUpsertsRecord()
    {
        $seoPageId = (string) $this->repository->nextId();

        DB::table('seo_pages')->insert([
            'id' => $seoPageId,
            'url' => 'test-url',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $translation = new SeoPageTranslation(
            seoPageId: new Id($seoPageId),
            locale: 'en',
            seoTitle: 'title',
            seoDescription: 'desc',
            seoKeywords: 'kw',
            seoOgImage: null,
        );

        $this->repository->saveTranslation($translation);

        $this->assertDatabaseHas('seo_pages_translations', [
            'seo_page_id' => $seoPageId,
            'locale' => 'en',
            'seo_title' => 'title',
        ]);
    }

    public function testGetAllReturnsPaginatorWithEntities()
    {
        DB::table('seo_pages')->insert([
            'id' => (string) $this->repository->nextId(),
            'url' => 'test-url',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->repository->getAll();

        $this->assertInstanceOf(
            \Illuminate\Pagination\LengthAwarePaginator::class,
            $result
        );

        $this->assertNotEmpty($result->items());
    }
}
