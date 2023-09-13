<?php

declare(strict_types=1);

namespace App\Store\Command\Vat\Update;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $id,
        #[Positive]
        private readonly int $value,
        private readonly bool $isDefault
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }
}
