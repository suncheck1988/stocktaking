<?php

declare(strict_types=1);

namespace App\Auth\Service;

class JWTPayloadGenerator
{
    public function __construct()
    {
    }

    public function generate(string $id): array
    {
        return [
            'alg' => 'RS256',
            'typ' => 'JWT',
            'exp' => strtotime('+8 hour', time()),
            'id' => $id,
        ];
    }
}
