<?php

declare(strict_types=1);

namespace App\Client\Command\Counterparty\Create;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $name,
        private readonly ?string $email
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
