<?php

declare(strict_types=1);

namespace Modules\News\Entities;

use App\ValueObjects\Id;
use Modules\News\ValueObjects\NewsText;
use Modules\News\ValueObjects\NewsTitle;


class NewsTranslation
{
    public function __construct(
        public ?Id $id = null,
        public ?Id            $newId = null,
        public string                $locale = 'uk',
        public ?NewsTitle         $title = null,
        public ?NewsText          $text = null,

        //Seo
        public ?string $seoTitle = null,
        public ?string $seoDescription = null,
        public ?string $seoKeywords = null,
        public ?string $seoOgImage = null,
    ) {}
}
