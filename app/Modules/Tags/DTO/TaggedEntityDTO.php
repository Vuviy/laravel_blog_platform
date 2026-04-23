<?php

namespace Modules\Tags\DTO;

class TaggedEntityDTO
{
    public function __construct(
        public readonly string             $id,
        public readonly string             $type,
        public readonly string             $title,
        public readonly string             $text,
        public readonly string             $url,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}
