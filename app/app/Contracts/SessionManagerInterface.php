<?php

namespace App\Contracts;

interface SessionManagerInterface
{
    public function store(string $key, mixed $value): void;
    public function forget(string $key): void;
    public function get(string $key): mixed;
    public function flush(): void;
    public function regenerate(): void;
}
