<?php

namespace Modules\Seo\Entities;

use App\ValueObjects\Id;
use Modules\Article\ValueObjects\ArticleText;
use Modules\Article\ValueObjects\ArticleTitle;

class SeoPageTranslation
{
    public function __construct(
        public ?Id     $id = null,
        public ?Id     $seoPageId = null,
        public string  $locale = 'uk',
        public ?string $seoTitle = null,
        public ?string $seoDescription = null,
        public ?string $seoKeywords = null,
        public ?string $seoOgImage = null,
    )
    {
    }
}
