<?php

namespace Modules\Seo\Entities\Contracts;

use Modules\Seo\Entities\SeoPageTranslation;

interface SeoPageInterface
{
    public function translate(string $locale, string $fallback = 'uk'): ?SeoPageTranslation;
}
