<?php
declare(strict_types=1);

namespace Modules\News\Http\Controllers;

use Illuminate\Http\Request;
use Modules\News\Filter\NewsFilter;
use Modules\News\Services\NewsService;
use Modules\Seo\Facades\Seo;

class NewsController
{
    public function __construct(
        private NewsService $service
    ) {}

    public function index(Request $request)
    {
        $request->merge(["status"=>1]);
        $filter = NewsFilter::fromRequest($request);
        $news = $this->service->getAll($filter);
        $title = __('common.news');
        return view('news::index', compact('news', 'title', 'filter'));


    }

    public function show(string $slug)
    {
        $new = $this->service->getNewBySlug($slug);

        Seo::setTitle($new->translate(app()->currentLocale())->seoTitle ?? $new->translate(app()->currentLocale())->title->getValue())
            ->setDescription($new->translate(app()->currentLocale())->seoDescription ?? mb_substr( $new->translate(app()->currentLocale())->text->getValue(), 0, 160))
            ->setCanonical(route('news.show',
                [
                    'locale' =>  app()->currentLocale(),
                    'slug' => $new->slug
                ]
            ))
            ->setOg('image',  $new->translate(app()->currentLocale())->seoOgImage ? asset('storage/' . $new->translate(app()->currentLocale())->seoOgImage) : '')
            ->setOg('type', 'news');

        return view('news::show', compact('new'));

    }
}
