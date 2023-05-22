<?php

declare(strict_types=1);

namespace App\Auth\Command\User\ResetPassword\Request;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $email
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
