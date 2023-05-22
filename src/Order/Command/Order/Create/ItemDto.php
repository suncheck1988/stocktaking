<?php

declare(strict_types=1);

namespace App\Order\Command\Order\Create;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ItemDto
{
    public function __construct(
        #[NotBlank]
        private readonly string $id,
        #[Positive]
        private readonly float $price,
        #[Positive]
        private readonly int $quantity
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
