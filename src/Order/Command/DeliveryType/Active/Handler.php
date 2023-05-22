<?php

declare(strict_types=1);

namespace App\Order\Command\DeliveryType\Active;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Order\Repository\DeliveryTypeRepository;
use Assert\AssertionFailedException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly DeliveryTypeRepository $deliveryTypeRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     */
    public function handle(Command $command): void
    {
        $deliveryType = $this->deliveryTypeRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $deliveryType->active();

        $this->flusher->flush();
    }
}
