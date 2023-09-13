<?php

declare(strict_types=1);

namespace App\Auth\Service\Auth;

class PasswordGenerator
{
    public function __construct()
    {
    }

    public function generateRandomString(int $length = 6): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        return substr(str_shuffle($chars), 0, $length);
    }

    public function getHashByPasswordString(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
