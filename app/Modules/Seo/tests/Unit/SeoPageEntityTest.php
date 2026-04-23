<?php

declare(strict_types=1);

namespace Modules\Seo\tests\Unit;

use App\ValueObjects\Id;
use Modules\Seo\Entities\SeoPage;
use Modules\Seo\Entities\SeoPageTranslation;
use Tests\TestCase;

class SeoPageEntityTest extends TestCase
{
    public function testTranslateReturnsExactLocale()
    {
        $translationEn = new SeoPageTranslation(
            seoPageId: new Id('1'),
            locale: 'en',
            seoTitle: 'EN',
            seoDescription: '',
            seoKeywords: '',
            seoOgImage: null
        );

        $translationUk = new SeoPageTranslation(
            seoPageId: new Id('1'),
            locale: 'uk',
            seoTitle: 'UK',
            seoDescription: '',
            seoKeywords: '',
            seoOgImage: null
        );

        $seoPage = new SeoPage(
            translations: [$translationEn, $translationUk]
        );

        $result = $seoPage->translate('en');

        $this->assertSame($translationEn, $result);
    }

    public function testTranslateReturnsFallbackWhenLocaleMissing()
    {
        $translationUk = new SeoPageTranslation(
            seoPageId: new Id('1'),
            locale: 'uk',
            seoTitle: 'UK',
            seoDescription: '',
            seoKeywords: '',
            seoOgImage: null
        );

        $seoPage = new SeoPage(
            translations: [$translationUk]
        );

        $result = $seoPage->translate('en', 'uk');

        $this->assertSame($translationUk, $result);
    }

    public function testTranslateReturnsNullWhenNoLocaleAndNoFallback()
    {
        $translationDe = new SeoPageTranslation(
            seoPageId: new Id('1'),
            locale: 'de',
            seoTitle: 'DE',
            seoDescription: '',
            seoKeywords: '',
            seoOgImage: null
        );

        $seoPage = new SeoPage(
            translations: [$translationDe]
        );

        $result = $seoPage->translate('en', 'uk');

        $this->assertNull($result);
    }
}
