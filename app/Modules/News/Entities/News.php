<?php
declare(strict_types=1);

namespace Modules\News\Entities;

use App\ValueObjects\Id;
use Modules\Article\Entities\ArticleTranslation;


final class News
{
    public function __construct(
        public ?Id              $id = null,
        public ?string              $slug = null,
        public bool                $status = false,
        public ?\DateTimeImmutable $created_at = null,
        public ?\DateTimeImmutable $updated_at = null,
        public array               $tags = [],
        public array               $translations = [],
        public array               $comments = [],
    )
    {
    }

    public function translate(string $locale, string $fallback = 'uk'): ?NewsTranslation
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
