<?php

namespace Modules\Seo\Services;

use Modules\Seo\Repositories\Contracts\SeoPageRepositoryInterface;

class SeoService
{
    protected string $title = '';
    protected string $description = '';
    protected string $keywords = '';
    protected ?string $canonical = null;
    protected array $og = [];

    public function __construct(private SeoPageRepositoryInterface $seoPageRepository)
    {
    }
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function seoForStaticPage(string $url): static
    {
        $seoPage = $this->seoPageRepository->getByUrl($url);

        $this->title = $seoPage->translate(app()->currentLocale())->seoTitle;
        $this->description = $seoPage->translate(app()->currentLocale())->seoDescription;
        $this->keywords = $seoPage->translate(app()->currentLocale())->seoKeywords;
        $this->canonical = url(app()->currentLocale() . '/' . ltrim($url, '/'));
        $this->setOg('image', $seoPage->translate(app()->currentLocale())->seoOgImage ? $seoPage->translate(app()->currentLocale())->seoOgImage : '');
        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setCanonical(string $url): static
    {
        $this->canonical = $url;
        return $this;
    }

    public function setOg(string $property, string $content): static
    {
        $this->og[$property] = $content;
        return $this;
    }

    public function generate(): string
    {
        $html = '';

        if ($this->title) {
            $html .= "<title>{$this->title}</title>\n";
            $html .= "<meta property=\"og:title\" content=\"{$this->title}\">\n";
        }

        if ($this->description) {
            $html .= "<meta name=\"description\" content=\"{$this->description}\">\n";
            $html .= "<meta property=\"og:description\" content=\"{$this->description}\">\n";
        }

        if ($this->canonical) {
            $html .= "<link rel=\"canonical\" href=\"{$this->canonical}\">\n";
        }


        foreach ($this->og as $property => $content) {
            $html .= "<meta property=\"og:{$property}\" content=\"{$content}\">\n";
        }

        return $html;
    }
}
