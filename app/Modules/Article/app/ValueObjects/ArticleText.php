<?php

namespace Modules\Artice\app\ValueObjects;

class ArticleText
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
