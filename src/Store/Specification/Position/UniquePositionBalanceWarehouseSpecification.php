<?php

declare(strict_types=1);

namespace App\Store\Specification\Position;

use App\Store\Model\Position\Position;
use App\Store\Model\Warehouse\Warehouse;
use App\Store\Repository\PositionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniquePositionBalanceWarehouseSpecification
{
    public function __construct(
        private readonly PositionRepository $positionRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(Position $position, Warehouse $warehouse): bool
    {
        return !$this->positionRepository->existBalanceByWarehouse($position, $warehouse);
    }
}
