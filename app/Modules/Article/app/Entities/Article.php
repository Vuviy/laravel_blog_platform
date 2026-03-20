<?php

namespace Modules\Article\app\Entities;

use App\ValueObjects\ArticleId;
use App\ValueObjects\ArticleText;
use App\ValueObjects\ArticleTitle;

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
