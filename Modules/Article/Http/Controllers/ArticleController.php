<?php
declare(strict_types=1);

namespace Modules\Article\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Article\Filter\ArticleFilter;
use Modules\Article\Services\ArticleService;
use Modules\Seo\Facades\Seo;

class ArticleController extends Controller
{
    public function __construct(
        private ArticleService $service
    ) {}

    public function index(Request $request)
    {
        $request->merge(["status"=>1]);

        $filter = ArticleFilter::fromRequest($request);

        $articles = $this->service->getAll($filter);
        $title = __('common.articles');
        return view('article::index', compact('articles', 'title', 'filter'));


    }

    public function show(string $slug)
    {

        $article = $this->service->getArticleBySlug($slug);

        Seo::setTitle($article->translate(app()->currentLocale())->seoTitle ?? $article->translate(app()->currentLocale())->title->getValue())
            ->setDescription($article->translate(app()->currentLocale())->seoDescription ?? mb_substr( $article->translate(app()->currentLocale())->text->getValue(), 0, 160))
            ->setCanonical(route('articles.show',
                [
                    'locale' =>  app()->currentLocale(),
                    'slug' => $article->slug
                ]
            ))
            ->setOg('image',  $article->translate(app()->currentLocale())->seoOgImage ? asset('storage/' . $article->translate(app()->currentLocale())->seoOgImage) : '')
            ->setOg('type', 'article');

        return view('article::show', compact('article'));

    }

}
