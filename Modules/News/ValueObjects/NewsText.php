<?php

declare(strict_types=1);

namespace Modules\News\ValueObjects;

class NewsText
{
    private ?string $value;

    public function __construct(?string $text = null)
    {
        $this->value = $text;

        if (trim($this->value) === '') {
            throw new \InvalidArgumentException('Text cannot be empty');
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
