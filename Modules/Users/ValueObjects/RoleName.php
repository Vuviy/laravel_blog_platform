<?php

namespace Modules\Users\ValueObjects;

class RoleName
{
    public function __construct(private string $value)
    {
        if (trim($this->value) === '') {
            throw new \InvalidArgumentException('rolename cannot be empty');
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
