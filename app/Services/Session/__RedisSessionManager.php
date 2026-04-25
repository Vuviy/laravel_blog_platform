<?php

namespace App\Services\Session;

use App\Contracts\SessionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Predis\Client;
class __RedisSessionManager implements SessionManagerInterface
{
    private Client $redis;
    private string $prefix;
    private int $ttl;
    private string $sessionId;
    private static $instance;


    public function __construct()
    {

        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => env('REDIS_HOST', '127.0.0.1'),
            'port'   => env('REDIS_PORT', 6379),
        ]);

        $this->prefix    = env('REDIS_SESSION_PREFIX', 'session:');
        $this->ttl       = (int) env('REDIS_SESSION_TTL', 7200);
        $this->sessionId = Cookie::get('redis_session_id')
            ?? $this->generateSessionId();
    }

    static public function getInstance(): SessionManagerInterface
    {

        if (null === self::$instance) {

            self::$instance = new static();
        }
        return self::$instance;
    }


    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    private function key(string $key): string
    {
        return $this->prefix . $this->sessionId . ':' . $key;
    }

    private function generateSessionId(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function store(string $key, mixed $value): void
    {
        $this->redis->setex($this->key($key), $this->ttl, serialize($value));

        Cookie::queue('redis_session_id', $this->sessionId, $this->ttl / 60);
    }

    public function forget(string $key): void
    {
        $this->redis->del($this->key($key));
    }

    public function get(string $key): mixed
    {
        $value = $this->redis->get($this->key($key));
        return $value ? unserialize($value) : null;
    }

    public function flush(): void
    {
        $keys = $this->redis->keys($this->prefix . $this->sessionId . ':*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
        Cookie::queue(Cookie::forget('redis_session_id'));
    }

    public function regenerate(): void
    {
        // TODO: Implement regenerate() method.
    }
}
