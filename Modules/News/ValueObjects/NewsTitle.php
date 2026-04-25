<?php

namespace Modules\News\ValueObjects;

class NewsTitle
{
    private ?string $value;

    public function __construct(?string $title = null)
    {
        $this->value = $title;

        if (trim($this->value) === '') {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
