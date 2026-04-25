<?php
declare(strict_types=1);

namespace Modules\Seo\Entities;

use App\ValueObjects\Id;
use Modules\Seo\Entities\Contracts\SeoPageInterface;


class SeoPage implements SeoPageInterface
{
    public function __construct(
        public ?Id              $id = null,
        public ?string              $url = null,
        public array               $translations = [],
        public ?\DateTimeImmutable $created_at = null,
        public ?\DateTimeImmutable $updated_at = null,
    )
    {
    }

    public function translate(string $locale, string $fallback = 'uk'): ?SeoPageTranslation
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
