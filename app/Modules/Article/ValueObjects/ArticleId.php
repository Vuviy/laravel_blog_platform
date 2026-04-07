<?php

namespace Modules\Article\ValueObjects;

class ArticleId
{
    private ?string $value;

    public function __construct(?string $id = null)
    {
        $this->value = $id ?: uniqid();

        if (trim($this->value) === '') {
            throw new \InvalidArgumentException('Id cannot be empty');
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
