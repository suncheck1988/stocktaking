<?php

declare(strict_types=1);

namespace App\UI\Http\Middleware;

use App\Data\RedisWrapper;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DdosMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly RedisWrapper $redis)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = '';
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }

        if ($ip === '') {
            return $handler->handle($request);
        }

        $key = 'ip_ddos_' . $ip;
        if ($this->redis->exists($key)) {
            $value = (int)$this->redis->get($key);
            if ($value >= 50) {
                throw new InvalidArgumentException('Слишком много запросов. Попробуйте позже');
            }
            $value++;
        } else {
            $value = 1;
        }

        $this->redis->set($key, (string)$value, ['ex' => 5]);

        return $handler->handle($request);
    }
}
