<?php

declare(strict_types=1);

namespace App\Order\Specification\DeliveryType;

use App\Order\Model\DeliveryType\DeliveryType;
use App\Order\Repository\DeliveryTypeRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniqueDeliveryTypeNameSpecification
{
    public function __construct(
        private readonly DeliveryTypeRepository $deliveryTypeRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(DeliveryType $deliveryType): bool
    {
        return !$this->deliveryTypeRepository->existByName($deliveryType);
    }
}
