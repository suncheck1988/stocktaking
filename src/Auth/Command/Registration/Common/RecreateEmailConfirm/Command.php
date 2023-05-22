<?php

declare(strict_types=1);

namespace App\Auth\Command\Registration\Common\RecreateEmailConfirm;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $token
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
