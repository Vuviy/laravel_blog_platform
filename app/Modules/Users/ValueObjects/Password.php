<?php

namespace Modules\Users\ValueObjects;
class Password
{

    private string $hash;
    public function __construct(){}

    public static function fromHash(string $hash): self
    {
        if ($hash === '') {
            throw new \InvalidArgumentException('Empty password hash provided.');
        }
        $instance = new self();
        $instance->hash = $hash;
        return $instance;
    }

    /**
     * create from plain and hash
     *
     * @param string $plain
     * @return self
     */
    public static function fromPlain(string $plain): self
    {
        $hash = password_hash($plain, PASSWORD_ARGON2ID);
        if ($hash === false) {
            throw new \InvalidArgumentException('Password hashing failed.');
        }

        $instance = new self();
        $instance->hash = $hash;
        return $instance;
    }

    /**
     * check plain
     */
    public function verify(string $plain): bool
    {
        return password_verify($plain, $this->hash);
    }

    /**
     * return hash
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
