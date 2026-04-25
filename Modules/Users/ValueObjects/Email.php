<?php

namespace Modules\Users\ValueObjects;

class Email
{
    public function __construct( private string $value)
    {
        $email = trim($value);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('Invalid email: %s', $email));
        }

        $this->value = $email;
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
