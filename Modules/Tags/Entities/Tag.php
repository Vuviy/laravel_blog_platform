<?php

namespace Modules\Tags\Entities;

use App\ValueObjects\Id;
use Modules\Tags\ValueObjects\TagTitle;

final class Tag
{
    public function __construct(
        public ?Id              $id = null,
        public ?TagTitle           $title = null,
        public ?\DateTimeImmutable $created_at = null,
        public ?\DateTimeImmutable $updated_at = null,
    )
    {
    }

}
