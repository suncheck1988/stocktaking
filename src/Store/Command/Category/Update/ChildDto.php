<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Update;

use Symfony\Component\Validator\Constraints\NotBlank;

class ChildDto
{
    public function __construct(
        #[NotBlank]
        private readonly string $id,
        private readonly bool $isRemove
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isRemove(): bool
    {
        return $this->isRemove;
    }
}
