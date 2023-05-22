<?php

declare(strict_types=1);

namespace App\Auth\Command\Login\Request;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $email,
        #[NotBlank]
        private readonly string $password
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
