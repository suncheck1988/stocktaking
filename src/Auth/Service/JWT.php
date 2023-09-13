<?php

declare(strict_types=1);

namespace App\Auth\Service;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Throwable;
use UnexpectedValueException;

class JWT extends \Firebase\JWT\JWT
{
    /**
     * @psalm-suppress UnusedVariable
     * @psalm-suppress ParamNameMismatch
     * @throws Throwable
     */
    public static function decode($jwt, $keyOrKeyArray, array $allowed_algs = []): object
    {
        try {
            $payload = parent::decode($jwt, $keyOrKeyArray, $allowed_algs);
        } catch (BeforeValidException|ExpiredException) {
            $tks = explode('.', $jwt);
            if (\count($tks) !== 3) {
                throw new UnexpectedValueException('Wrong number of segments');
            }
            [$headb64, $bodyb64, $cryptob64] = $tks;
            $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64));
        }

        return $payload;
    }
}
