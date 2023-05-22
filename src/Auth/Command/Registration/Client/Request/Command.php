<?php

declare(strict_types=1);

namespace App\Auth\Command\Registration\Client\Request;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $name,
        #[NotBlank]
        private readonly string $email,
        #[NotBlank]
        private readonly string $password
    ) {
    }

    public function getName(): string
    {
        return $this->name;
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
