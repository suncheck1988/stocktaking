<?php

declare(strict_types=1);

namespace App\Store\Command\FixedAsset\Update;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $id,
        private readonly ?string $financiallyResponsiblePersonId,
        #[NotBlank]
        private readonly string $categoryId,
        private readonly ?string $counterpartyId,
        private readonly ?string $warehouseId,
        #[NotBlank]
        private readonly string $name,
        private readonly ?string $description,
        #[NotBlank]
        private readonly string $serialNumber,
        #[NotBlank]
        private readonly string $inventoryNumber,
        #[NotBlank]
        private readonly string $unitId,
        #[Positive]
        private readonly float $purchasePrice,
        private readonly ?string $vatId
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFinanciallyResponsiblePersonId(): ?string
    {
        return $this->financiallyResponsiblePersonId;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function getCounterpartyId(): ?string
    {
        return $this->counterpartyId;
    }

    public function getWarehouseId(): ?string
    {
        return $this->warehouseId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }

    public function getUnitId(): string
    {
        return $this->unitId;
    }

    public function getPurchasePrice(): float
    {
        return $this->purchasePrice;
    }

    public function getVatId(): ?string
    {
        return $this->vatId;
    }
}
