<?php

namespace Modules\Seo\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Seo\Services\SeoService;


/**
 * @method static SeoService setTitle(string $title)
 * @method static SeoService seoForStaticPage(string $url)
 * @method static SeoService setDescription(string $description)
 * @method static SeoService setCanonical(string $url)
 * @method static SeoService setOg(string $property, string $content)
 * @method static string generate()
 *
 * @see SeoService
 */
class Seo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SeoService::class;
    }
}
