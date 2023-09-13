<?php

declare(strict_types=1);

namespace App\Store\Specification\Warehouse;

use App\Store\Model\Warehouse\Warehouse;
use App\Store\Repository\WarehouseRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniqueWarehouseNameSpecification
{
    public function __construct(
        private readonly WarehouseRepository $warehouseRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(Warehouse $warehouse): bool
    {
        return !$this->warehouseRepository->existByName($warehouse);
    }
}
