<?php

declare(strict_types=1);

namespace App\Order\Command\Order\Create;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class Command
{
    /**
     * @param ItemDto[] $items
     */
    public function __construct(
        #[NotBlank]
        private readonly string $responsibleUserId,
        private readonly ?string $counterpartyId,
        #[NotBlank]
        private readonly string $paymentMethodId,
        #[NotBlank]
        private readonly string $deliveryTypeId,
        #[NotBlank]
        private readonly string $address,
        private readonly ?string $comment,
        private readonly ?float $deliveryPrice,
        #[Valid]
        private readonly array $items
    ) {
    }

    public function getResponsibleUserId(): string
    {
        return $this->responsibleUserId;
    }

    public function getCounterpartyId(): ?string
    {
        return $this->counterpartyId;
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    public function getDeliveryTypeId(): string
    {
        return $this->deliveryTypeId;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getDeliveryPrice(): ?float
    {
        return $this->deliveryPrice;
    }

    /**
     * @return ItemDto[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
