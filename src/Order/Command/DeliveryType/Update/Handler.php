<?php

declare(strict_types=1);

namespace App\Order\Command\DeliveryType\Update;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Order\Repository\DeliveryTypeRepository;
use App\Order\Specification\DeliveryType\UniqueDeliveryTypeNameSpecification;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly DeliveryTypeRepository $deliveryTypeRepository,
        private readonly UniqueDeliveryTypeNameSpecification $uniqueDeliveryTypeNameSpecification,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function handle(Command $command): void
    {
        $deliveryType = $this->deliveryTypeRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $deliveryType->update($command->getName(), $this->uniqueDeliveryTypeNameSpecification);

        $this->flusher->flush();
    }
}
