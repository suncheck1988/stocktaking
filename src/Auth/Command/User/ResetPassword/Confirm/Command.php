<?php

declare(strict_types=1);

namespace App\Auth\Command\User\ResetPassword\Confirm;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $token,
        #[NotBlank]
        private readonly string $password
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
