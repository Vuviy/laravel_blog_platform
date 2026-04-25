<?php

namespace App\ValueObjects;

class Id
{
    public function __construct(private string $value)
    {
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
