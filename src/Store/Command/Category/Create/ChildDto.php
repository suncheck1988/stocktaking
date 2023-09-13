<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Create;

use Symfony\Component\Validator\Constraints\NotBlank;

class ChildDto
{
    public function __construct(
        #[NotBlank]
        private readonly string $name
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
