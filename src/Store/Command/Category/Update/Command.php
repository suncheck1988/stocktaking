<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Update;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class Command
{
    /**
     * @param ChildDto[] $children
     * @param NewChildDto[] $newChildren
     */
    public function __construct(
        #[NotBlank]
        private readonly string $id,
        #[NotBlank]
        private readonly string $name,
        private readonly ?string $parentId,
        #[Valid]
        private readonly array $children,
        #[Valid]
        private readonly array $newChildren
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    /**
     * @return ChildDto[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return NewChildDto[]
     */
    public function getNewChildren(): array
    {
        return $this->newChildren;
    }
}
