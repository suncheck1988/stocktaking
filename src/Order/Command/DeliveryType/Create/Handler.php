<?php

declare(strict_types=1);

namespace App\Order\Command\DeliveryType\Create;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Order\Model\DeliveryType\DeliveryType;
use App\Order\Repository\DeliveryTypeRepository;
use App\Order\Specification\DeliveryType\UniqueDeliveryTypeNameSpecification;
use Assert\AssertionFailedException;
use DateTimeImmutable;
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
        $client = $this->authContext->getClient();

        $deliveryType = new DeliveryType(
            Uuid::generate(),
            $client,
            $command->getName(),
            new DateTimeImmutable(),
            $this->uniqueDeliveryTypeNameSpecification
        );

        $this->deliveryTypeRepository->add($deliveryType);

        $this->flusher->flush();
    }
}
