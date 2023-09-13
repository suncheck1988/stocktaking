<?php

declare(strict_types=1);

namespace App\Data;

use Redis;
use RedisException;
use Throwable;

class RedisWrapper
{
    private ?Redis $redis = null;

    public function __construct(
        private readonly string $host
    ) {
    }

    /**
     * @param string $key
     * @return bool|int|Redis
     */
    public function exists(string $key): bool|int|Redis
    {
        try {
            return $this->getConnection()->exists($key);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param string $key
     * @return false|mixed|string
     */
    public function get(string $key): mixed
    {
        try {
            return $this->getConnection()->get($key);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @param array<string, int>|null $timeout
     * @return bool|Redis
     * @psalm-suppress PossiblyNullArgument
     */
    public function set(string $key, string $value, ?array $timeout = null): bool|Redis
    {
        try {
            return $this->getConnection()->set($key, $value, $timeout);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param string $key
     * @return int|Redis
     */
    public function delete(string $key): int|Redis
    {
        try {
            return $this->getConnection()->del($key);
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * @throws RedisException
     */
    private function getConnection(): Redis
    {
        if ($this->redis === null) {
            $this->redis = new Redis();
            $this->redis->connect($this->host, 6379, 1);
        }

        return $this->redis;
    }
}
