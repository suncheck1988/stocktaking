<?php

declare(strict_types=1);

namespace App\Store\Command\Vat\Create;

use Symfony\Component\Validator\Constraints\Positive;

class Command
{
    public function __construct(
        #[Positive]
        private readonly int $value,
        private readonly bool $isDefault
    ) {
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
