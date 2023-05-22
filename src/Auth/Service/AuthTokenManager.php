<?php

declare(strict_types=1);

namespace App\Auth\Service;

use Firebase\JWT\JWT;
use Throwable;

class AuthTokenManager
{
    public function __construct(
        private readonly string $secretKey
    ) {
    }

    public function encode(array $data): string
    {
        return JWT::encode($data, $this->secretKey);
    }

    /**
     * @throws Throwable
     */
    public function decode(string $token): array
    {
        return (array)\App\Auth\Service\JWT::decode($token, $this->secretKey, ['HS256']);
    }
}
