<?php

declare(strict_types=1);

namespace App\Store\Command\Position\Create;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Valid;

class Command
{
    /**
     * @param BalanceDto[] $balance
     */
    public function __construct(
        #[NotBlank]
        private readonly string $categoryId,
        #[NotBlank]
        private readonly string $name,
        private readonly ?string $description,
        #[Positive]
        private readonly float $price,
        private readonly ?string $vatId,
        #[NotBlank]
        private readonly string $unitId,
        #[Valid]
        private readonly array $balance
    ) {
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getVatId(): ?string
    {
        return $this->vatId;
    }

    public function getUnitId(): string
    {
        return $this->unitId;
    }

    /**
     * @return BalanceDto[]
     */
    public function getBalance(): array
    {
        return $this->balance;
    }
}
