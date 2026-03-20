<?php

namespace Modules\Article\Entities;

use Modules\Article\ValueObjects\ArticleId;
use Modules\Article\ValueObjects\ArticleText;
use Modules\Article\ValueObjects\ArticleTitle;

final class Article
{
    public function __construct(
        public ?ArticleId $id = null,
        public ?ArticleTitle $title = null,
        public ?ArticleText $text = null,
        public ?\DateTimeImmutable $created_at = null,
        public ?\DateTimeImmutable $updated_at = null,
    )
    {
    }

}
