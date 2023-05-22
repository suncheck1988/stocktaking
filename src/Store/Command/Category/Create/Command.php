<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Create;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $name,
        private readonly ?string $parentId
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }
}
