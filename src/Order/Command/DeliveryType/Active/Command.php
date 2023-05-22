<?php

declare(strict_types=1);

namespace App\Order\Command\DeliveryType\Active;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $id
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
