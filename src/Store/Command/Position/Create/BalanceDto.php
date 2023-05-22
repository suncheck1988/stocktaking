<?php

declare(strict_types=1);

namespace App\Store\Command\Position\Create;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class BalanceDto
{
    public function __construct(
        #[NotBlank]
        private readonly string $warehouseId,
        #[PositiveOrZero]
        private readonly int $quantity
    ) {
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
