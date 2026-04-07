<?php

namespace Modules\Article\Entities;

use App\ValueObjects\Id;


final class Article
{
    public function __construct(
        public ?Id              $id = null,
        public bool                $status = false,
        public ?\DateTimeImmutable $created_at = null,
        public ?\DateTimeImmutable $updated_at = null,
        public array               $tags = [],
        public array               $translations = [],
        public array               $comments = [],
    )
    {
    }

    public function translate(string $locale, string $fallback = 'uk'): ?ArticleTranslation
    {

        foreach ($this->translations as $translation) {
            if ($translation->locale === $locale) {

                return $translation;
            }

        }

        foreach ($this->translations as $translation) {
            if ($translation->locale === $fallback) {
                return $translation;
            }
        }

        return null;
    }

}
