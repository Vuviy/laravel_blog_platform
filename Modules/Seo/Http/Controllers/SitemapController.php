<?php

namespace Modules\Seo\Http\Controllers;

use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;
use Modules\News\Repositories\Contracts\NewsRepositoryInterface;
use Modules\Seo\Repositories\Contracts\SeoPageRepositoryInterface;

class SitemapController
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
        private NewsRepositoryInterface    $newsRepository,
        private SeoPageRepositoryInterface    $seoPageRepository,
    )
    {
    }

    public function sitemapForm()
    {
        $content = '';
        if (is_file(config('seo.sitemap_path')))
        {
            $content = file_get_contents(config('seo.sitemap_path'));
        }
        return view('seo::admin.sitemap-form', compact('content'));
    }

    public function generateSitemap()
    {
        $locales = config('app.available_locales');

        $urls = collect();

        $articles = $this->articleRepository->getAllPublished();
        foreach ($articles as $article) {

            foreach ($locales as $locale) {
                $urls->push([
                    'loc' => url(sprintf('%s/articles/%s', $locale, $article->slug)),
                    'lastmod' => $article->updated_at->format('Y-m-d H:i:s'),
                    'changefreq' => 'monthly',
                    'priority' => '0.8',
                ]);
            }
        }



        $news = $this->newsRepository->getAllPublished();
        foreach ($news as $item) {
            foreach ($locales as $locale) {
                $urls->push([
                    'loc' => url(sprintf('%s/news/%s', $locale, $item->slug)),
                    'lastmod' => $item->updated_at->format('Y-m-d H:i:s'),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ]);
            }
        }


        $seoPages = $this->seoPageRepository->getAll();
        foreach ($seoPages as $item) {
            foreach ($locales as $locale) {
                $urls->push([
                    'loc' => url(sprintf('%s/%s', $locale, trim($item->url, '/'))),
                    'lastmod' => $item->updated_at->format('Y-m-d H:i:s'),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ]);
            }
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        foreach ($urls as $url) {
            $urlElement = $xml->addChild('url');
            $urlElement->addChild('loc', $url['loc']);
            $urlElement->addChild('lastmod', $url['lastmod']);
            $urlElement->addChild('changefreq', $url['changefreq']);
            $urlElement->addChild('priority', $url['priority']);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $dom->save(config('seo.sitemap_path'));
        return redirect()->back()->with(['message' => 'sitemap.xml згенеровано']);

    }

    public function index()
    {
        $path = config('seo.sitemap_path');

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, ['Content-Type' => 'application/xml']);
    }
}
