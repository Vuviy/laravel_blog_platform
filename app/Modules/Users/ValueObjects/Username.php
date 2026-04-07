<?php

namespace Modules\Users\ValueObjects;

class Username
{
    public function __construct( private string $value)
    {
        if (trim($this->value) === '' || strlen($this->value) <= 2) {
            throw new \InvalidArgumentException('username cannot be empty');
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
