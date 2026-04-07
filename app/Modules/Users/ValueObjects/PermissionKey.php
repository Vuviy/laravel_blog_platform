<?php

namespace Modules\Users\ValueObjects;

class PermissionKey
{
    public function __construct(private string $value)
    {
        if (trim($this->value) === '' || !preg_match('/^[a-zA-Z]+\.[a-zA-Z]+$/', $value)) {
            throw new \InvalidArgumentException('key cannot be empty or this format');
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
