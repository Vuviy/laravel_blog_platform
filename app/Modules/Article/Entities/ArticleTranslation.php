<?php

namespace Modules\Article\Entities;

use App\ValueObjects\Id;
use Modules\Article\ValueObjects\ArticleText;
use Modules\Article\ValueObjects\ArticleTitle;

class ArticleTranslation
{
    public function __construct(
        public ?Id $id = null,
        public ?Id            $articleId = null,
        public string                $locale = 'uk',
        public ?ArticleTitle         $title = null,
        public ?ArticleText          $text = null,

        //Seo
        public ?string $seoTitle = null,
        public ?string $seoDescription = null,
        public ?string $seoKeywords = null,
        public ?string $seoOgImage = null,
    ) {}
}
