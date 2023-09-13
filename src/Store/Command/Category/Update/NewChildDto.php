<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Update;

use Symfony\Component\Validator\Constraints\NotBlank;

class NewChildDto
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
