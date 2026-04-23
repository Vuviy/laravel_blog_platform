<?php

declare(strict_types=1);

namespace Modules\Seo\tests\Unit;

use Modules\Seo\Entities\Contracts\SeoPageInterface;
use Modules\Seo\Entities\SeoPage;
use Modules\Seo\Entities\SeoPageTranslation;
use Modules\Seo\Repositories\Contracts\SeoPageRepositoryInterface;
use Modules\Seo\Services\SeoService;

use stdClass;
use Tests\TestCase;

class SeoServiceTest extends TestCase
{
    private SeoPageRepositoryInterface $repository;
    private SeoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->repository = $this->createMock(SeoPageRepositoryInterface::class);
        $this->service = new SeoService($this->repository);
    }

    public function testSettersAreFluent(): void
    {
        $result = $this->service
            ->setTitle('t')
            ->setDescription('d')
            ->setCanonical('c')
            ->setOg('image', 'img');

        $this->assertSame($this->service, $result);
    }

    public function testSeoForStaticPageSetsData(): void
    {
        $seoPageTranslation = $this->createMock(SeoPageTranslation::class);

        $seoPageTranslation->seoTitle = 'Title';
        $seoPageTranslation->seoDescription = 'Desc';
        $seoPageTranslation->seoKeywords = 'kw';
        $seoPageTranslation->seoOgImage = 'img.jpg';

        $seoPage = $this->createMock(SeoPageInterface::class);

        $seoPage->method('translate')->willReturn($seoPageTranslation);

        $this->repository
            ->expects($this->once())
            ->method('getByUrl')
            ->with('/test')
            ->willReturn($seoPage);

        $this->service->seoForStaticPage('/test');

        $html = $this->service->generate();

        $this->assertStringContainsString('<title>Title</title>', $html);
        $this->assertStringContainsString('name="description" content="Desc"', $html);
        $this->assertStringContainsString('og:image" content="img.jpg"', $html);
        $this->assertStringContainsString('rel="canonical"', $html);
    }

    public function testSeoForStaticPageHandlesEmptyOgImage(): void
    {
        $seoPageTranslation = $this->createMock(SeoPageTranslation::class);

        $seoPageTranslation->seoTitle = 'Title';
        $seoPageTranslation->seoDescription = 'Desc';
        $seoPageTranslation->seoKeywords = 'kw';
        $seoPageTranslation->seoOgImage = null;

        $seoPage = $this->createMock(SeoPageInterface::class);

        $seoPage->method('translate')->willReturn($seoPageTranslation);

        $this->repository->method('getByUrl')->willReturn($seoPage);

        $this->service->seoForStaticPage('/test');

        $html = $this->service->generate();

        $this->assertStringContainsString('og:image" content=""', $html);
    }

    public function testGenerateWithOnlyTitle(): void
    {
        $html = $this->service
            ->setTitle('Title')
            ->generate();

        $this->assertStringContainsString('<title>Title</title>', $html);
        $this->assertStringContainsString('og:title', $html);
        $this->assertStringNotContainsString('description', $html);
    }

    public function testGenerateWithDescription(): void
    {
        $html = $this->service
            ->setDescription('Desc')
            ->generate();

        $this->assertStringContainsString('name="description" content="Desc"', $html);
        $this->assertStringContainsString('og:description', $html);
    }

    public function testGenerateWithCanonical(): void
    {
        $html = $this->service
            ->setCanonical('https://test.com')
            ->generate();

        $this->assertStringContainsString('<link rel="canonical" href="https://test.com">', $html);
    }

    public function testGenerateWithOg(): void
    {
        $html = $this->service
            ->setOg('image', 'img.jpg')
            ->setOg('type', 'article')
            ->generate();

        $this->assertStringContainsString('og:image" content="img.jpg"', $html);
        $this->assertStringContainsString('og:type" content="article"', $html);
    }

    public function testGenerateFull(): void
    {
        $html = $this->service
            ->setTitle('T')
            ->setDescription('D')
            ->setCanonical('C')
            ->setOg('image', 'I')
            ->generate();

        $this->assertStringContainsString('<title>T</title>', $html);
        $this->assertStringContainsString('name="description" content="D"', $html);
        $this->assertStringContainsString('rel="canonical" href="C"', $html);
        $this->assertStringContainsString('og:image" content="I"', $html);
    }
}
