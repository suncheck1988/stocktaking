<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Create;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class Command
{
    /**
     * @param ChildDto[] $children
     */
    public function __construct(
        #[NotBlank]
        private readonly string $name,
        #[NotBlank]
        #[Valid]
        private readonly array $children
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ChildDto[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
