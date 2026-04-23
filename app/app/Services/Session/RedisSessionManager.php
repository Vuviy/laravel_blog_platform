<?php

namespace App\Services\Session;

use App\Contracts\SessionManagerInterface;
use Illuminate\Contracts\Session\Session;

class RedisSessionManager implements SessionManagerInterface
{
    public function __construct(private Session $session)
    {}

    public function store(string $key, mixed $value): void
    {
        $this->session->put($key, $value);
    }

    public function get(string $key): mixed
    {
        return $this->session->get($key);
    }

    public function forget(string $key): void
    {
        $this->session->forget($key);
    }

    public function regenerate(): void
    {
        $this->session->regenerate();
    }

    public function flush(): void
    {
        $this->session->flush();
    }
}
